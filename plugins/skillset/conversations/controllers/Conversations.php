<?php namespace skillset\Conversations\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use cms\helpers\Langs;
use Cms\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use October\Rain\Support\Facades\Flash;
use RainLab\User\Models\User;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\ConversationUser;
use skillset\Conversations\Models\Message;
use skillset\Notifications\Models\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;

class Conversations extends Controller
{
    use ApiResponser;
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function formRender($options = [])
    {
        $ConversationID = last(explode('/', $_SERVER['REQUEST_URI']));
        $user = User::where('admin_user_id', $this->user->id)->first();
        $token = JWTAuth::fromUser($user);
        $Conversation = (new Conversation)->find($ConversationID);
        (new Message)->where('conversation_id', $ConversationID)->where('user_id', '!=', 0)->update(['seen' => 1]);
        if ($Conversation->status_id != 0 AND !$Conversation->conversation_admin_id) {
            $Conversation->update(['conversation_admin_id' => $this->user->id]);
            $userLang = $this->getConversationUserLang($ConversationID);
            (new Message)->sendSystemMessage($ConversationID, 'operator_joined', [], [$user->name.' '.$user->surname], $userLang);
        }

        return View::make(
            'skillset.conversations::chat',
            ['app_url' => config('app.chat.app_url'), 'conversation_id' => $ConversationID, 'token' => $token]
        );
    }

    public function onCloseChat($id)
    {
        (new Conversation)->closeConversation($id);
        $userLang = $this->getConversationUserLang($id);
        (new Message)->sendSystemMessage($id, 'chat_finished', [], [], $userLang);
        Flash::success('ჩატი დახურულია');
        $model = $this->formFindModelObject($id);

        if ($redirect = $this->makeRedirect('update', $model)) {
            return $redirect;
        }
    }

    public function onLeaveChat($id)
    {
        (new Conversation)->leaveConversation($id);
        $model = $this->formFindModelObject($id);
        $user = User::where('admin_user_id', $this->user->id)->first();
        $userLang = $this->getConversationUserLang($id);
        (new Message)->sendSystemMessage($id, 'admin_left_chat', [], [$user->name.' '.$user->surname], $userLang);
        if ($redirect = $this->makeRedirect('update', $model)) {
            return $redirect;
        }
    }

    public function listExtendQuery($query)
    {
        $query->where('type', 1);
    }

    public function startConversationWithSupport(Request $request, Conversation $conversation)
    {
        if ($activeConversation = $conversation->hasActiveSupportConverstion()) {
            $conversation->sendMessage($activeConversation->id, config('auth.UserID'), $request->input('message'), $request->input('images'));
            return $this->response($activeConversation->id);
        }
        return $this->response($conversation->startNewConversation([config('auth.UserID')], config('auth.UserID'),1, $request->input('message'), $request->input('images')));
    }


    public function hasActiveSupportConversation(Conversation $conversation)
    {
        if ($activeConversation = $conversation->hasActiveSupportConverstion()) {
            return $this->response($activeConversation->id);
        }
        return $this->errorResponse('Not Found', self::$ERROR_CODES['NOT_FOUND']);
    }

    public function startConversationWithSupportUser(Request $request, Conversation $conversation, Notification $notificationModel, User $user)
    {
        $activeConversationID = $conversation->hasActiveSupportUserConverstion(config('auth.UserID'));
        if ($activeConversationID) {
            $conversation->sendMessage($activeConversationID, config('auth.UserID'), $request->input('message'), $request->input('images'));
        } else {
            $activeConversationID = (int)$conversation->startNewConversation([config('auth.UserID'), $conversation->supperUserID], config('auth.UserID'), 1, $request->input('message'));
        }

        $userInfo = $user->getInfo($request);
        $userName = $userInfo['name'].' '.$userInfo['surname'];

        $notificationModel->sendTemplateNotifications([$conversation->supperUserID], 'newMessage', [$userName], ['conversation_id' => $activeConversationID, 'user_name' => $userName], 'chat');
        return $this->response((int)$activeConversationID);
    }

    public function getSupportUser(Request $request, Conversation $conversation, User $user)
    {
        $User = $user->getInfo([], $conversation->supperUserID);
        return $this->response([
            'name'      => $User['name'],
            'surname'   => $User['surname'],
            'avatar'    => $User['avatar']
        ]);
    }


    public function getDetails(Request $request, Conversation $conversationModel)
    {
        return $this->response($conversationModel->getOne($request->all()));
    }

    public function getConversations(Request $request, ConversationUser $conversationUserModel)
    {
        return $this->successResponse($conversationUserModel->getAll($request->all()));
//        return $this->successResponse($Conversations);
    }

    public function userHasNewConversations(Request $request, Conversation $conversation)
    {
        return $this->response($conversation->userHasNewConversations());
    }

    public function uploadImages(Request $request, Conversation $conversationModel)
    {
        $validator = Validator::make($request->all(), ['images.*' => 'image|max:4000']);
        if ($validator->fails()) {
            return $this->errorResponse($validator->getMessageBag()->toArray(), self::$ERROR_CODES['VALIDATION_ERROR']);
        }
        return $this->response($conversationModel->uploadImages($request));
    }

    public function AdminUnreadMessagesCount(Request $request)
    {
        if ($request->get('secret') != config('app.admin_secret')) {
            return $this->errorResponse();
        }
        $Count = DB::table('skillset_conversations_ AS c')
            ->join('skillset_conversations_users AS cu', 'cu.conversation_id', 'c.id')
            ->join('skillset_conversations_messages AS cm', function($q) {
                $q->on('cm.conversation_id', 'c.id');
                $q->on('cm.user_id', 'cu.user_id');
            })
            ->where('c.type', 1)->where('seen', 0)
            ->count();
        return $this->response(['count' => $Count]);
    }

    private function getConversationUserLang($ConversationID)
    {
        $ConversationUser = (new ConversationUser)->where('conversation_id', $ConversationID)->where('user_id', '!=', 0)->first();
        if ($ConversationUser) {
            return User::find($ConversationUser->user_id)->lang ?? 0;
        }
        return config('app.default_lang');
    }

}
