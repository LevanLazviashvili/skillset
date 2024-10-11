<?php
namespace skillset\Configuration\Traits;
use Illuminate\Support\Arr;
use skillset\Configuration\Models\Configuration;
use skillset\Configuration\Models\RateCommissionConfiguration;

trait Config
{
    private $appConfig;

    public function getConfig($param = null, $default = null)
    {
        if (!$this->appConfig) {
            $this->setConfig();
        }
        if ($param) {
            return Arr::get($this->appConfig, $param, $default);
        }
        return $this->appConfig;
    }

    private function setConfig()
    {
        $this->appConfig = Configuration::all()->pluck('value', 'key')->toArray();
    }

    public function getRateCommissionConfig()
    {
        return (new RateCommissionConfiguration)->orderBy('rate')->pluck('percent','rate');
    }

    public function getRateCommission($Rate)
    {
        foreach ($this->getRateCommissionConfig() AS $Range => $Commission) {
            if ($Range >= $Rate) {
                return $Commission;
            }
        }
    }
}