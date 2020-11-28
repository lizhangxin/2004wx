<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\GooodsModel;
use App\CollectModel;
use App\User_openidModel;
class XcxController extends Controller
{
    public function login(Request $request){
        //使用code
        $code = $request->get('code');
//        dd($code);
        //使用code
        $userinfo =json_decode(file_get_contents("php://input"),true);
        $url ='https://api.weixin.qq.com/sns/jscode2session?appid='.env('WX_XCX_APPID').'&secret='.env('WX_XCX_SECRET').'&js_code='.$code.'&grant_type=authorization_code';
        $data=json_decode(file_get_contents($url),true);
        if (isset($res['errcode'])){ //有错误
            $response=[
                'error'=>'500001',
                'msg'=>'登陆失败',
            ];
        }else{
            $openid=$data['openid'];
            $u = User_openidModel::where(['openid'=>$openid])->first();
            if ($u) {
                //老用户
                $user_id=$u['user_id'];
            }else{
                $u_info=[
                    'openid'=>$openid,
                    'nickname'=>$userinfo['u']['nickName'],
                    'language'=>$userinfo['u']['language'],
                    'city'=>$userinfo['u']['city'],
                    'province'=>$userinfo['u']['province'],
                    'country'=>$userinfo['u']['country'],
//                    'headimgurl'=>$userinfo['u']['headimgurl'],
                    'add_time'=>time(),
//                    'type'=>3
                ];
                $user_id=User_openidModel::insertGetId($u_info);
            }
            $token = sha1($data['openid'].$data['session_key'].mt_rand(0,999999));
            $redis_key="xcxkey:".$token;
            $loginInfo=[
                'uid'=>$user_id,
                'user_name'=>'黎明',
                'login_time'=>time(),
                'login_ip'=>$request->getClientIp(),
                'token'=>$token,
                'openid'=>$openid
            ];
                Redis::hMset($redis_key,$loginInfo);
                //设置时间
                Redis::expire($redis_key,7200);
            $response =[
                    'error'=>'0',
                    'msg'=>'登录成功',
                    'data'=>[
                        'token'=>$token
                    ]
                ];
            }
        return $response;
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

    /**
     * 收藏
     * @param Request $request
     * @return array
     */
    public function add_fav(Request $request){
        $goods_id=$request->get('id');
        $token=$request->get('token');
        $key="xcxkey:".$token;
        //取出openid
        $token=Redis::hgetall($key);
        $user_id=User_openidModel::where('openid',$token['openid'])->select('user_id')->first()->toArray();

        $data=[
            'goods_id'=>$goods_id,
            'add_time'=>time(),
            'user_id'=>$user_id['user_id']
        ];
        $res=CollectModel::insert($data);

        if($res){
            $respones=[
                'error'=>0,
                'msg'=>'收藏成功',
            ];
        }else{
            $respones=[
                'error'=>50001,
                'msg'=>'收藏失败',
            ];
        }
        return $respones;
    }
    //取消收藏
    public function no_fav(Request $request){
        $goods_id=$request->get('id');
        $token=$request->get('token');
        $key="xcxkey:".$token;
        $token=Redis::hgetall($key);
//        dd($token);
//        echo $token;exit;
        $user_id=User_openidModel::where('openid',$token['openid'])->value('user_id');
//        print_r($user_id);
        $res=CollectModel::where(['user_id'=>$user_id,'goods_id'=>$goods_id])->delete();
        if($res){
            $respones=[
                'error'=>0,
                'msg'=>'取消收藏成功',
            ];
        }else{
            $respones=[
                'error'=>50001,
                'msg'=>'取消收藏失败',
            ];
        }
        return $respones;

    }

}
