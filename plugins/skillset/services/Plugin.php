<?php namespace skillset\Services;

use Cms\Traits\ApiResponser;
use GuzzleHttp\Exception\ClientException;
use http\Exception\BadConversionException;
use Illuminate\Support\Facades\App;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    use ApiResponser;
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function boot()
    {
//        App::error(function(\Exception $exception) {
//            $Code = $exception->getCode();
//            $Code = $Code && is_int($Code) ? $Code : 407;
//            if (env('APP_DEBUG') AND $exception->getCode() !== config('app.default_error_code')) {
//                return $this->errorResponse($exception->getMessage() . ' ' . $exception->getFile() . ':' . $exception->getLine(). '            '.$exception->getTraceAsString(), $Code);
//            }
//            return $this->errorResponse($exception->getMessage(), $Code);
//        });
    }
}
