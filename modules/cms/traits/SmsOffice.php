<?php
namespace Cms\Traits;
use Illuminate\Support\Arr;
use October\Rain\Support\Facades\Http;
use skillset\Log\Models\SmsLog;

//use GuzzleHttp\Client;

trait SmsOffice
{
    public function SendSMS($Phone = null, $Text = null)
    {
        if (strlen($Text) > 700)
        {
            foreach ($this->getSubStrings($Text) AS $subText) {
                $this->SendMessage($Phone, $subText);
            }
            return;
        }
        $this->SendMessage($Phone, $Text);
    }

    private function SendMessage($Phone = null, $Text = null)
    {
        $postInput = [
            'key'           => '28a40872e0f14b9ebc073a43d492e1cd',
            'destination'   => $Phone,
            'sender'        => 'skillset',
            'content'       => $Text,
            'urgent'        => 'true'
        ];

        $url = 'https://smsoffice.ge/api/v2/send/';

        $ch = curl_init();

        $url = $url.'?'.http_build_query($postInput);
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        (new SmsLog)->create([
            'to_phone'      => $Phone,
            'sms_text'      => $Text,
            'curl_response' => $data
        ]);
        return $data;
    }

    private function sendRequest()
    {

    }

    private function getSubStrings($Text, $length = 700)
    {
        $SubStrings = [];
        $StrPos = strpos($Text, ' ', $length);
        $SubStrings[0] = substr($Text, 0, $StrPos);
        $SubStrings[1] = substr($Text, $StrPos);
        if (strlen($SubStrings[1]) > $length) {
            $StrPos2 = strpos($SubStrings[1], ' ', $length);
            $SubStr = $SubStrings[1];
            $SubStrings[1] = substr($SubStr, 0,  $StrPos2);
            $SubStrings[2] = substr($SubStr, $StrPos2);
        }
        return $SubStrings;
    }


}