<?php namespace skillset\Conversations;

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
            'special_conversation_status' => function($value) {
                $map = [
                    0 => '<div style="color:red">დახურული</div>',
                    1 => '<div style="color:green">აქტიური</div>',
                ];
                return Arr::get($map, $value);
            },
        ];
    }


}
