<?php namespace skillset\Orders;

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
            'special_order_statuses' => function($value) {
                $map = [
                    0 => '<div style="color:red">გაუქმებულია</div>',
                    1 => '<div style="color:orange">სამუშაოები დაიწყო</div>',
                    2 => '<div style="color:orange">მიღება ჩაბარება გაფორმდა</div>',
                    3 => '<div style="color:orange">დამკვეთმა მიიღო სამუშაოები (გადაუხდელია)</div>',
                    4 => '<div style="color:green">შეკვეთა გადახდილია</div>',
                ];
                return Arr::get($map, $value);
            },
            'special_payment_type'  => function($value) {
                $map = [
                    0 => 'ნაღდი ანგარიშსწორებით',
                    1 => 'ბარათით'
                ];
                return Arr::get($map, $value);
            }
        ];
    }
}
