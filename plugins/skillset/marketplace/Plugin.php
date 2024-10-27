<?php namespace skillset\Marketplace;

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
            'special_application_statuses' => function($value) {
                $map = [
                    1 => '<div style="color:red">ახალი</div>',
                    2 => '<div style="color:orange">პროცესშია</div>',
                    3 => '<div style="color:orange">დასრულებული</div>',
                ];
                return Arr::get($map, $value);
            },
            'special_application_types'  => function($value) {
                $map = [
                    1 => 'უფასო',
                    2 => 'vip'
                ];
                return Arr::get($map, $value);
            },
            'special_application_trade_types'  => function($value) {
                $map = [
                    1 => 'გაყიდვა',
                    2 => 'ყიდვა',
                ];
                return Arr::get($map, $value);
            },
            'special_application_categories'  => function($value) {
                $map = [
                    1 => 'ახალი სამშენებლო მასალა',
                    2 => 'მეორადი სამშენებლო მასალა',
                    3 => 'შრომის მეორადი იარაღები'
                ];
                return Arr::get($map, $value);
            },
            'special_marketplace_payment_types'  => function($value) {
                $map = [
                    0 => 'ქეში',
                    1 => 'ბალანსი'
                ];
                return Arr::get($map, $value);
            },
            'special_marketplace_offer_statuses'  => function($value) {
                $map = [
                    1 => 'საუბარი დაიწყო',
                    2 => 'მომხმარებელმა გამოაგზავნა სავარაუდო ინვოისი',
                    3 => 'ინვოისი დადასტურებულია',
                    4 => 'ინვოისი უარყოფილია',
                ];
                return Arr::get($map, $value);
            },
            'special_marketplace_order_statuses'  => function($value) {
                $map = [
                    1 => 'დადასტურების მოლოდინში',
                    2 => 'გადახდის მოლოდინში',
                    3 => 'კლიენტმა გადაიხადა',
                    4 => 'მიღება/ჩაბარება გაგზავნილია',
                    5 => 'გადახდილი'
                ];
                return Arr::get($map, $value);
            },
            'special_marketplace_message_unread' => function($value) {
                if ($value > 0) {
                    return '<div style="color:red">კი ('. $value .')</div>';
                }
                return '<div style="color:green">არა</div>';
            }
        ];
    }
}
