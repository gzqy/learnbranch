<?php

namespace Api\Controller;

use Think\Controller;

class AudioToTextController extends Controller
{
    public function callBackBD()
    {
        //        $s='{"appId":16709401,"userId":16709401,"callId":"11","category":"TXT","content":"[ { \"sentence\":\"您好什么意思\", \"roleCategory\":\"client\", \"snStartTime\":\"00:01.760\", \"snStopTime\":\"00:03.520\", \"duration\":\"2\" } , { \"sentence\":\"您好什么意思\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:01.760\", \"snStopTime\":\"00:03.520\", \"duration\":\"2\" } , { \"sentence\":\"哎喂您好我想咨询一下就是那个你们电话接有带录音功能的你们这边广州有没有经销商呢呃您要哪个型号的呀\", \"roleCategory\":\"client\", \"snStartTime\":\"00:04.00\", \"snStopTime\":\"00:14.560\", \"duration\":\"10\" } , { \"sentence\":\"哎喂您好我想咨询一下就是那个你们电话接有带录音功能的你们这边广州有没有经销商呢呃您要哪个型号的呀\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:04.00\", \"snStopTime\":\"00:14.560\", \"duration\":\"10\" } , { \"sentence\":\"对型号的话您看暂时还没有定就是您这边\", \"roleCategory\":\"client\", \"snStartTime\":\"00:15.199\", \"snStopTime\":\"00:20.00\", \"duration\":\"5\" } , { \"sentence\":\"对型号的话您看暂时还没有定就是您这边\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:15.199\", \"snStopTime\":\"00:20.00\", \"duration\":\"5\" } , { \"sentence\":\"嗯您是您\", \"roleCategory\":\"client\", \"snStartTime\":\"00:20.320\", \"snStopTime\":\"00:22.399\", \"duration\":\"2\" } , { \"sentence\":\"嗯您是您\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:20.320\", \"snStopTime\":\"00:22.399\", \"duration\":\"2\" } , { \"sentence\":\"您您是也是先生还是直接用户\", \"roleCategory\":\"client\", \"snStartTime\":\"00:22.559\", \"snStopTime\":\"00:25.600\", \"duration\":\"3\" } , { \"sentence\":\"您您是也是先生还是直接用户嗯我这边需要跟客户的哦那就咱们先生呗先生少的话这样您就告诉我大概需求我给您推荐一下然后直接给您一个咱们那个电商的价格不就行了吗\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:25.920\", \"snStopTime\":\"00:37.759\", \"duration\":\"12\" } , { \"sentence\":\"嗯我这边需要跟客户的哦那就咱们先生呗先生少的话这样您就告诉我大概需求我给您推荐一下然后直接给您一个咱们那个电商的价格不就行了吗嗯可以啊是不是啊就是他可能就是那个客户的话可能就是他只要里面更换的话有录音功能这些就可以了很简单的最基础版的就行了呗\", \"roleCategory\":\"client\", \"snStartTime\":\"00:38.790\", \"snStopTime\":\"00:51.359\", \"duration\":\"13\" } , { \"sentence\":\"嗯可以啊是不是啊就是他可能就是那个客户的话可能就是他只要里面更换的话有录音功能这些就可以了很简单的最基础版的就行了呗\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:38.790\", \"snStopTime\":\"00:51.359\", \"duration\":\"13\" } , { \"sentence\":\"嗯要不然你这边帮给我推推荐一款比较基础本金付款的然后还有一个稍微点了终端一点点的那那我想问一下客户有多少个电话进行录音呢\", \"roleCategory\":\"client\", \"snStartTime\":\"00:51.840\", \"snStopTime\":\"01:04.959\", \"duration\":\"13\" } , { \"sentence\":\"嗯要不然你这边帮给我推推荐一款比较基础本金付款的然后还有一个稍微点了终端一点点的那那我想问一下客户有多少个电话进行录音呢\", \"roleCategory\":\"agent\", \"snStartTime\":\"00:51.840\", \"snStopTime\":\"01:04.959\", \"duration\":\"13\" } , { \"sentence\":\"呃这边的话暂时还没签没电他就是说先让我们给他报价然后他他要那个对明白是这样子的因为咱们家里也不只有电话机咱们家还有录音系统左右录音系统就是说像一个设备我就可以接很多个电话那种可以集中管理的嗯或者这样他可能就是要变成可能就是只要电话可以吧明白没问题的那这样吧咱们有扣扣或微信吗加一下我给您推荐两款电话机然后呢顺便会把录音系统也推荐给您如果有其他客户其他项目呢咱们可以一起合作吗是吧\", \"roleCategory\":\"client\", \"snStartTime\":\"01:05.599\", \"snStopTime\":\"01:40.799\", \"duration\":\"35\" } , { \"sentence\":\"呃这边的话暂时还没签没电他就是说先让我们给他报价然后他他要那个对明白是这样子的因为咱们家里也不只有电话机咱们家还有录音系统左右录音系统就是说像一个设备我就可以接很多个电话那种可以集中管理的嗯或者这样他可能就是要变成可能就是只要电话可以吧明白没问题的那这样吧咱们有扣扣或微信吗加一下我给您推荐两款电话机然后呢顺便会把录音系统也推荐给您如果有其他客户其他项目呢咱们可以一起合作吗是吧\", \"roleCategory\":\"agent\", \"snStartTime\":\"01:05.599\", \"snStopTime\":\"01:40.799\", \"duration\":\"35\" } , { \"sentence\":\"可以啊您您的微信号码是多少我在呃您加我吧那个8020\", \"roleCategory\":\"client\", \"snStartTime\":\"01:40.959\", \"snStopTime\":\"01:46.400\", \"duration\":\"6\" } , { \"sentence\":\"可以啊您您的微信号码是多少我在呃您加我吧那个8020\", \"roleCategory\":\"agent\", \"snStartTime\":\"01:40.959\", \"snStopTime\":\"01:46.400\", \"duration\":\"6\" } , { \"sentence\":\"80207982\", \"roleCategory\":\"client\", \"snStartTime\":\"01:47.400\", \"snStopTime\":\"01:49.920\", \"duration\":\"2\" } , { \"sentence\":\"80207982\", \"roleCategory\":\"agent\", \"snStartTime\":\"01:47.400\", \"snStopTime\":\"01:49.920\", \"duration\":\"2\" } , { \"sentence\":\"7982嗯我是李晓燕您怎么称呼\", \"roleCategory\":\"client\", \"snStartTime\":\"01:50.560\", \"snStopTime\":\"01:54.790\", \"duration\":\"4\" } , { \"sentence\":\"7982嗯我是李晓燕您怎么称呼\", \"roleCategory\":\"agent\", \"snStartTime\":\"01:50.560\", \"snStopTime\":\"01:54.790\", \"duration\":\"4\" } , { \"sentence\":\"不是罗罗罗罗罗罗女士啊女士行嘞那您加我吧我的话一会儿您把公司名称告诉我我给您申请一个最低的价格就完了咱们集中上的价格就没有中间环节的好吗好好好好祝女士您加我吧加油咱们那个扣扣说微信是吧好吧\", \"roleCategory\":\"client\", \"snStartTime\":\"01:54.400\", \"snStopTime\":\"02:12.960\", \"duration\":\"18\" } , { \"sentence\":\"不是罗罗罗罗罗罗女士啊女士行嘞那您加我吧我的话一会儿您把公司名称告诉我我给您申请一个最低的价格就完了咱们集中上的价格就没有中间环节的好吗好好好好祝女士您加我吧加油咱们那个扣扣说微信是吧好吧\", \"roleCategory\":\"agent\", \"snStartTime\":\"01:54.400\", \"snStopTime\":\"02:12.960\", \"duration\":\"18\" } , { \"sentence\":\"好好好拜拜嗯再见\", \"roleCategory\":\"client\", \"snStartTime\":\"02:13.120\", \"snStopTime\":\"02:16.319\", \"duration\":\"3\" } , { \"sentence\":\"好好好拜拜嗯再见\", \"roleCategory\":\"agent\", \"snStartTime\":\"02:13.120\", \"snStopTime\":\"02:16.319\", \"duration\":\"3\" } ]","triggerTime":1562295742183,"logId":"62c6d41f-3e80-4ec3-bb31-e47e5d1cb452"}';
        $s = file_get_contents("php://input");
//        file_put_contents(SITE_PATH . '/data/runtime/bdcall.log', date('Y-m-d H:i:s') . "\n" . $s . "\n", FILE_APPEND);
        file_put_contents(SITE_PATH . '/data/runtime/lingban2.log', date('Y-m-d H:i:s') . "\n" . $s . "\n", FILE_APPEND);
        
        $s  = json_decode($s, true);
        if($s['category']!='TXT'){
            return false;
        }
        $c  = json_decode($s['content'], true);
        $i  = 0;
        $cc = '';
        foreach ($c as $v) {
            $t  = $i % 2 == 0 ? 'A' : 'B';
            $cc .= $t . '：' . $v['sentence'] . ',';
            ++$i;
        }
        $cc = trim($cc, ',');
        $callId = explode('_', $s['callId']);
        
        $con = [
            'device_call_id' => $callId[3],
//            'uid'=>$s['callId'],
            'type'           =>'' . (int)$callId[2] ,
        ];
        //type 0 设备 1 app
        if ($callId[3]) {
            $log  = M('device_app_call',null)->field('files')->where(['id' => $callId[3]])->getField('files');
            $file = D("Common/DeviceCall")->getFileDir($log) . $log;//要转换的文件
        } else {
            $log  = M('device_call')->field('files')->where(['id' => $callId[3]])->getField('files');
            $file = D("Common/DeviceCall")->getAppFileDir($log) . $log;//要转换的文件
        }
        $fileWav = substr($file, 0, strlen($file) - 4) . '.wav';
        chmod($fileWav, '0755');
        unlink($fileWav);
        M('device_call_text_hanyun',null)->where($con)->save(['status' => $cc ? 1 : 0, 'content' => $cc, 'truncate_time' => time()]);
        return true;
    }
    
