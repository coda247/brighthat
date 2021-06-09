<?php
namespace App\Controllers;

use App\Interfaces\SecretKeyInterface;
use Firebase\JWT\JWT;
use App\Requests\CustomRequestHandler;

class GenerateTokenController implements SecretKeyInterface
{
    public static function generateToken($email)
    {
        $now = time();
        $future = strtotime('+24 hour',$now);
        $secretKey = self::JWT_SECRET_KEY;
        $payload = [
         "jti"=>$email,
         "iat"=>$now,
         "exp"=>$future
        ];

        return "Bearer ".JWT::encode($payload,$secretKey,"HS256");
    }
}