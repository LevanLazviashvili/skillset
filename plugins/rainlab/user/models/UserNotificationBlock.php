<?php namespace RainLab\User\Models;

use Aws\Result;
use cms\helpers\Langs;
use Guzzle\Http\Message\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use skillset\Notifications\Models\Notification;

/**
 * Model
 */
class UserNotificationBlock extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $availableToBlockTemplates = ['newJob', 'newMarketplaceApplication'];
    public $availableToBlockTemplateIDs = [];
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_user_notification_blocks';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function __construct()
    {
        $TemplateNames = (new Notification)->templates;

        foreach ($this->availableToBlockTemplates AS $templateName) {
            $this->availableToBlockTemplateIDs[$templateName] = Arr::get($TemplateNames, $templateName);
        }
    }


    public function getStatuses()
    {
        $Return = [];
        foreach ($this->availableToBlockTemplateIDs AS $key => $template) {
            $Return[$key] = [
                'key'       => $key,
                'title'     => Langs::get($key),
                'status'    => true
            ];
        }
        $blockedNotifications = self::where('user_id', config('auth.UserID'))->whereIn('notification_template_id', $this->availableToBlockTemplateIDs)->get();
        $flipedTemplateIDs = array_flip($this->availableToBlockTemplateIDs);
        foreach ($blockedNotifications AS $blockedNotification) {
            if ($key = Arr::get($flipedTemplateIDs, $blockedNotification->notification_template_id)) {
                $Return[$key]['status'] = false;
            }
        }
        return array_values($Return);
    }

    public function updateStatus($params)
    {
        foreach (Arr::get($params, 'notifications') AS $notificationName => $value) {
            $NotificationID = Arr::get($this->availableToBlockTemplateIDs, $notificationName);
            if (!$NotificationID) {
                continue;
            }
            if (!$value) {
                self::updateOrCreate([
                    'user_id'                      => config('auth.UserID'),
                    'notification_template_id'     => $NotificationID
                ], [
                    'user_id'                      => config('auth.UserID'),
                    'notification_template_id'     => $NotificationID
                ]);
                continue;
            }
            self::where('user_id', config('auth.UserID'))->where('notification_template_id', $NotificationID)->delete();

        }


    }
}
