<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
/**
 * 语音转文字控制器
 */
class TruncateController extends ShopbaseController {
	protected $nav_id = 10;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/Truncate/index'),'name'=>'语音转写'),
            array('id'=>2,'a'=>U('/Truncate/demo1'),'name'=>'语音搜索'),
            array('id'=>3,'a'=>U('/Truncate/demo2'),'name'=>'准确率报表'),
            array('id'=>4,'a'=>U('/Truncate/demo3'),'name'=>'学习报表'),
            array('id'=>5,'a'=>U('/Truncate/demo4'),'name'=>'质检报表'),
            array('id'=>6,'a'=>U('/Truncate/demo5'),'name'=>'销售漏斗'),
        );
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
//        $this->assign('second_nav_id',1);
    }
    public function demo1(){
        $this->assign('second_nav_id',2);
        $this->display();
    }
    public function demo2(){
        $this->assign('second_nav_id',3);
        $this->display();
    }
    public function demo3(){
        $this->assign('second_nav_id',4);
        $this->display();
    }
    public function demo4(){
        $this->assign('second_nav_id',5);
        $this->display();
    }
    public function demo5(){
        $this->assign('second_nav_id',6);
        $this->display();
    }
    /**
     * 语音转写列表
     * @param keywords 关键字查询
     * @param start_time 开始时间
     * @param end_time 结束时间
     */
    public function index(){
    	$keywords = I('keywords');
    	$start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p','','int') ? I('p','','int') : 1;
        $limit = I('limit','','int') ? I('limit','','int') :20;

        //where条件处理
        $keywords && $where['content'] = array('like',"%{$keywords}%");
        if($start_date && $end_date){
            $where['truncate_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['truncate_time'] = array('gt',strtotime($start_date));
            $end_date && $where['truncate_time'] = array('lt',strtotime($end_date));
        }

        //分页处理
        $count = M('device_call_text_hanyun')->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"keywords"=>$keywords,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M('device_call_text_hanyun')
                ->where($where)
                ->field("id,status,truncate_time,upload_time,type,device_call_id")
                ->order("id desc")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        if($data){
            foreach($data as $k=>$v){
                if(0 == $v['type']){
                    $call = M("device_call")->alias("a")
                    ->join("left join devices b on a.device_id = b.id")
                    ->join("left join device_line c on a.device_id = c.device_id and a.line_id = c.code")
                    ->field("b.name,c.portname,c.code,b.code as dcode")
                    ->where("a.id = {$v['device_call_id']}")
                    ->find();
                    $data[$k]['device_name'] = $call['name'];
                    $data[$k]['portname'] = $call['portname'];
                    $data[$k]['code'] = $call['code'];
                    $data[$k]['dcode'] = $call['dcode'];
                }else{
                    $call = M("device_app_call")->alias("a")
                        ->join("devices_app b on a.device_id = b.id")
                        ->field("b.name,b.code")
                        ->where("a.id = {$v['device_call_id']}")
                        ->find();
                    $data[$k]['device_name'] = $call['name'];
                    $data[$k]['portname'] = $call['name'];
                    $data[$k]['code'] = $call['code'];
                    $data[$k]['dcode'] = $call['code'];
                }
            }
        }
        //计算出现率 
        if($keywords){
            $all = M('device_call_text_hanyun')->count();
        	$round = sprintf("%.2f", $count / $all) * 100; 
        	$this->assign('round',$round);
        }
        
        $this->accountLogs->addLog("查询语音转写列表,搜索条件：转换内容：{$keywords},转写最小时间 {$start_date},转写最大时间：{$end_date}");
        $this->assign('second_nav_id',1);
        $this->assign('keywords',$keywords);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('data',$data);
        $this->assign('Page',$page->show());
        $this->display();
    }

    public function index_back(){
        $keywords = I('keywords');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p','','int') ? I('p','','int') : 1;
        $limit = I('limit','','int') ? I('limit','','int') :20;

        //where条件处理
        $keywords && $where['a.content'] = array('like',"%{$keywords}%");
        if($start_date && $end_date){
            $where['a.time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['a.time'] = array('gt',strtotime($start_date));
            $end_date && $where['a.time'] = array('lt',strtotime($end_date));
        }

        //分页处理
        $count = M('device_call_text')->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"keywords"=>$keywords,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M('device_call_text')->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->where($where)
                ->field("a.id,a.content,a.time,b.device_id,b.files,c.name")
                ->order("a.id desc")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();

        //计算出现率 
        if($keywords){
            $all = M('device_call_text')->count();
            $round = sprintf("%.2f", $count / $all) * 100; 
            $this->assign('round',$round);
        }
        
        $this->accountLogs->addLog("查询语音转写列表,搜索条件：转换内容：{$keywords},转写最小时间 {$start_date},转写最大时间：{$end_date}");
        $this->assign('keywords',$keywords);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('data',$data);
        $this->assign('Page',$page->show());
        $this->display();
    }

    /**
     * 准写列表导出
     */
    public function index_excel(){
        if(1 == I('excel')){
            $keywords = I('keywords');
            $start_date = I('start_date');
            $end_date = I('end_date');

            $keywords && $where['a.content'] = array('like',"%{$keywords}%");
            if($start_date && $end_date){
                $where['a.time'] = array('between',"{$start_date},{$end_date}");
            }else{
                $start_date && $where['a.time'] = array('gt',strtotime($start_date));
                $end_date && $where['a.time'] = array('lt',strtotime($end_date));
            }

            ini_set('memory_limit', '256M');
            ini_set("max_execution_time", "3600");

            $data = M('device_call_text')->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->where($where)
                ->field("a.id,a.content,a.time,b.device_id,b.files,c.name")
                ->order("a.id desc")
                ->select();
            header ( "Content-type:application/vnd.ms-excel" );  
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
            $head =array('设备名称','端口名称','文件名称','转写时间','文字结果');
            foreach($head as $k=>$v){
                $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
            }
            $i = 0;
            $fp = fopen('php://output', 'a');
            fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
            
            foreach($data as $k => $v){
                $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['portname']);
                $excels[$i][] = iconv('utf-8','gbk',$v['files']);
                $excels[$i][] = date("Y-m-d H:i:s",$v['time']);
                $excels[$i][] = iconv('utf-8','gbk',$v['content']);
                fputcsv($fp,$excels[$i]);
                $i++;
            }
            $this->accountLogs->addLog("导出设备状态表格");
            unset($excels);  
            ob_flush();  
            flush();    
        }else{
            $this->error("请求方式错误");
        }   
    }

    /**
     * 查看详情
     * @param id 转写详情
     */
    public function detail(){
    	$id = I("id",'','int');
    	if(!$id){
    		$this->error('请选择要查看的语音转写记录');
    	}

    	$detail = M("device_call_text_hanyun")->find($id);
        
    	if(!$detail){
            $this->error('转写记录不存在');
    	}
        $content = explode(',', $detail['content']);
        $this->assign('content',$content);
    	//查出对应的设备和记录
        $model = $detail['type'] ? M("device_app_call") : M("device_call") ;
        $act = $detail['type'] ? 'getAppFilePlayDir' : 'getFilePlayDir' ;
    	$device_call = $model->where("id = {$detail['device_call_id']}")->field("files")->find();
        $file = D("Common/DeviceCall")->$act($device_call['files']);
        $file = $file.D("Common/DeviceCall")->replaceForFilename($device_call['files']);
        $this->assign('file',$file);
        $this->accountLogs->addLog("查看语音转写详情，查看id：{$id}");
    	$this->assign('detail',$detail);
    	$this->assign('device',$device);
        $this->assign('id',$detail['device_call_id']);
    	$this->assign('device_call',$device_call);
    	$this->display();
    }

    public function detail_back(){
        $id = I("id",'','int');
        if(!$id){
            $this->error('请选择要查看的语音转写记录');
        }

        $detail = M("device_call_text")->find($id);
        if(!$detail){
            $this->error('转写记录不存在');
        }

        //查出对应的设备和记录
        $device_call = M("device_call")->where("id = {$detail['device_call_id']}")->field("device_id,files")->find();
        $device = M("devices")->where("id = {$device_call['device_id']}")->field('name')->find();
        $this->accountLogs->addLog("查看语音转写详情，查看id：{$id}");
        $this->assign('detail',$detail);
        $this->assign('device',$device);
        $this->assign('device_call',$device_call);
        $this->display();
    }

    /**
     * 语音转写 日志记录
     * @param start_time 开始时间
     * @param end_time 结束时间
     * @param account_id 操作管理员id
     */
    public function log(){
    	$start_date = I('start_date');
        $end_date = I('end_date');
        $account_id = I('account_id','','int');
        $p = I('p','','int') ? I('p','','int') : 1;
        $limit = I('limit','','int') ? I('limit','','int') :20;

    	$model = M("truncate_log");
        //where条件处理
        $where = array();
        $account_id && $where['a.account_id'] = $account_id;
        if($start_date && $end_date){
            $where['a.time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['a.time'] = array('gt',strtotime($start_date));
            $end_date && $where['a.time'] = array('lt',strtotime($end_date));
        }

        //分页处理
        $count = $model->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"account_id"=>$account_id,'start_date'=>$start_date,'end_date'=>$end_date));

        //查询列表
        $data =  $model->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->join("left join accounts d on a.account_id = d.id")
                ->where($where)
                ->field("a.id,a.file,a.status,a.time,b.device_id,c.name,d.name as aname")
                ->order("a.id desc")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        $accounts = M("accounts")->field("name,id,account")->select();
        $this->accountLogs->addLog("查询语音转写日志，查询条件：管理员id：{$account_id},日志最小时间：{$start_date},日志最大时间：{$end_date}");
        $this->assign('start_date',$start_date);
        $this->assign('accounts',$accounts);
        $this->assign('end_date',$end_date);
        $this->assign('account_id',$account_id);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->display();
    }

        /**
     * 准写列表导出
     */
    public function log_excel(){
        if(1 == I('excel')){
            $start_date = I('start_date');
            $end_date = I('end_date');
            $account_id = I('account_id','','int');

            $where = array();
            $account_id && $where['a.account_id'] = $account_id;
            if($start_date && $end_date){
                $where['a.time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
            }else{
                $start_date && $where['a.time'] = array('gt',strtotime($start_date));
                $end_date && $where['a.time'] = array('lt',strtotime($end_date));
            }

            ini_set('memory_limit', '256M');
            ini_set("max_execution_time", "3600");

            $data = M("truncate_log")->alias('a')
                ->join("left join device_call b on a.device_call_id = b.id")
                ->join("left join devices c on c.id = b.device_id")
                ->join("left join accounts d on a.account_id = d.id")
                ->where($where)
                ->field("a.id,a.file,a.status,a.time,b.device_id,c.name,d.name as aname")
                ->order("a.id desc")
                ->select();
            header ( "Content-type:application/vnd.ms-excel" );  
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
            $head =array('设备名称','端口名称','文件名称','操作人员','转写时间','转写结果');
            foreach($head as $k=>$v){
                $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
            }
            $i = 0;
            $fp = fopen('php://output', 'a');
            fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
            
            foreach($data as $k => $v){
                $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['portname']);
                $excels[$i][] = iconv('utf-8','gbk',$v['file']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['aname']);
                $excels[$i][] = date("Y-m-d H:i:s",$v['time']);
                if(1 == $v['status']){
                    $excels[$i][] = iconv('utf-8','gbk','成功');
                }else{
                    $excels[$i][] = iconv('utf-8','gbk','失败');
                }
                
                fputcsv($fp,$excels[$i]);
                $i++;
            }
            $this->accountLogs->addLog("导出设备状态表格");
            unset($excels);  
            ob_flush();  
            flush();    
        }else{
            $this->error("请求方式错误");
        }   
    }

    /**
     * 语音转文字功能 
     * @param id 记录id 
     */
    public function truncate(){
        $id = I('id');
        if($id){
            //查找记录
            $log = M("device_call")->where("id = {$id}")->getField("files");
            if($log){
                $home_url = C('home_url');
                //检测 是否转换过 没有转换过 前往转换
                $text = M("device_call_text")->where("device_call_id = {$id}")->find();
                if(!$text){ //请求接口 查看
                    $file = D("Common/DeviceCall")->getFileDir($log).$log;//要转换的文件
                    
                    if(!is_file($file)){	
                    	$this->assign('status',0);
			        	$this->assign('reason','录音文件不存在');
			        	$this->assign('name',$log->files);
                    }
                    //文件是mp3格式 不能符合标准 转换wav
                    // $filename = explode('.',$file);
                    // $filename = $filename[0].'.wav';
                    // exec("sox  {$file} -r 8000 -b 16  {$filename}");
                    $text = self::hanyun_truncate($file);
                    // $text = self::curl_truncate($filename);
                    dump($text);exit;
                    unlink($filename);
                    // $text = \Models\Handle::arc_truncate($file);
                    $data_log = array();
            		$data_log['time'] = time();
            		$data_log['account_id'] = $this->account_id;
            		$data_log['device_call_id'] = $id;
            		$data_log['file'] = $log;
                    if(0 == $text['code']){ //成功
                    	//成功后 将数据记录到数据库 并记录日志
                    	$data = array();
                    	$data['device_call_id'] = $id;
                    	$data['content'] = $text['data'];
                    	$data['time'] = time();
                        $content_id = M("device_call_text")->add($data);
                    	//记录日志
                    	$data_log['status'] = 1;
                    	$data_log['content_id'] = $content_id;
                        M("truncate_log")->add($data_log);
                    	$this->assign('text',$text['data']);
                    	$this->assign('status',1);
				        $this->assign('reason','无');
				        $this->assign('name',$log);
                    }else{ //失败
                    	$data_log['status'] = 0;
                    	$data_log['content_id'] = 0;
                    	M("truncate_log")->add($data_log);
                		$this->assign('status',0);
				        $this->assign('reason','语音转写失败');
				        $this->assign('name',$log);
	                	$this->assign('text','');
                    }
                }else{
                	$this->assign('status',1);
			        $this->assign('reason','无');
			        $this->assign('name',$log);
                	$this->assign('text',$text['content']);
                }
                $this->accountLogs->addLog('. 记录文件: '.$log.']转文字');
            }else{
            	$this->assign('status',0);
	        	$this->assign('reason','记录不存在');
	        	$this->assign('name','无');
            }
        }else{
        	$this->assign('status',0);
        	$this->assign('reason','参数错误');
        	$this->assign('name','无');
        }
        $this->assign('second_nav_id',2);
        $this->display();
    }

    /**
     * 手机语音转换测试
     */
    public function truncate_app(){
        $id = I('id');
        if($id){
            //查找记录
            $log = M("device_app_call")->where("id = {$id}")->getField("files");
            if($log){
                $home_url = C('home_url');
                if(1){ //请求接口 查看
                    $file = D("Common/DeviceCall")->getAppFileDir($log).$log;//要转换的文件
                    
                    if(!is_file($file)){    
                        $this->assign('status',0);
                        $this->assign('reason','录音文件不存在');
                        $this->assign('name',$log->files);
                    }
                    //文件是mp3格式 不能符合标准 转换wav
                    $filename = explode('.',$file);
                    $filename = $filename[0].'.wav';
                    exec("sox  {$file} -r 8000 -b 16  {$filename}");
                    // $filename = "/var/www/newweb/public/Record/sinovoice8k.wav";//测试数据
                    // $filename = "/var/www/tpxianfeng/public/Record/66161706/2018/09/26/15/ceshi2.wav";//测试数据
                    // $filename = "/var/www/tpxianfeng/public/Record/66161706/2018/09/26/15/Demo16k.wav";//测试数据
                    $text = self::curl_truncate($filename);
                    dump($text);exit;
                    unlink($filename);
                    // $text = \Models\Handle::arc_truncate($file);
                    $data_log = array();
                    $data_log['time'] = time();
                    $data_log['account_id'] = $this->account_id;
                    $data_log['device_call_id'] = $id;
                    $data_log['file'] = $log;
                    if(0 == $text['code']){ //成功
                        //成功后 将数据记录到数据库 并记录日志
                        $data = array();
                        $data['device_call_id'] = $id;
                        $data['content'] = $text['data'];
                        $data['time'] = time();
                        $content_id = M("device_call_text")->add($data);
                        //记录日志
                        $data_log['status'] = 1;
                        $data_log['content_id'] = $content_id;
                        M("truncate_log")->add($data_log);
                        $this->assign('text',$text['data']);
                        $this->assign('status',1);
                        $this->assign('reason','无');
                        $this->assign('name',$log);
                    }else{ //失败
                        $data_log['status'] = 0;
                        $data_log['content_id'] = 0;
                        M("truncate_log")->add($data_log);
                        $this->assign('status',0);
                        $this->assign('reason','语音转写失败');
                        $this->assign('name',$log);
                        $this->assign('text','');
                    }
                }else{
                    $this->assign('status',1);
                    $this->assign('reason','无');
                    $this->assign('name',$log);
                    $this->assign('text',$text['content']);
                }
                $this->accountLogs->addLog('. 记录文件: '.$log.']转文字');
            }else{
                exit("文件或者记录不存在");
                $this->assign('status',0);
                $this->assign('reason','记录不存在');
                $this->assign('name','无');
            }
        }else{
            $this->assign('status',0);
            $this->assign('reason','参数错误');
            $this->assign('name','无');
        }
        $this->assign('second_nav_id',2);
        $this->display();
    }

    /**
     * 科大讯飞 测试语音转文字
     * @param $file
     */
    function curl_truncate($file){
        $appid = '5aebccd1';
        $appkey = 'f5880b16d22fd0475c376d0619c9e1b5';
        $param = ['engine_type' => 'sms8k','aue' => 'raw'];
        $time = (string)time();
        $x_param = base64_encode(json_encode($param));
        $header_data = [
            'X-Appid:'.$appid,
            'X-CurTime:'.$time,
            'X-Param:'.$x_param, 
            'X-CheckSum:'.md5($appkey.$time.$x_param),        
            'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
        ];    //Body
        $file_content = file_get_contents($file);
        $body_data = 'audio='.urlencode(base64_encode($file_content));    //Request
        $url = "http://api.xfyun.cn/v1/service/v1/iat";   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_data);    
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,1);
    }

    /**
     * 接通华声 语音转文字测试 分成四段请求
     */
    public function arc_truncate($file){
        // header("Content-Type:application/x-www-form-urlencoded;charset=utf-8");
        $appkey = '445d5457';
        $devkey = "2f1a6f0bd65603e99149f59d8078f2e8";
        $version = "5.0";
        $udid = "101:1234567890";
        $date = date("Y-m-d H:i:s",time());
        $time = time();
        // $capkey = "capkey=asr.cloud.freetalk,audioformat=pcm16k16bit,domain=telecom,realtime=yes,index=1,identify={$identify}";
        $sessionKey = md5($date.$devkey);
        $udid  = "101:1234567890";
        $format = "json";
        $json_long = "1024";
        $xcontent = 0;
        //文件转字节流
        $by = 

        //Body
        $file_content = file_get_contents($file);
        $capkey = "capkey=asr.cloud.freetalk,audioformat=pcm8k16bit,domain=telecom,realtime=yes,index=-1,identify={$time}";
             $header_data = [
                'x-app-key:'.$appkey,
                'x-sdk-version:'.$version,
                'x-request-date:'.$date,
                'x-task-config:'.$capkey,
                'x-udid:'.$udid,
                'x-session-key:'.$sessionKey,
            ];
        $a = self::curl_post($header_data,$file_content);    
        dump($a);exit;


        $bytes = self::getBytes($file_content); //将音频文件转换为byte数组
        $bytes1 = array();
        foreach($bytes as $k=>$v){
            $str = "[";
            foreach ($v as $val) {
                $str .= "{$val},";
            }
            $str = substr($str,0,strlen($str)-1); 
            $str .= "]";
            $bytes1[$k] = $str;
        }
        //分成四次请求
        foreach($bytes1 as $k=>$v){
            if(count($bytes1) == $k){
                $identify = -$k;
            }else{
                $identify = $k;
            }
            $capkey = "capkey=asr.cloud.freetalk,audioformat=pcm8k16bit,domain=telecom,realtime=yes,index={$identify},identify={$time}";
             $header_data = [
                'x-app-key:'.$appkey,
                'x-sdk-version:'.$version,
                'x-request-date:'.$date,
                'x-task-config:'.$capkey,
                'x-udid:'.$udid,
                'x-session-key:'.$sessionKey,
            ];
            $data[$k] = self::curl_post($header_data,$v);
        }
        dump($data);exit;
    }
    public function test(){
        $id= $_GET['id'];
        if(0 == $type){ //设备的语音转写
            $log = M("device_call")->where("id = {$id}")->getField("files");
        }else{ //app手机的语音转写
            $log = M("device_app_call")->where("id = {$id}")->getField("files");
        }
        if($log) {
            $text = M("device_call_text_hanyun")->where("device_call_id = {$id} and type= '{$type}'")->find();
            if (!$text) {
                if (0 == $type) {
                    $file = D("Common/DeviceCall")->getFileDir($log) . $log;//要转换的文件
                } else {
                    $file = D("Common/DeviceCall")->getAppFileDir($log) . $log;//要转换的文件
                }
                if (!is_file($file)) {
                    $data['code'] = 100;
                    $data['msg']  = "转写失败，录音文件不存在";
                }
                //                        $text = self::hanyun_truncate($file);
                $text = $this->bdSendFileToText($file, $id, $type);
                var_dump($text);die;
            }
        }
    }
     /**
     * 语音转文字功能 
     * @param id 记录id 
     */
    public function truncate_hanyu(){
        $data = array();
        if(IS_POST){
            $id = I('id');
            $type = I('type',0,'int');
            if(!$id){
                $data['code'] = 100;
                $data['msg'] = "转写失败,缺少请求参数";
            }else{
                if(0 == $type){ //设备的语音转写
                    $log = M("device_call")->where("id = {$id}")->getField("files");
                }else{ //app手机的语音转写
                    $log = M("device_app_call")->where("id = {$id}")->getField("files");
                }
                if($log){
                    $text = M("device_call_text_hanyun")->where("device_call_id = {$id} and type= '{$type}'")->find();
                    if(!$text){
                        if(0 == $type){
                            $file = D("Common/DeviceCall")->getFileDir($log).$log;//要转换的文件
                        }else{
                            $file = D("Common/DeviceCall")->getAppFileDir($log).$log;//要转换的文件
                        }
                        if(!is_file($file)){    
                            $data['code'] = 100;
                            $data['msg'] = "转写失败，录音文件不存在";
                        }
//                        $text = self::hanyun_truncate($file);
                        $text = $this->bdSendFileToText($file,$id,$type);
                        $text = json_decode($text,1);
                        $data_log = array();
                        $data_log['time'] = time();
                        $data_log['account_id'] = $this->account_id;
                        $data_log['device_call_id'] = $id;
                        $data_log['file'] = $log;
                        if(1 == $text['status']){ //成功
                            //成功后 将数据记录到数据库 并记录日志
                            $data = array();
                            $data['device_call_id'] = $id;
                            $data['upload_time'] = time();
                            $data['uid'] = $text['uid'];
                            $data['type'] = $type;
                            $content_id = M("device_call_text_hanyun")->add($data);
                            //记录日志
                            $data_log['status'] = 1;
                            $data_log['content_id'] = $content_id;
                            M("truncate_log")->add($data_log);
                            $data['code'] = 200;
                            $data['msg'] = "转写成功，正在转换中";
                        }else{ //失败
                            $data_log['status'] = 0;
                            $data_log['content_id'] = 0;
                            M("truncate_log")->add($data_log);
                            $data['code'] = 100;
                            $data['msg'] = "转写失败，录音文件上传失败";
                        }
                    }else{
                        $data['code'] = 100;
                        if('0' == $text['status']){
                            $data['msg'] = "该记录正在转换中，请耐心等待";
                        }else{
                            $data['msg'] = "该记录已经转写成功";
                        }
                    }
                }else{
                    $data['code'] = 100;
                    $data['msg'] = "转写失败,记录不存在";
                }
            }
        }else{
            $data['code'] = 100;
            $data['msg'] = "转写失败,请求不合法";
        }
        echo json_encode($data);exit;
    }
    public function bdSendFileToText($filePath, $callId, $type = 0)
    {
        $filePathWav = substr($filePath,0,strlen($filePath)-4).'.wav';;
        if (is_file($filePathWav) && stripos($filePath,'.wav')<0){
            chmod($filePathWav,'0755');
            unlink($filePathWav);
        }
        exec("sox  {$filePath} -r 8000 -b 16  {$filePathWav}");
        require_once SITE_PATH . 'application/Common/Common/bdAiCallCenter.php';
        $filePathWav = 'http://39.105.2.43:82/' . str_replace(SITE_PATH,'',$filePathWav);
        $r = toTextBD($filePathWav, 'call_' . (int)$type . '_' . $callId);
        $r= json_decode($r,true);
        //hanyun  status 0 失败 1 成功 跟百度相反
        $r['status'] = $r['status'] ? 0 : 1;
        //hanyun 使用 uid 百度使用 callId
        $r['uid']='bd_call_'.$type.'_'.$callId;
        return json_encode($r);
    }
    /**
     * 汉云语音转文字
     * @param RESTful post请求方式
     * @param http://106.15.40.207/asrs/public/home/api/testUploadFile
     * @param username xianfeng
     * @param password xianfeng_hanyun_asrs
     * @param file 文件
     */
    public function hanyun_truncate($file){
        // $url = "http://106.15.40.207/asrs/public/home/api/testUploadFile";
        $url = "http://106.15.40.207/asrs/public/home/api/uploadFile";
        $data = file_get_contents($file);
        $uid = uniqid();
        $header = array(
                    "Content-Type: audio/mp3", //文件类型
                    "username: xianfeng", //用户名
                    "password: " . md5("xianfeng_hanyun_asrs"), //密码
                    "uniqueid: " . $uid//唯一id
                );
        $timeout = 30;
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        if ($header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header); // 一个用来设置HTTP头字段的数组
        }
    //    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:49.0) Gecko/20100101 Firefox/49.0"); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 允许重定向
        curl_setopt($curl, CURLOPT_MAXREDIRS, 100); //允许重定向 的最大次数
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); //重定向时自动设置Referer
        if (empty($data)) {
            //有些服务器只能接收单一个请求,就要设置些参数 如nodejs http://www.baidu.com 只支持get请求
            //值为GET"，"POST"，"CONNEC
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        }
        if (empty($GLOBALS['cookie_file'])) {
            curl_setopt($curl, CURLOPT_COOKIEFILE, '/tmp/cokie'); // 读取上面所储存的Cookie信息
        } else {
            curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']); // 读取上面所储存的Cookie信息
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_NOBODY, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $response = curl_exec($curl); // 执行操作
        $curl_info = curl_getinfo($curl);
        $error = '';
        if (curl_errno($curl)) {
            $error = curl_errno($curl) . ' Errno ' . curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        curl_close($curl); // 关键CURL会话
        $return_body = substr($response, $curl_info['header_size']);
        $return_body = json_decode($return_body,1);
        $return_body['uid'] = $uid;
        return json_encode($return_body);    
    }


    protected static function curl_post($header_data,$body_data){
        $url = "api.hcicloud.com:8880/asr/Recognise";  
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_data);
        
        $result = curl_exec($ch);
        $data['header'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);    
        curl_close($ch);
        $data['result'] = $result;
        return $data;
    }

    

    protected static function getBytes($str) {
        $len = strlen($str);
        $bytes = array();
        for($i=0;$i<$len;$i++) {
           if(ord($str[$i]) >= 128){
               $byte = ord($str[$i]) - 256;
               // $byte = ord($str[$i]).' '.ord($str[++$i]);
           }else{
               $byte = ord($str[$i]);
           }
               // $bytes[] = ord($string[$i]); 
            $bytes[] =  $byte;
        }
        
        // $count = floor(count($bytes) / 6400);//数字长度 // $len = 6400; //每段数组的长度
        $count = 4; //将数组进行切分
        $len = ceil(count($bytes) / $count); //每一段的数组长度
        $data = array();
        for($i=1;$i<=$count;$i++){ //切割成多个小数组
            if( $count== $i){
                $arr= array_slice($bytes,$len * ($i-1));
            }else{
                $arr = array_slice($bytes,$len * ($i-1),$len);
            }
            $data[$i] = $arr;
        }
        return $data;
    }
}