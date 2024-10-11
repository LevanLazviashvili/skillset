<?php namespace Vdomah\JWTAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class Token extends Model
{

    public static function Gen($Data, $Exp, $Secret = false)
    {
        if (is_null($Data)) return false;

        $Token = array(
            'Data'      => $Data,
            'TokenID'   => base64_encode(random_bytes(32)),
            'IssuedAt'  => time(),
            'ExpiresAt' => time() + $Exp
        );

        $payload = JWTFactory::sub('token')->data($Token)->make();
        return JWTAuth::encode($payload)->get();
    }

}