<?php
/**
 * 百度智能呼叫中心，音频文件转写
 * 需要外网可以访问的的地址才行
 */
defined('BD_APP_ID') or define('BD_APP_ID', '16709401');
defined('BD_API_KEY') or define('BD_API_KEY', '9YZmrK19YxnzMLPT1vCcscSs');
defined('BD_SECRET_KEY') or define('BD_SECRET_KEY', 'fMcoHdmKDs4bx2kYURhkU0e9GrrgiFEN');

function getTokenBD()
{
    $tokenFile = './data/runtime/bdToken.log';
    $r         = file_get_contents($tokenFile);
    $r         = json_decode($r, true);
    if ($r['request_time'] && $r['access_token'] && ($r['expires_in'] + $r['request_time']) > time()) {
        return $r['access_token'];
    }
    
    $url                        = 'https://aip.baidubce.com/oauth/2.0/token';
    $post_data['grant_type']    = 'client_credentials';
    $post_data['client_id']     = BD_API_KEY;//'你的 Api Key';
    $post_data['client_secret'] = BD_SECRET_KEY;//'你的 Secret Key';
    
    $res                 = curlpage($url, $post_data, 2, true);
    $res                 = json_decode($res, true);
    $token               = $res['access_token'];
    $res['request_time'] = time();
    file_put_contents($tokenFile, json_encode($res));
    return $token;
}

/**
 * https://ai.baidu.com/docs/#/BICC-ASR-API/top
 */
function toTextBD($file, $deviceCallId)
{
    $accessToken = getTokenBD();
    $url         = "https://aip.baidubce.com/rpc/2.0/session/offline/upload/asr?access_token={$accessToken}";
    $post_data   = [
        'appId'         => BD_APP_ID,   //appId	用户百度云申请appId	必选	Long
//        bd_0_1233333 0 设备 1 app  123333语音记录id
        'callId'        => 'bd_' . $deviceCallId,  //唯一电话识别参数,建议使用UUID,不超过128位,业务方保证(appId,callId)联合唯一	必选	String
        'companyName'   => 'pioneerw',   //录音所属公司	必选	String
        'agentFileUrl'  => $file,    //	用户销售侧文件存储URL或者单个文件的混音文件	必选	String
        'clientFileUrl' => $file,   //用户客户侧文件存储URL	非必选	String
        'callbackUrl'   => 'http://39.105.2.43:82/index.php?s=/Api/AudioToText/callBackBD',     //用户获取翻译结果回调接口,若填写则通过地址回调，若不填则须客户使用查询结果接口进行查询	非必选	String
        'suffix'        => 'wav',    //wav
    ];
    
    $r = curlpage($url, json_encode($post_data), 2, true, 'Content-Type:application/json; charset=utf-8');
    return $r;
}

function getTextBD($callId)
{
    $accessToken = getTokenBD();
    $url         = "https://aip.baidubce.com/rpc/2.0/search/info?access_token={$accessToken}";
    $post_data   = [
        'category' => 'OFFLINE_ASR_RESULT',
        'paras'    => [
            'appId'  => BD_APP_ID,
            'callId' => $callId
        ],
    ];
    return curlpage($url, json_encode($post_data), 2, true, 'Content-Type:application/json; charset=utf-8');
}

if (!function_exists('curlpage')) {
    function curlpage($url, $postdata = '', $timeout = 2, $post = true, $header = array(), $cookie = '')
    {
        $timeout = (int)$timeout;
        
        if (!$timeout || empty($url)) return false;
        
        $ch = curl_init();
        if (!$post && $postdata) {
            rtrim($url, '?');
            $mark = strrpos($url, '?') ? '&' : '?';
            $url  .= $mark . http_build_query($postdata);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        //返回的内容作为变量储存，而不是直接输出
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);           //设置cURL允许执行的最长秒数
        // 	curl_setopt($ch, CURLOPT_NOSIGNAL,1);                  //注意，毫秒超时一定要设置这个
        // 	curl_setopt($ch, CURLOPT_TIMEOUT_MS,200);              //超时毫秒，cURL 7.16.2中被加入。从PHP 5.2.3起可使用
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        }
        
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            if ($header) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        }
        
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
