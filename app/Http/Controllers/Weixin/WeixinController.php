<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;

class WeixinController extends Controller
{

    public function checkSignature(){
        $signature = request()->get("signature");//["signature"];
        $timestamp = request()->get("timestamp");//["timestamp"];
        $nonce = request()->get("nonce");//["nonce"];

        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if( $tmpStr == $signature ){  //验证通过
            // 1接收数据
            $xml_str = file_get_contents("php://input");
            //接收日志
            file_put_contents('lzx.event.log',$xml_str);
            echo '';
            die;
        }else{
            echo "";
        }
    }

    public function getToken(){
        // Cache::flush();
       $key = 'wx:access_token';
       $token = Redis::get($key);
//         dd($access_token);
        if($token) {
            echo "有缓存";
            echo '<br>';
        }else{
            echo "没有缓存";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('APPID')."&secret=".env('WX_APPSEC');
            // dd($url);
            $res = file_get_contents($url);
            $data = json_decode($res,true);
            //dd($token);
            $token = $data["access_token"];

            Redis::set($key,$token);
            Redis::expire($key,3600);
        }
        echo 'access_token'.$token;
    }
}


