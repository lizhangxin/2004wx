<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use DB;
use App\GooodsModel;
class XcxController extends Controller
{
    public function login(Request $request){
        //使用code
        $code = $request->get('code');
//        dd($code);
        //使用code

        $url ='https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
        $res=json_decode(file_get_contents($url),true);
        if (isset($res['errorde'])){
            $data=[
                'error'=>'50001',
                'msg'=>'登陆失败',
            ];
            return $data;
        }else{
            if (empty(DB::table("user_openid")->where("user_openid",$res["openid"])->first())){
                DB::table("user_openid")->insert(["user_openid"=>$res["openid"]]);
            }
            $token = sha1($res['openid'].$res['session_key'].mt_rand(0,999999));
            $redis_key="xcxkey:".$token;
                Redis::set($redis_key,time());
                Redis::expire($redis_key,7200);
            $date =[
                    'error'=>'0',
                    'msg'=>'登录成功',
                    'data'=>[
                        'token'=>$token
                    ]
            ];
            return $date;

        }
    }

    public function goods(){
        $goods = GooodsModel::inRandomOrder()->take('20')->get()->toArray();
//        dd($goods);
        return $goods;
    }
    public function detail(Request $request){
        $goods_id = $request->get('goods_id');
//        dd($goods_id);
        $goods_id=GooodsModel::where('goods_id',$goods_id)->first();
        return $goods_id;
    }
    public function cart(Request $request){
        $goods_id = $request->get('goods_id');
        $data = [
            'goods_id'=>$goods_id

        ];
    }

}
