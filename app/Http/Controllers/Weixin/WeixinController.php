<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\UserModel;
use App\MediaModel;
//use GuzzleHttp\Client;
use GuzzleHttp\Client;
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
            Log::info($xml_str);
            //接收日志
            file_put_contents('lzx_event.log',$xml_str);
            echo '';
            $this->responseMsg();
            $this->getweather();
            $this->custom();
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
            echo $token;
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('APPID')."&secret=".env('WX_APPSEC');
            // dd($url);
            $res = file_get_contents($url);
            $data = json_decode($res,true);
            //dd($token);
            $token = $data["access_token"];

            Redis::set($key,$token);
            Redis::expire($key,3600);
        }
        return $token;
    }
    //关注回复
    public function responseMsg(){
        //获取微信post数据 xml(格式)
        $postStr = file_get_contents("php://input");
        $postArray= simplexml_load_string($postStr,"SimpleXMLElement",LIBXML_NOCDATA);
        $toUser= $postArray->FromUserName;//openid
        //evnet  判断是不是推送事件
        if ($postArray->MsgType=="event"){
            if ($postArray->Event=="subscribe") {
                $WeachModelInfo = UserModel::where('openid', $toUser)->first();
                if (is_object($WeachModelInfo)) {
                    $WeachModelInfo = $WeachModelInfo->toArray();
                }
                if (!empty($WeachModelInfo)) {
                    $content = "欢迎回来";
                } else {
                    $array = ['阳光不燥微风正好', '你我山巅自相逢'];
                    $content = $array[array_rand($array)];
                    $token = $this->getToken();
                    $data = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $token . "&openid=" . $toUser . "&lang=zh_CN";
                    file_put_contents('user_wetch', $data);//存文件
                    $wetch = file_get_contents($data);
                    $json = json_decode($wetch, true);
                    $data = [
                        'openid' => $toUser,
                        'subscribe' => $json['subscribe'],
                        'nickname' => $json['nickname'],
                        'sex' => $json['sex'],
                        'city' => $json['city'],
                        'country' => $json['country'],
                        'province' => $json['province'],
                    ];
                    $weachInfo = UserModel::insert($data);
                }
                $this->text($postArray, $content);
            }
            if ($postArray->Event == 'CLICK') {
                if ($postArray->EventKey == 'weather') {
                    //调用天气
                    $content = $this->getweather();
                    $this->text($postArray, $content);
                }
            }
            if ($postArray->Event == 'CLICK') {
                if ($postArray->EventKey == 'checkin') {
                    $key = 'USER_SIGN_' . date('Y_m_d', time());
                    $content = '签到成功';
                    $user_sign_info = Redis::zrange($key, 0, -1);
                    if(in_array((string)$toUser,$user_sign_info)){
                        $content='已经签到，不可重复签到';
                    }else{
                        Redis::zadd($key,time(),(string)$toUser);
                    }
                    $result= $this->text($postArray, $content);
                    return $result;
                }

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
            }
        }
        if (!empty($postArray)){
            $data = $postArray->MsgType;
            switch ($data){
                case 'video';
                    $this->videohandler($postArray);
                    break;
                case 'voice';
                    $this->voicehandler($postArray);
                    break;
                case  'text';
                    $this->texthandler($postArray);
                    break;
                case  'image';
                    $this->picture($postArray);
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
    //菜单
    public function custom(){
        $access_token=$this->getToken();
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
//        echo $url;
        $array=[
            'button'=>[
                [
                    'type'=>'click',
                    'name'=>"jd.2004",
                    'key'=>'k_jd_2004',
                ],
                [
                    'name'=>'菜单',
                    "sub_button"=>[
                        [
                            'type'  => 'click',
                            'name'  => '传图',
                            'key'   => 'uploadimg'
                        ],
                        [
                            'type'  => 'click',
                            'name'  => '天气',
                            'key'   => 'weather'
                        ],
                        [
                            'type'  => 'click',
                            'name'  => '签到',
                            'key'   => 'checkin'
                        ]
                    ]
                ]
            ]
        ];
//        $array->toArray();
        $client=new Client();

        $response=$client->request('POST',$url,[
            'verify'=>false,
            'body'=>json_encode($array,JSON_UNESCAPED_UNICODE)
        ]);
        $data=$response->getBody();
        echo $data;
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
    //视频入库
    public function videohandler($postArray){
        $data = [
            'add_time'=>$postArray->CreateTime,
            'media_type'=>$postArray->MsgType,
            'media_id'=>$postArray->MediaId,
            'msg_id'=>$postArray->MsgId,
        ];
        MediaModel::insert($data);
    }
    //音频
    public function voicehandler($postArray){
        $data = [
            'add_time'=>$postArray->CreateTime,
            'media_type'=>$postArray->MsgType,
            'media_id'=>$postArray->MediaId,
            'msg_id'=>$postArray->MsgId,
        ];
        MediaModel::insert($data);
    }
    //文本
    public function texthandler($postArray){
        $data = [
            'media_url'=>$postArray->CreateTime,
            'media_type'=>$postArray->MsgType,
            'openid'=>$postArray->FromUserName,
            'msg_id'=>$postArray->MsgId,
        ];
        MediaModel::insert($data);
    }
    //图片
    public function picture($postArray){
        $data = [
            'media_url'=>$postArray->PicUrl,
            'media_type'=>'image',
            'add_time'=>time(),
            'openid'=>$postArray->FromUserName,
        ];
        MediaModel::insert($data);
    }

}
