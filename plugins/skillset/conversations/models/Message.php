<?php namespace skillset\Conversations\Models;

use skillset\Conversations\Models\MessageImages;
use Illuminate\Support\Arr;
use Model;
use RainLab\User\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use RainLab\Translate\Models\Message as TranslateMessage;

/**
 * Model
 */
class Message extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_conversations_messages';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function Sender()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function images()
    {
        return $this->hasMany(MessageImages::class, 'message_id', 'id');
    }

    public function ConversationUsers()
    {
        return $this->hasMany(ConversationUser::class, 'conversation_id', 'conversation_id');
    }

    public $SystemMessages = [
        'chat_created'              => ['საუბარი დაიწყო'],
        'offer_accepted_by_worker'  => ['შემსრულებელი დაეთანხმა სამუშაოს დაწყებას.   დასრულების თარიღი: %s'],
        'offer_rejected_by_worker'  => ['შემსრულებელმა უარი განაცხადა სამუშაოების დაწყებაზე'],
        'offer_accepted_by_client'  => ['დამკვეთი დაეთანხმა სამუშაოს დაწყებას', 'სამუშაო დაწყებულია'],
        'offer_accepted_by_client_pre_pay'  => ['დამკვეთი დაეთანხმა სამუშაოს დაწყებას'],
        'offer_rejected_by_client'  => ['დამკვეთმა უარი განაცხადა სამუშაოების დაწყებაზე'],
        'marketplace_offer_accepted_pre_pay'  => ['მყიდველი დაეთანხმა ინვოისს'],
        'marketplace_offer_accepted'  => ['თანხა გადახდილია.'],
        'marketplace_offer_rejected'  => ['ინვოისი უარყოფილია.'],
        'payed_with_cash'           => ['სამუშაო დასრულებულია, თანხა გადახდილია ნაღდი ანგარიშსწორებით'],
        'payed_with_balance'        => ['სამუშაო დასრულებულია, თანხა გადახდილია საბანკო ბარათით'],
        'job_payed_with_balance'                 => ['თანხა გადახდილია საბანკო ბარათით',  'სამუშაო დაწყებულია'],
        'job_finished_payed_with_cash'           => ['სამუშაო დასრულებულია, თანხა გადახდილია ნაღდი ანგარიშსწორებით'],
        'job_finished_payed_with_balance'        => ['სამუშაო დასრულებულია, თანხა გადახდილია საბანკო ბარათით'],
        'marketplace_client_accepted'           => ['კლიენტმა ჩაიბარა შეკვეთა'],
        'marketplace_payed_with_cash'           => ['შესყიდვა დასრულებულია, თანხა გადახდილია ნაღდი ანგარიშსწორებით'],
        'marketplace_payed_with_balance'        => ['შესყიდვა დასრულებულია, თანხა გადახდილია საბანკო ბარათით'],
        'contract_is_ready'         => ['მიღება/ჩაბარება გამოგზავნილია'],
        'job_contract'         => ['მიღება/ჩაბარება: %s'],
        'marketplace_contract'         => ['მიღება/ჩაბარება: %s'],
        'operator_joined'           => ['ოპერატორი %s შემოვიდა ჩატში'],
        'chat_finished'             => ['საუბარი დასრულებულია'],
        'admin_left_chat'           => ['ოპერატორი %s გავიდა ჩატიდან'],
        'offered_services'          => ['შემსრულებელმა გამოგზავნა სავარაუდო ინვოისი: \n %s \n  გაითვალისწინეთ, სამუშაოს დასრულებისას შესაძლოა ინვოისი შეიცვალოს.'],
        'offered_products'          => ['მომხმარებელმა გამოგზავნა სავარაუდო ინვოისი: \n %s \n .'],
        'offered_services_pretext'  => ['1. შემსრულებელი პასუხისმგებელია სამუშაოს შესრულების ხარისხზე დამკვეთის წინაშე. \n 2. დამკვეთი ვალდებულია: \n 2.1. მიღება-ჩაბარების გაფორმებისთანავე მოახდინოს ანგარიშსწორება შემსრულებელთან. \n 2.2. სამუშაოს დაწყებამდე შეკვეთის გაუქმების შემთხვევაში გადაიხადოს ხელოსნის გამოძახების ხარჯი - 20 ლარი.'],
        'job_offered_services_pretext'  => ['1. შემსრულებელი პასუხისმგებელია სამუშაოს შესრულების ხარისხზე დამკვეთის წინაშე. \n 2. დამკვეთი ვალდებულია: \n 2.1. მიღება-ჩაბარების გაფორმებისთანავე მოახდინოს ანგარიშსწორება შემსრულებელთან.']
    ];

    public function getMessageText($key, $index = 0)
    {
        $translate = TranslateMessage::where('code', 'system_messages.' . $key)->first()->getContentAttribute();

        return explode(' | ', $translate)[$index];
    }

    public function sendSystemMessage($ConversationID, $MessageKey, $AdditionalData = [], $params = [])
    {
        if (!$ConversationID) {
            return;
        }
        $token = JWTAuth::attempt([
            'username' => config('app.system_messages.username'),
            'password' => config('app.system_messages.password')
        ]);

        $translate = TranslateMessage::where('code', 'system_messages.' . $MessageKey)->first()->getContentAttribute();

        $messages = explode(' | ', $translate);

        foreach ($messages AS $Message) {
            if (substr_count($Message, '%s') == count($params)) {
                $Message = vsprintf($Message, $params);
            } else {
                $Message = str_replace('%s', '', $Message);
            }
            traceLog($Message);
            $this->sendMessageByCurl($Message, $ConversationID, $token, $AdditionalData);
        }
    }

    private function sendMessageByCurl($Message, $ConversationID, $Token, $AdditionalData)
    {
        $dataString = json_encode([
            'message'           => $Message,
            'conversation_id'   => $ConversationID,
            'additional_data'   => $AdditionalData
        ]);

        $headers = [
            'authorization: Bearer ' . $Token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('app.system_messages.url'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
