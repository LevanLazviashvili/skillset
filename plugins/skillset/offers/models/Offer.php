<?php namespace skillset\Offers\Models;


use Backend\Facades\Backend;
use Carbon\Carbon;
use Cms\Traits\Pagination;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Model;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Configuration\Traits\Config;
use skillset\Conversations\Controllers\Conversations;
use skillset\Conversations\Models\Conversation;
use skillset\Conversations\Models\Message;
use skillset\details\Models\Region;
use skillset\Notifications\Models\Notification;
use skillset\Services\Models\Service;
use skillset\Services\Models\SubService;

/**
 * Model
 */
class Offer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Pagination;
    use Config;

    public $Statuses = [
        'active'    => 0,
        'finished'  => 1,
        'canceled'  => -1
    ];
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'skillset_offers_';

    public $visible = ['id', 'created_at', 'offer', 'title', 'service_id', 'client_id', 'status_id', 'OfferedWorkers', 'Client', 'OfferedWorker'];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'Services'      => [Service::class, 'key' => 'service_id', 'otherKey' => 'id'],
        'Client'        => [User::class, 'key' => 'client_id', 'otherKey' => 'id'],
        'Client1'       => [User::class, 'key' => 'client_id', 'otherKey' => 'id'],
        'Unread'        => [OfferWorker::class, 'key' => 'id', 'otherKey' => 'offer_id', 'conditions' => '(SELECT count(0) FROM skillset_conversations_messages WHERE conversation_id = skillset_offers_workers.conversation_id AND seen = 0 AND user_id NOT IN (SELECT id FROM users WHERE admin_user_id IS NOT NULL) AND conversation_id != 0) > 0 AND (SELECT count(0) FROM skillset_offers_ WHERE id = skillset_offers_workers.offer_id AND client_id = 0) > 0'],
        'OfferWorkers'   => [OfferWorker::class, 'key' => 'id', 'otherKey' => 'offer_id']
    ];

    public function getSubServicesOptions()
    {
        $Return = [
            ['ყველა']
        ];
        if ($this->Services) {
            return array_replace($Return, $this->Services->SubServices()->pluck('title','id')->toArray());
        }
        return array_replace($Return,(new Service)->first()->SubServices()->pluck('title','id')->toArray());
    }

    public function getRegionOptions()
    {
        return (new Region)->pluck('title','id')->toArray();
    }

    public function OfferedWorkers()
    {
        return $this->hasMany(OfferWorker::class, 'offer_id', 'id');
    }
    public function OfferedWorker()
    {
        return $this->hasOne(OfferWorker::class, 'offer_id', 'id')->where('worker_id', config('auth.UserID'));
    }

    public function Service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

    public function Client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }

    public function store($params = [])
    {
        $ServiceData = $this->getServiceData($params);
        $SearchParams = [];
        foreach ($params AS $key => $value) {
            if (!in_array($key, ['offer', 'workers', 'service_id', 'service_sub_ids'])) {
                $SearchParams[$key] = $value;
            }
        }
        return self::create([
            'client_id'             => config('auth.UserID'),
            'status_id'             => 0,
            'title'                 => Arr::get($ServiceData, 'title'),
            'offer'                 => Arr::get($params, 'offer'),
            'service_id'            => Arr::get($ServiceData, 'id'),
            'search_params'         => json_encode($SearchParams, JSON_UNESCAPED_UNICODE),
            'custom_client_phone'   => Arr::get($params, 'custom_client_phone'),
            'custom_client_address' => Arr::get($params, 'custom_client_address'),
            'comment'               => Arr::get($params, 'comment')
        ]);

    }

    public function getOne($id, $ignoreValidation = false, $WithMessagesCount = false)
    {
        $UserModel = (new User);
        $UserType = $UserModel->getUserType();
        $Query = self::Query();

        if ($UserType == 'worker') {
            $Query->with('Client', 'OfferedWorker')
                ->whereHas('OfferedWorkers', function($q){
                    $q->where('worker_id', config('auth.UserID'));
                });
        } else {
            if (!$ignoreValidation) {
                $Query->where('client_id', config('auth.UserID'));
            }
            $Query->with('Client', 'OfferedWorkers', 'OfferedWorkers.Details');
        }

        $Query->where('id', $id);
        $Item = $Query->first();
        if (!$Item) {
            return false;
        }
        $Return = $Item->toArray();
        $Return['client'] = $UserModel->filterInfo($Item->Client, true, true, $Item->custom_client_address);

        if ($UserType == 'client') {
            $OfferedWorkers = [];
            foreach ($Item->OfferedWorkers AS $key => $offeredWorker) {
//                if ($offeredWorker->notification_count > 0 && $offeredWorker->seen == 0) {
//                    $offeredWorker->update(['seen' => 1]);
//                }
                $OfferedWorkers[$key] = $offeredWorker->toArray();
                $OfferedWorkers[$key]['details'] = $UserModel->filterInfo($offeredWorker->Details);
            }
            $Return['offered_workers'] = $OfferedWorkers;
            $Item->update(['seen' => 1]);
        } else {
            if ($Item->OfferedWorker) {
                $Item->OfferedWorker->update(['seen' => 1]);
            }
        }
//        $Return['app_percent'] = $this->getConfig('skillset_percent'); //TODO delete/edit
        return $Return;
    }

    public function getOfferEditData($params = [])
    {
        $Return = [];
        $Offer = (new Offer)->getOne(Arr::get($params, 'offer_id'), true, true);
        $Return['OfferStatuses'] = [
            0   => '<div style="color:orange">შეთავაზებულია</div>',
            1   => '<div style="color:orange">შემსრულებელმა გახსნა ჩატი</div>',
            2   => '<div style="color:green">შემსრულებელი დათანხმდა შეთავაზებას</div>',
            -1  => '<div style="color:red">შემსრულებელმა უარი თქვა შეთავაზებაზე</div>',
            -2  => '<div style="color:red">დამკვეთმა უარი თქვა ხელოსანთან თანამშრომლობაზე</div>',
            3   => '<div style="color:green">შეთავაზება გადავიდა შეკვეთებში</div>'

        ];

        $Return['OfferedWorkers'] = Arr::get($Offer, 'offered_workers');
        $Return['OfferStatus']  = Arr::get($Offer, 'status_id');
        foreach ($Return['OfferedWorkers'] AS $key => $OfferedWorker) {
            $Return['OfferedWorkers'][$key]['unread_messages'] = (new Message)->where('conversation_id', Arr::get($OfferedWorker, 'conversation_id'))->where('seen', 0)->whereNotIn('user_id', function($q) {
                $q->select('id')->from('users')->where('admin_user_id', '!=', null);
            })->count();
            $Return['OfferedWorkers'][$key]['chat_url'] = Backend::url('/skillset/conversations/conversations/update/'. Arr::get($OfferedWorker, 'conversation_id'));
        }
        return $Return;
    }

    public function getAll($params = [], $withPager = true, $Count = 0)
    {
        $UserModel = (new User);
        $UserType = $UserModel->getUserType();
        $Query = $this->getQuery($UserType);
        $Count = $Count ? $Count : $Query->count();
        $Pager = $this->GetPageData($Count, Arr::get($params, 'limit', 20), Arr::get($params, 'page', 1));
        $Query->limit(Arr::get($Pager, 'limit', 0))
            ->offset(Arr::get($Pager, 'offset', 0));
//        $appPercent = $this->getConfig('skillset_percent'); //TODO DELETE / edit
        $Data = $Query->orderBy('id', 'desc')->get()->map(function($item) use ($UserModel, $UserType) {
            $Obj = $item;
            $Return = $item->toArray();
            if ($UserType == 'worker') {
                $Obj->OfferedWorker->update(['seen' => 1]);
                $Return['client'] = $UserModel->filterInfo($Obj->Client, true, true, $Obj->custom_client_address);
//                $Return['client']['address'] = $UserModel->getUserFullAddress($Obj->Client);
            } else {
                if ($item->seen == 0) {
                    $item->update(['seen' => 1]);
                }
                $OfferedWorkers = [];
                foreach ($Obj->OfferedWorkers AS $key => $offeredWorker) {
//                    if ($offeredWorker->seen == 0) {
//                        $offeredWorker->update(['seen' => 1]);
//                    }
                    $OfferedWorkers[$key] = $offeredWorker->toArray();
                    $OfferedWorkers[$key]['details'] = $UserModel->filterInfo($offeredWorker->Details);
                }
                $Return['offered_workers'] = $OfferedWorkers;
            }
//            $Return['app_percent'] = $appPercent;
            return $Return;

        });

        $Return['offers'] = $Data->toArray();
        if ($withPager) {
            $Return['pagination'] = $Pager;
        }
        return ($Return);
    }

    public function cancelOffer($OfferID)
    {
        self::where('id', $OfferID)->update(['status_id' => $this->Statuses['canceled']]);
        $this->cancelOfferConversations($OfferID);
    }

    public function cancelUnActiveOffers()
    {
        $minimumDate = Carbon::now()->subDays($this->getConfig('unactivate_offers_after_days'))->toDateTimeString();
        $Offers = self::where('status_id', 0)->where('updated_at', '<', $minimumDate)
            ->whereDoesntHave('OfferedWorkers', function($q) use ($minimumDate) {
                $q->where('updated_at', '>', $minimumDate);
            })
            ->get();
        foreach ($Offers AS $Offer) {
            (new Notification)->sendUnActiveOfferNotification($Offer->client_id, $Offer->service_id, json_decode($Offer->search_params, 1), $Offer->id);
            $this->cancelOffer($Offer->id);
        }
    }

    private function generateOfferTitle($params = [])
    {
        if (Arr::get($params, 'service_ids')) {
            $item = (new Service)->find(Arr::first(explode('.', Arr::get($params, 'service_ids'))));
            return Arr::get($item, 'title');
        }

        if (Arr::get($params, 'service_sub_ids')) {
            $SubService = (new SubService)->with('Service')->find(Arr::first(explode('.', Arr::get($params, 'service_sub_ids'))));
            return Arr::get($SubService, 'Service.title');
        }

        return '';
    }

    private function getServiceData($params = [])
    {
        if (Arr::get($params, 'service_id')) {
            return (new Service)->find(Arr::get($params, 'service_id'));
        }

        if (Arr::get($params, 'service_ids')) {
            return (new Service)->find(Arr::get(explode('.',Arr::get($params, 'service_ids')), 0));
        }

        if (Arr::get($params, 'service_sub_ids')) {
            $SubService = (new SubService)->with('Service')->find(Arr::first(explode('.', Arr::get($params, 'service_sub_ids'))));
            return Arr::get($SubService, 'Service');
        }
        return [];
    }

    public function getQuery($UserType)
    {
        $Query = self::Query();

        if ($UserType == 'worker') {
            $Query->with('Client', 'Client.Country', 'Client.Region', 'OfferedWorker')
                ->whereHas('OfferedWorkers', function($q){
                    $q->where('worker_id', config('auth.UserID'));
                    $q->where('status_id', '!=', (new OfferWorker)->statuses['offer_rejected_by_client']);
                });
        } else {
            $Query->where('client_id', config('auth.UserID'));
            $Query->with('OfferedWorkers', 'OfferedWorkers.Details');
        }

        $Query->where('status_id', $this->Statuses['active']);
        return $Query;

    }

    private function cancelOfferConversations($OfferID)
    {
        $ConverstionIDs = (new OfferWorker)->where('offer_id', $OfferID)->get()->pluck('conversation_id')->toArray();
        (new Conversation)->whereIn('id', $ConverstionIDs)->update(['status_id' => 0]);
    }
}
