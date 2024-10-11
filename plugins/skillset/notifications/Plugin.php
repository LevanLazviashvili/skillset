<?php namespace skillset\Notifications;

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
            'special_send_to' => function($value) {
                $map = [
                    0 => 'ყველას',
                    1 => 'ხელსნებს',
                    2 => 'შემკვეთებს',
                ];
                return $map[$value];
            },
            'special_frequency' => function($value) {
                $map = [
                    0 => 'ერთჯერადად',
                    1 => 'ყოველ 1 საათში',
                    2 => 'ყოველ 3 საათში',
                    3 => 'ყოველ დღე',
                    4 => '3 დღეში ერთხელ',
                    5 => 'თვეში ერთხელ',
                ];
                return $map[$value];
            },
        ];
    }
}
