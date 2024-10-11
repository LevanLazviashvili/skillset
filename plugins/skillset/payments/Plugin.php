<?php namespace skillset\Payments;

use Illuminate\Support\Arr;
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
            'special_payment_type' => function($value) {
                $map = [
                    0 => '',
                    1 => 'მიღ./ჩაბ. გადახდა',
                    2 => 'ბალანსის შევსება',
                ];
                return Arr::get($map, $value, '');
            },
            'special_status' => function($value) {
                $map = [
                    'created'       => '<p style="color: orange;">პროცესშია</p>',
                    'error'         => '<p style="color: red;">წარუმატებლად დასრულდა</p>',
                    'rejected'      => '<p style="color: red;">წარუმატებლად დასრულდა</p>',
                    'performed'     => '<p style="color: green;">წარმატებით დასრულდა</p>',
                    'success'       => '<p style="color: green;">წარმატებით დასრულდა</p>',
                ];
                return Arr::get($map, $value, '<p style="color: red;">წარუმატებლად დასრულდა</p>');
            },
        ];
    }

}