    /**
     * https://ai.baidu.com/docs/#/BICC-ASR-API/64471c3e
     * 结果查询接口
     */
    public function getTextBD()
    {
        require_once SITE_PATH . 'application/Common/Common/bdAiCallCenter.php';
        $device_call_id = $_REQUEST['device_call_id'];
        $type           = (int)$_REQUEST['type'];
        $r = getTextBD('bd_call_'.(int)$type.'_'.$device_call_id);
        if($_REQUEST['dump']){
            echo $r;die;
        }
        return $r;
    }
    
    public function sendLingBan(){
        $id=$_REQUEST['id'];
//        $v=M('device_call',null)->where(['id'=>(int)$id])->find();
//        $dir      = D("Common/DeviceCall")->getFileDir($v['files']);
//        $id = '66160435-20191031111056-O-L03-EN-13770708453.wav';
//        $filepath = $dir.$v['files'];
        $filepath = '/var/www/tpxianfeng/application/Api/Controller/cuishou/' . $id;
        if(!is_file( $filepath )){
            echo 'file no exist';die;
        }
        list($msec, $sec) = explode(' ', microtime());
    
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $accessKey = '1234567890';
        $param=[
//            'appid'=>'ling_asr',
            'appid'=>'5a87825b03044f5c874e2de84db68b17',
            'timestamp'=>(int)$msectime,
            'signType'=>'normal',
//            'signature'=>'1234567890',
            'signature'=>'15ff5c306d144f44b07e5118629c686e',
            
            'audioType'=>'mp3',
            'bizInfo'=>['call_id'=>$id],
            'contents'=>'',
            "callbackUrl"=>"https://pioneerw.vicp.io//index.php?s=/Api/AudioToText/callBackBD"
        ];
//        $param['signature']=md5($param['appid'] .$param['timestamp'] .$accessKey);
        $param['contents']=base64_encode(file_get_contents($filepath));
        if($_REQUEST['dump']){
            echo json_encode($param);die;
        }
        //异步接口
        $url='http://api.utterance.lingban.cn/openapi/v1/utterance/lapse';
        $r=$this->curlpage($url,json_encode($param),10,true,['Content-Type: application/json;charset=UTF-8']);
        echo $r;
    }
    public function curlpage($url, $postdata = '', $timeout = 2, $post = true, $header = array(), $cookie = '')
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
