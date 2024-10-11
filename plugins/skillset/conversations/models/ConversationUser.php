<?php namespace skillset\Conversations\Models;

//use App\Models\Conversation;
//use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class ConversationUser extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_conversations_users';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function Conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'id');
    }

    public function Users()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function getAll(array $params = [])
    {
        $userModel = (new User);
        $Conversations = self::where('user_id', config('auth.UserID'));
        if ($OrderID = Arr::get($params, 'order_id')) {
            $Conversations->whereHas('Conversation.Order', function($q) use ($OrderID){
               $q->where('id', $OrderID);
            });
        }

        if ($OfferID = Arr::get($params, 'offer_id')) {
            $Conversations->whereHas('Conversation.Offer', function($q) use ($OfferID){
                $q->where('offer_id', $OfferID);
            });
        }
//        DB::connection()->enableQueryLog();
        return $Conversations->select('skillset_conversations_users.*')->with('Conversation', 'Conversation.Users', 'Conversation.lastMessage')
            ->join('skillset_conversations_ AS sc', 'sc.id', 'skillset_conversations_users.conversation_id')
            ->orderBy('sc.updated_at', 'desc')
            ->get()->map(function($Obj) use ($userModel){
//                if ($_SERVER['REMOTE_ADDR'] == '188.123.138.96') {
//                    print_R(DB::getQueryLog());
//                    die();
//                }
                $Return = $Obj->toArray();
                foreach (Arr::get($Obj, 'Conversation.Users', []) AS $user) {
                    if (Arr::get($user, 'id') != config('auth.UserID')) {
                        $Return['conversation']['user'] = $userModel->filterInfo($user);
                    }
                }
                if (!Arr::get($Return, 'conversation.user')) {
                    $Return['conversation']['user']['avatar']['path'] = config('app.url').'storage/app/uploads/public/logo.png';
                    $Return['conversation']['user']['avatar']['thumb'] = config('app.url').'storage/app/uploads/public/logo_thumb.png';
                }
                $Return['conversation']['last_message'] = Arr::get($Return, 'conversation.last_message.0');
                unset($Return['conversation']['users']);
                return $Return;
            })->toArray();
    }

}
