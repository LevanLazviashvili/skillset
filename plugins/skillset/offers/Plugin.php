<?php namespace skillset\Offers;

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
            'special_offer_statuses' => function($value) {
                $map = [
                    -1 => '<div style="color:red">გაუქმებულია</div>',
                    0 => '<div style="color:orange">მიმდინარე</div>',
                    1 => '<div style="color:green">შეთავაზება გადავიდა შეკვეთებში</div>',
                ];
                return Arr::get($map, $value);
            },
            'special_unread' => function($value) {
                if ($value) {
                    return '<div style="color:red">კი</div>';
                }
                return '<div style="color:green">არა</div>';
            }
        ];
    }
}
