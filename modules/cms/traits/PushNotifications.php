<?php

namespace Cms\Traits;

use Carbon\Carbon;
use Google\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RainLab\User\Models\User;
use skillset\Notifications\Models\NotificationLog;
use Google_Client;

trait PushNotifications
{

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function SendPushNotification(
        $UserIDs,
        $Title,
        $Body,
        $IconType = 0,
        $ActionButtonTitle = '',
        $ActionPage = 'profile',
        $ActionParams = ['balance_popup' => true],
        $ShowInApp = false,
        $topic = null
    ) {

        $data = [
            "notification" => [
                "title" => $Title,
                "body" => $Body,
            ],
            "android" => [
                "notification" => [
                    "sound" => 'default',
                ],
                "priority" => "high",
            ],
            "apns" => [
                "payload" => [
                    "aps" => [
                        "content-available" => 1,
                        "priority" => "high",
                    ],
                ],
            ],
            "data" => [
                'icon_state' => (string)$IconType,
                'action_button_title' => $ActionButtonTitle,
                'action_page' => $ActionPage,
                'action_params' => json_encode($ActionParams),
                'show_in_app' => (string)(int)$ShowInApp
            ]
        ];

        $Users = (new User)
            ->whereNotNull('device_token')
            ->whereIn('id', is_array($UserIDs) ? $UserIDs : [$UserIDs]);

        $DeviceTokens = $Users->pluck('device_token')->toArray();

        (new NotificationLog)->logNotification($UserIDs, $Title, $Body);

        $Users->update(['last_notification_at' => Carbon::now()->toDateTimeString()]);

        $tokens = is_array($DeviceTokens) ? $DeviceTokens : [$DeviceTokens];

        if ($topic) {
            foreach (array_chunk($tokens, 1000) as $chunk) {
                $this->subscribeToTopic($chunk, $topic);
                $this->sendToTopic($topic, $data);
                $this->unsubscribeToTopic($chunk, $topic);
            }

            return;
        }

        foreach ($tokens as $token) {
            $this->send($token, $data);
        }
    }

    /**
     * @throws Exception
     */
    public function getAccessToken()
    {
        $credentialsPath = storage_path(config('app.firebase.config_path')); // Path to your service account file

        $client = new Google_Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        return $token['access_token'];
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function send($token, array $data): void
    {
        $client = new Client();

        try {
             $client->post(config('app.firebase.api_url'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => array_merge(['token' => $token], $data),
                ],
            ]);
        } catch (ClientException $e) {
            // Handle client exceptions like 400 or 404 errors
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody()->getContents(), true);

            // Handle invalid token errors (404)
            if ($statusCode === 404 && isset($errorBody['error']['status']) && $errorBody['error']['status'] === 'NOT_FOUND') {
                // Log the error and token
                Log::warning('Invalid FCM token detected', ['token' => $token, 'response' => $errorBody]);

                User::where('device_token', $token)->update(['device_token' => null]);
            }

            // Handle unauthorized errors (401) for issues like invalid access tokens
            if ($statusCode === 401) {
                Log::error('Authorization error during FCM request', ['message' => $errorBody['error']['message']]);
            }

            // Handle other types of errors
            Log::error('FCM request failed', ['status_code' => $statusCode, 'message' => $errorBody['error']['message']]);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            Log::error('Unexpected error during FCM request', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function sendToTopic($topic, array $data): void
    {
        $client = new Client();

        try {
             $client->post(config('app.firebase.api_url'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => ['message' => array_merge(['topic' => $topic], $data)],
            ]);

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 404 && isset($errorBody['error']['status']) && $errorBody['error']['status'] === 'NOT_FOUND') {
                Log::warning('Invalid FCM topic detected', ['topic' => $topic, 'response' => $errorBody]);
            }

            if ($statusCode === 401) {
                Log::error('Authorization error during FCM request', ['message' => $errorBody['error']['message']]);
            }

            Log::error('FCM request failed', ['status_code' => $statusCode, 'message' => $errorBody['error']['message']]);
        } catch (\Exception $e) {
            Log::error('Unexpected error during FCM request', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function subscribeToTopic($tokens, $topic)
    {

        $client = new Client();

        try {
            $client->post(config('app.firebase.batch_add_api_url'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                    'access_token_auth' => 'true',
                ],
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens,
                ]
            ]);

            Log::info('Successfully subscribed tokens to topic', ['topic' => $topic]);
        } catch (ClientException $e) {
            // Handle client exceptions like 400 or 404 errors
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 400) {
                Log::error('Bad request when subscribing to FCM topic', ['error' => $errorBody, 'topic' => $topic]);
            }

            if ($statusCode === 404) {
                Log::error('Topic not found when subscribing to FCM topic', ['topic' => $topic, 'error' => $errorBody]);
            }

            if ($statusCode === 401) {
                Log::error('Authorization error during topic subscription request', ['error' => $errorBody['error']['message']]);
            }

            Log::error('FCM subscription request failed', ['status_code' => $statusCode, 'message' => $errorBody['error']['message']]);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            Log::error('Unexpected error during FCM subscription request', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function unsubscribeToTopic($tokens, $topic)
    {

        $client = new Client();

        try {
            $client->post(config('app.firebase.batch_remove_api_url'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                    'access_token_auth' => 'true',
                ],
                'json' => [
                    'to' => '/topics/' . $topic,
                    'registration_tokens' => $tokens,
                ]
            ]);

            Log::info('Successfully unsubscribed tokens to topic', ['topic' => $topic]);
        } catch (ClientException $e) {
            // Handle client exceptions like 400 or 404 errors
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $errorBody = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 400) {
                Log::error('Bad request when unsubscribing to FCM topic', ['error' => $errorBody, 'topic' => $topic]);
            }

            if ($statusCode === 404) {
                Log::error('Topic not found when unsubscribing to FCM topic', ['topic' => $topic, 'error' => $errorBody]);
            }

            if ($statusCode === 401) {
                Log::error('Authorization error during topic unsubscription request', ['error' => $errorBody['error']['message']]);
            }

            Log::error('FCM unsubscription request failed', ['status_code' => $statusCode, 'message' => $errorBody['error']['message']]);
        } catch (\Exception $e) {
            // Handle other unexpected errors
            Log::error('Unexpected error during FCM unsubscription request', ['message' => $e->getMessage()]);
        }
    }
}