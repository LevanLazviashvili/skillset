<?php namespace skillset\Notifications\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Cms\Traits\ApiResponser;
use Google\Exception;
use skillset\Conversations\Models\ConversationUser;
use skillset\Notifications\Models\Notification;
use Illuminate\Http\Request;

class Notifications extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('skillset.Notifications', 'main-menu-item');
    }

    public function sendMessageNotification(Request $request, Notification $notificationModel, ConversationUser $conversationUserModel)
    {
        $SendTo = $conversationUserModel->where('conversation_id', $request->input('conversation_id'))->where('user_id', '!=', config('auth.UserID'))->first();
        if (!$SendTo) {
            return;
        }
        $notificationModel->sendTemplateNotifications([$SendTo->user_id], 'newMessage', [$request->input('user_name')], ['conversation_id' => $request->input('conversation_id'), 'user_name' => $request->input('user_name')], 'chat');
    }

    public function sendAutoNotifications()
    {
        return $this->response((new Notification)->sendAutoNotifications());
    }

    public function notifyWorkersWithNegativeBalance()
    {
        (new Notification)->notifyWorkersWithNegativeBalance();
    }

    public function notifyClientWithUnPayedFinishedWork()
    {
        (new Notification)->notifyClientWithUnPayedFinishedWork();
    }

    public function notifyUnratedOrdersUsers()
    {
        (new Notification)->notifyUnratedOrdersUsers();
    }

    public function notifyUnratedJobOrderUsers()
    {
        (new Notification)->notifyUnratedJobOrderUsers();
    }

    public function notifyUsersAboutNewChat()
    {
        (new Notification)->notifyUsersAboutNewChat();
    }

//    public function notifyUsersAboutUnreadMessages()
//    {
//        (new Notification)->notifyUsersAboutUnreadMessages();
//    }
    public function notifyWorkersAboutEndDate()
    {
        (new Notification)->notifyWorkersAboutEndDate();
    }

    public function sendTestNotification(Request $request)
    {
//        (new Notification)->sendTestNotification($request->all());
//        (new Notification)->sendAutoNotifications();
//        (new Notification)->sendTemplateNotifications(209, 'marketplaceInvoiceAccepted');
//        return $this->response('sent');
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        return (new Notification)->test();
    }
}
