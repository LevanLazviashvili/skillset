<?php namespace skillset\Conversations\Models;

//use App\Models\ConversationUsers;
//use App\Models\Message;
//use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Model;
use Pheanstalk\Exception;
use RainLab\User\Models\User;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Orders\Models\Order;
use skillset\Jobs\Models\Offer as jobOffer;
use skillset\Marketplace\Models\Offer as marketAppOffer;

/**
 * Model
 */
class Conversation extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_conversations_';
    protected $primaryKey = 'id';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $attachMany = [
        'images'   =>   \System\Models\File::class
    ];

    public $belongsTo = [
        'CreatedBy'             => [User::class, 'key' => 'created_by', 'otherKey' => 'id'],
        'ConversationAdmin'     => [User::class, 'key' => 'conversation_admin_id', 'otherKey' => 'admin_user_id'],
        'unread'                => [Message::class, 'key' => 'id', 'otherKey' => 'conversation_id', 'conditions' => 'seen = 0 AND user_id != 0'],
        'lastMessage'          => [Message::class, 'key' => 'id', 'otherKey' => 'conversation_id', 'order' => 'updated_at desc']
    ];

    public $belongsToMany = [

    ];


    public function Order()
    {
        return $this->hasOne(Order::class, 'conversation_id', 'id');
    }

    public function Offer()
    {
        return $this->hasOne(OfferWorker::class, 'conversation_id', 'id');
    }

    public function jobOffer()
    {
        return $this->hasOne(jobOffer::class, 'conversation_id', 'id');
    }

    public function marketAppOffer()
    {
        return $this->hasOne(marketAppOffer::class, 'conversation_id', 'id');
    }

    public function ConversationUsers()
    {
        return $this->hasMany(ConversationUser::class, 'conversation_id', 'id');
    }

//    public function CreatedBy()
//    {
//        return $this->hasOne(User::class, 'id', 'created_by');
//    }

    public function users()
    {
        return $this->hasManyThrough(User::class, ConversationUser::class, 'conversation_id', 'id', 'id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id')->orderBy('id', 'desc');
    }

    public function startNewConversation($userIDs = [], $CreatedBy = 0, $Type = 0, $Message = null, $Images = null)
    {
//        if (count($userIDs) != 2) {
//            return false;
//        }
        $item = self::create([
            'status_id'     => 1,
            'type'          => $Type,
            'created_by'    => $CreatedBy
        ]);
        $ConversationID = $item->id;
        foreach ($userIDs AS $userID) {
            ConversationUser::create([
                'conversation_id' => $ConversationID,
                'user_id'         => $userID
            ]);
        }
        if ($Message) {
            $this->sendMessage($ConversationID, $CreatedBy, $Message, $Images);
        }

        return $ConversationID;
    }

    public function sendMessage($ConversationID, $UserID, $Message, $Images)
    {
        if (!$Message) {
            return;
        }
        $message = Message::create([
            'conversation_id' => $ConversationID,
            'user_id'         => $UserID,
            'message'         => $Message
        ]);
        if ($Images) {
            foreach ($Images as $image) {
                $message->images()->create([
                    'message_id' => $message->id,
                    'path' => Arr::get($image, 'path'),
                    'thumb' => Arr::get($image, 'thumb')
                ]);
            }
        }
        Conversation::find($ConversationID)->update(['updated_at' => Carbon::now()->toDateTimeString()]);
    }

    public function hasActiveSupportConverstion($UserID = null)
    {
        return self::whereHas('ConversationUsers', function($q) use ($UserID) {
            $q->where('user_id', $UserID ?: config('auth.UserID'));
        })->where('type', 1)->where('status_id', 1)->first();
    }

    public function getOne($params = [])
    {
        $item = self::with('Order', 'Offer', 'jobOffer.order', 'jobOffer.job', 'marketAppOffer.order', 'users')
            ->where('id', Arr::get($params, 'conversation_id'))
            ->whereHas('ConversationUsers', function($q){
                $q->where('user_id', config('auth.UserID'));
            })
            ->first();
        if (!$item) {
            return [];
        }
        $Return = $item->toArray();
        foreach ($item->users AS $key => $user) {
            $Return['users'][$key] = $user->filterInfo($user);
        }

        return $Return;
    }

    public function uploadImages($request) {
        $Conversation = self::whereHas('ConversationUsers', function($q) {
            $q->where('user_id', config('auth.UserID'));
        })->first();
        if (!$Conversation) {
            throw new Exception('Wrong Conversation ID');
        }
        $Conversation->images = $request->file('images');
        $Conversation->save();
        $Return = [];
        foreach ($Conversation->images->reverse()->take(count($request->file('images'))) AS $image) {
            $Return[] = [
                'path'    =>   $image->getPath(),
                'thumb'   =>   $image->getThumb(100, 100, ['mode' => 'crop'])
            ];
        }
        return $Return;
    }

    public function closeConversation($id)
    {
        self::find($id)->update(['status_id' => 0, 'conversation_admin_id' => 0]);
    }

    public function leaveConversation($id)
    {
        self::find($id)->update(['conversation_admin_id' => 0]);
    }

    public function userHasNewConversations()
    {
        $minimumDate = Carbon::now()->subMinutes(2)->toDateTimeString();
        $newConversation = self::where('created_at', '>', $minimumDate)->whereHas('ConversationUsers', function ($q){
            $q->where('user_id', config('auth.UserID'));
        })->limit(1)->first();

        return [
            'has_new' => (bool)$newConversation
        ];
    }
}
