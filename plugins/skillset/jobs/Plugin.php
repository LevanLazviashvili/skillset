<?php namespace skillset\Jobs;

use October\Rain\Support\Arr;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function registerListColumnTypes()
    {
        return [
            'special_job_statuses' => function($value) {
                $map = [
                    1 => '<div style="color:red">ახალი</div>',
                    2 => '<div style="color:orange">სამუშაოები დაიწყო</div>',
                    3 => '<div style="color:green">დასრულებულია</div>',
                ];
                return Arr::get($map, $value);
            },
            'special_job_types'  => function($value) {
                $map = [
                    1 => 'უფასო',
                    2 => 'vip'
                ];
                return Arr::get($map, $value);
            },
            'special_job_payment_types'  => function($value) {
                $map = [
                    0 => 'ქეში',
                    1 => 'ბალანსი'
                ];
                return Arr::get($map, $value);
            },
            'special_job_order_statuses'  => function($value) {
                $map = [
                    1 => 'გადახდის მოლოდინში',
                    2 => 'სამუშაო დაიწყო',
                    3 => 'მიღება / ჩაბარება გაგზავნილია',
                    4 => 'თანხა გადახდილია'
                ];
                return Arr::get($map, $value);
            },
            'special_job_offer_statuses'  => function($value) {
                $map = [
                    1 => 'საუბარი დაიწყო',
                    2 => 'შემსრულებელი დაეთანხმა სამუშაოს დაწყებას',
                    3 => 'შემსრულებელმა უარი განაცხადა სამუშაოების დაწყებაზე',
                    4 => 'დამკვეთმა უარი განაცხადა სამუშაოების დაწყებაზე',
                    5 => 'დამკვეთი დაეთანხმა სამუშაოს დაწყებას (ინვოისი დადასტურებილია)'
                ];
                return Arr::get($map, $value);
            },
            'special_job_message_unread' => function($value) {
                if ($value > 0) {
                    return '<div style="color:red">კი ('. $value .')</div>';
                }
                return '<div style="color:green">არა</div>';
            }
        ];
    }
}
