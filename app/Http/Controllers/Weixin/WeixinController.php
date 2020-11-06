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
    public function responseMsg()
    {
        $poststr = file_get_contents("php://input");
        Log::info('=======' . $poststr);
        $postarray = simplexml_load_string($poststr);
        if ($postarray->MsgType == 'event') {
            if ($postarray->Event == 'subscribe') {
                // 返回需要身份的一个转变
                $array = ['阳光不燥微风正好', '你我山巅自相逢', 'gai溜子'];
                $Content = $array[array_rand($array)];
                infocodl($postarray, $Content);
            }
            if ($postarray->Event == 'CLICK') {
                $eventkey = $postarray->EventKey;
                switch ($eventkey) {
                    case 'V1001_TODAY_MUSIC':
                        $array = ['无心斗艳', '消失的眼角膜'];
                        $Content = $array[array_rand($array)];
                        infocodl($postarray, $Content);
                        break;
                    case  'V1001_GOOD':
                        $count = Cache::add('good', 1) ?: Cache::increment('good');
                        $Content = '点赞人数:' . $count;
                        infocodl($postarray, $Content);
                        break;

                    default:

                        break;
                }

            }
        } elseif ($postarray->MsgType == 'text') {
            $msg = $postarray->Content;
            switch ($msg) {
                case '1':
                    $Content = '李章鑫，傻子凯，傻子龙';
                    infocodl($postarray, $Content);
                    break;
                case '2':
                    $Content = '李章鑫';
                    infocodl($postarray, $Content);
                    break;
                case '表白了':
                    $Content = '表白成功';
                    infocodl($postarray, $Content);
                    break;
                case '小姐姐':
                    $Content = '小姐姐：表白成功';
                    infocodl($postarray, $Content);
                    break;
                default:
                    $Content = 'sorry';
                    infocodl($postarray, $Content);
                    break;
            }
        }
    }



}
