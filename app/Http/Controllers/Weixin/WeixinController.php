<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeixinController extends Controller
{

    public function checkSignature(Request $request){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

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
    public function index(){
        $this->createMenu();
        $this->getToken();
//         $res=$this->checkSignature();
//         if($res){
//             echo $_GET["echostr"];
//         }
    }

    //自定义菜单
    public function createMenu(){
        $menu = '{
            "button":[
            {
                "type":"click",
                "name":"优惠活动",
                "key":"V1001_TODAY_MUSIC"
            },
            {
                "name":"个人中心",
                "sub_button":[
                {
                    "type":"view",
                    "name":"今日抽奖",
                    "url":"http://lizhangxin.guojunlong.shop/luck/getuserinfo"
                    },
                    {
                    "type":"click",
                    "name":"中奖信息",
                    "key":"http://lizhangxin.guojunlong.shop/luck/list"
                    }]
                }]
        }';
        $access_token = $this->getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $res = $this->curl($url,$menu);
        dd($res);
    }

    //微信服务器post提交
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

    //获取token
    public function getToken(){
        // Cache::flush();
        $access_token = Cache::get('access_token');
        // dd($access_token);
        if(!$access_token){
            // echo "11";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('luck.appID')."&secret=".config('luck.appsecret');
            // dd($url);
            $token = file_get_contents($url);
            $token = json_decode($token,true);
            //dd($token);
            $access_token = $token["access_token"];
            Cache::put("access_token",$token["access_token"],$token["expires_in"]);
        }
        return $access_token;
    }

    //获取code
    public function getUserInfo(){
        $redirect_url = urlencode('http://lizhangxin.guojunlong.shop/luck/getuseropenid');
        $str="asdfghjklqwerty12345678";
        $str=substr(str_shuffle($str),0,3);
        //dd($str);
        //静授权的snsapi_base
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxe2be2bf26023d30b&redirect_uri='.$redirect_url.'&response_type=code&scope=snsapi_base&state='.$str.'#wechat_redirect';
//        echo $url;
//        dd(123);
        header('location:'.$url);
    }

    //静默授权
    public function getuseropenid(){
        $code = request()->code;
        //dd($code);
        //echo "==yyyyy==";
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.config('luck.appID').'&secret='.config('luck.appsecret').'&code='.$code.'&grant_type=authorization_code';
        $result = file_get_contents($url);
        Log::info("=====".$result);
        $result = json_decode($result,true);
        //dd($result);
        $openid= $result['openid'];
        return view('Luck/luck',['openid'=>$openid]);


    }
    //执行抽奖
    public function store(){
        $openid=request()->name;
        Cache :: has($openid)?cache::increment($openid):Cache::put($openid,1,60*60*1);
        $count = Cache::get($openid);
        if($count>=3){
            dd('一天只能抽三次,谢谢参与');
        }

        $array=['一等奖','二等奖','三等奖','谢谢参与'];
        $name = $array[array_rand($array)];
        $data=[
            'openid'=>$openid,
            'name'=>$name
        ];
        $res =Openid::create($data);
        dd($res);
    }
    //获取抽奖信息
    public function list(){
        $openid='o_j-I1SrsX4uJ2PsLP-C-Wkqa4Xs';
        $res=Openid::where('openid',$openid)->get();
        dd($res);
    }
}


