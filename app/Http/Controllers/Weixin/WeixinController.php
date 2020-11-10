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
            $this->createMenu();
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
        $url='http://api.k780.com/?app=weather.realtime&weaid=1&ag=today,futureDay,lifeIndex,futureHour&appkey=53296&sign=8a16a77a58bc523e3f63a65d696a3fef&format=json';
        $weather=file_get_contents($url);
        $weather=json_decode($weather,true);
        if($weather['success']){
            $content = '';
            $v=$weather['result']['realTime'];
                $content .= "日期:".$v['week']."当日温度:".$v['wtTemp']."天气:".$v['wtNm']."风向:".$v['wtWindNm'];

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
    public function createMenu()
    {
        $data = '{
                     "button":[
                     {
                          "type":"click",
                          "name":"今日歌曲",
                          "key":"V1001_TODAY_MUSIC"
                      },
                      {
                           "name":"菜单",
                           "sub_button":[
                           {
                               "type":"view",
                               "name":"搜索",
                               "url":"http://www.soso.com/"
                            },
                            {
                                 "type":"miniprogram",
                                 "name":"wxa",
                                 "url":"http://mp.weixin.qq.com",
                                 "appid":"wx286b93c14bbf93aa",
                                 "pagepath":"pages/lunar/index"
                             },
                            {
                               "type":"click",
                               "name":"赞一下我们",
                               "key":"V1001_GOOD"
                            }]
                       }]
                 }';
        $access_token=$this->getToken();
        echo $access_token;die;
        $url ='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;

        $res=$this->curl($url, $data);
        dd($res);
    }
    public function curl($url,$menu){
        //1.初始化
        $ch = curl_init();
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//设置提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//设置返回值返回字符串
        curl_setopt($ch,CURLOPT_POST,1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);//上传的文件
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);//过滤https协议
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//过滤https协议
        //3.执行
        $output = curl_exec($ch);
        //关闭
        curl_close($ch);
        return $output;
    }
}


