<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeixinController extends Controller
{

    public function checkSignature(Request $request)
    {   $echostr->$request->get('echostr');
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        if ($tmpStr == $signature) {
            echo $echostr;
        } else {
            echo "111";
        }
    }
  
}
