<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;

class WeixinController extends Controller
{

    public function index(){
//        $res = $this->checkSignature();
//        if ($res){
//            echo $_GET["echostr"];
//        }
        $this->responseMsg();
        $this->infocodl();
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
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $type = $postObj->MsgType;
            $customrevent = $postObj->Event;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
       <ToUserName><![CDATA[%s]]></ToUserName>
       <FromUserName><![CDATA[%s]]></FromUserName>
       <CreateTime>%s</CreateTime>
       <MsgType><![CDATA[%s]]></MsgType>
       <Content><![CDATA[%s]]></Content>
       <FuncFlag>0</FuncFlag>
       </xml>";
            if($type=="event" and $customrevent=="subscribe"){
                $contentStr = "感谢你的关注\n回复1查看联系方式\n回复2查看最新资讯\n回复3查看法律文书";
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
            if(!empty( $keyword ))
            {
                $msgType = "text";
                if($keyword=="1"){
                    $contentStr = "qiphon";}
                if($keyword=="2"){
                    $contentStr = "test 。";}
                if($keyword=="3"){
                    $contentStr = "test333";}
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }
}


