<?php
use Illuminate\Support\Facades\Cache;
use Log;
function upload($filename)
{
    $file = request()->$filename;
    $path = $file->store('uploads');
    return $path;
}
function curl($url, $menu)
{
    //1.初始化
    $ch = curl_init();
    //2.设置
    curl_setopt($ch, CURLOPT_URL, $url);//设置提交地址
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $menu);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    //3.执行
    $output = curl_exec($ch);
    //4.关闭
    curl_close($ch);

    return $output;
}
function getToken()
{
    //		Cache::flush();
    $access_token = Cache::get('access_token');
    if (!$access_token) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . config('wechat.appID') . "&secret=" . config('wechat.appsecret');
        //echo $url;
        $token = file_get_contents($url);
        $token = json_decode($token, true);
        //dd($token);
        $access_token = $token['access_token'];
        Cache::put('access_token', $token['access_token'], $token['expires_in']);
    }
    return $access_token;

}
function responseImage($postarray,$media_id){
    $temple='<xml>
                              <ToUserName><![CDATA['.$postarray->FromUserName.']]></ToUserName>
                              <FromUserName><![CDATA['.$postarray->ToUserName.']]></FromUserName>
                              <CreateTime>'.time().'</CreateTime>
                              <MsgType><![CDATA[image]]></MsgType>
                              <Image>
                                <MediaId><![CDATA['.$media_id.']]></MediaId>
                              </Image>
                            </xml>';
    Log::info('====='.$temple);
    echo $temple;
}
function infocodl($postarray, $Content)
{
//                        Log::info('123');
    $ToUserName = $postarray->FromUserName;
    $FromUserName = $postarray->ToUserName;
    $time = time();
    $text = "text";
    $ret = '<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <Content><![CDATA[%s]]></Content>
                                </xml>';
    $info = sprintf($ret, $ToUserName, $FromUserName, $time, $text, $Content);
    echo $info;
}
function textimg($postarray, $Content)
{
    $ToUserName = $postarray->FromUserName;
    $FromUserName = $postarray->ToUserName;
    $time = time();
    $text = "news";
    $ArticleCount = 1;
    $ret = '<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <ArticleCount>%s</ArticleCount>
                                <Articles>
                                    <item>
                                    <Title><![CDATA[%s]]></Title>
                                    <Description><![CDATA[%s]]></Description>
                                    <PicUrl><![CDATA[%s]]></PicUrl>
                                    <Url><![CDATA[%s]]></Url>
                                    </item>
                                </Articles>
                                </xml>';
    $info = sprintf($ret, $ToUserName, $FromUserName, $time, $text, $ArticleCount, $Content['Title'], $Content['Description'], $Content['PicUrl'], $Content['Url']);
    echo $info;
}

?>
