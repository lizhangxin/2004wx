<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class WeixinController extends Controller
{
    //调用方法
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
            file_put_contents('lzx_event.log',$xml_str);
            echo '';
            $this->responseMsg();
            $this->getweather();
        }else{
            echo "";
        }
    }
    //获取token
    public function getToken(){
        // Cache::flush();
       $key = 'wx:access_token';
       $token = Redis::get($key);
//         dd($access_token);
        if($token) {
            echo "有缓存";
            echo $token;
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
        echo 'access_token:'.$token;
    }
    //关注回复
    public function responseMsg(){
        $postStr = file_get_contents("php://input");
        $postArray= simplexml_load_string($postStr,"SimpleXMLElement",LIBXML_NOCDATA);
        if ($postArray->MsgType=="event"){
            if ($postArray->Event=="subscribe"){
                $array = ['阳光不燥微风正好', '你我山巅自相逢'];
                $content = $array[array_rand($array)];
                $this->text($postArray,$content);

            }
        }elseif ($postArray->MsgType=="text"){
            $msg=$postArray->Content;
            switch ($msg){
                case '你好';
                    $content='enen';
                    $this->text($postArray,$content);
                    break;
                case '时间';
                    $content=date('Y-m-d H:i:s',time());
                    $this->text($postArray,$content);
                    break;
                case  '天气';
                    $content = $this->getweather();
                    $this->text($postArray,$content);
                    break;
                default;
                $content='失恋小铺';
                $this->text($postArray,$content);
                break;
            }
        }
    }

    //天气接口
    public function getweather(){
        $url='http://api.k780.com/?app=weather.realtime&weaid=1&ag=today,futureDay,lifeIndex,futureHour&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json';
        $weather = file_get_contents($url);
        $weather= json_decode($weather,true);
//        dd($weather);
        if($weather['success']){
            $content = '';
            foreach($weather['result'] as $v){
                $content .='日期:'.$v['days'].$v['week'].'当日温度:'.$v['temperature'].'天气:'.$v['weather'].'风向:'.$v['wind'];
            }
        }
        return $content;
    }
    //文本回复消息
    public function text($postArray,$content){
        $toUser = $postArray->FromUserName;
        $fromUser = $postArray->ToUserName;
        $template = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <Content><![CDATA[%s]]></Content>
                                </xml>";
        $info = sprintf( $template, $toUser, $fromUser, time(), 'text', $content);
        echo $info;
    }
}


