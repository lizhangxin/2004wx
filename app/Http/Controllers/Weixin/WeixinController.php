<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use log;

class WeixinController extends Controller
{

    public function index(){
//        $res = $this->checkSignature();
//        if ($res){
//            echo $_GET["echostr"];
//        }
        $this->responseMsg();
    }
    public function checkSignature(){
        $signature = request()->get("signature");//["signature"];
        $timestamp = request()->get("timestamp");//["timestamp"];
        $nonce = request()->get("nonce");//["nonce"];

        $token = env('WX_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            echo $_GET['echostr'];
        } else {
            echo "wx";
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
    public function responseMsg(){
        $poststr = file_get_contents("php://input");
        Log::info('======'.$poststr);
        $postarray = simplexml_load_string($poststr);
        if ($postarray->MsgType=='event'){
            if ($postarray->Event=='subscribe'){
                $array = ['关注成功','你好','zzzz'];
                $Content = $array[array_rand($array)];
                infocodl($postarray,$Content);
            }
        }

    }

}


