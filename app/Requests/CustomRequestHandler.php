<?php

namespace App\Requests;
class CustomRequestHandler
{

    public static function getUser($request){
        return $request->getAttribute("jwt")['jti'];
    }
    public static function getParam($request,$key,$default=null)
    {
        $postParams = $request->getParsedBody();

        $getParams = $request->getQueryParams();
        
        $serverParams = $request->getServerParams();

        $getBody = json_decode($request->getBody(),true);
      
        $result = $default;
        if(is_array($serverParams) && isset($serverParams[$key])){
            $result = $serverParams[$key];
        }
        else if(is_array($postParams) && isset($postParams[$key]))
        {
            $result = $postParams[$key];

        }else if(is_object($postParams) && property_exists($postParams, $key))
        {
            $result = $postParams->$key;
        }
        else if(is_array($getBody) && isset($getBody[$key]))
        {
            $result = $getBody[$key];

        }else if(isset($getParams[$key]))
        {
            $result = $getParams[$key];
        }
        return $result;
    }

 

    

}