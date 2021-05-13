<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 手机上传
 */
class AppController extends Controller{
    private $_token_key = 'HVbXAIUT1G2GRmzW'; //加密token 用于验证机制
    private $_token_open = false;

    function _initialize() {
        $this->_tokenCheck();
    }

   
    /**
     * 事件上传
     * * @param event_type 事件类型 3=提机拨号,4=拨号内容,5=来电振铃,6=开始录音,7=来电接听,8=去电挂机/接听完毕,10表示来电  9表示去电,11=未接来电,12去电未接,,13 未接主动挂断 14=来电号码  50=现场视频,60 现场录音
     * @param device_id 设备编号 code
     * @param line 端口号
     * @param date 时间
     * @param voltage
     * @param ringcnt
     * @param caller 电话号
     * @param duration
     * @param token 验证秘钥
     */
    public function Event(){
        $device_id = I('device_id');
    	$token = I('request.token');   // md5(event_type+device_id+line+HVbXAIUT1G2GRmzW)
        $event_type = I('request.event_type');
        $device_code = I('request.device_id');
        $caller = I('request.caller'); //来电号码,来电接听,接听完毕,未接来电
        $FilePath = I('request.FilePath');
        $duration = I('request.duration'); //录音时长
        $TimeLong = I('request.timeLong'); //通话时间
        $add_time = I('request.createdtime');//生成记录时间
        $date = I('request.stime');//录音开始时间
        //有录音文件但是没有 电话 表明是现场录音
        if($FilePath&&empty($caller)){
           $event_type=60;
        }
        // $canshus = $_REQUEST;
        // $text = "";
        // foreach($canshus as $k=>$v){
        // 	$text .= "{$k}=>{$v}";
        // }
        // file_put_contents("./test.txt", $text,FILE_APPEND);
        if (empty($device_code)  || empty($event_type)   ||  $device_code=='18487963616' ) {
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递设备编码device_id或者时间类型event_type'),'JSON');
        }
        if(!$add_time){
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递生成记录时间'),'JSON');exit;
        }
		
		
		
		
		
        $line = 1;
        $md5_token = md5($event_type.$device_code.$line.$this->_token_key);
        if ( $token != $md5_token && $this->_token_open == true ) {
            $this->ajaxReturn(array('code'=>'9999','msg'=>'没有传递token或者token验证失败'),'JSON');exit;
        }

        //60 代表现场录音
        $call_types = array(9,10,11,60,12,13);
        if(!in_array($event_type,$call_types)){
        	$this->ajaxReturn(array('code'=>'0001','msg'=>'记录类型不符合规定'),'JSON');exit;
        }
        if(9 == $event_type || 10 == $event_type){ //去电 来电 必须传递相关参数
        	if(!$caller || !$add_time){
        		$this->ajaxReturn(array('code'=>'0001','msg'=>'来电或者去电必须上传通话人、文件路径、录音时间'),'JSON');exit;
        	}
        }
        $caller = str_replace(array('A','B'), array('*','#'), $caller);

        //判断设备是否存在
        $device_id = M("devices_app")->where("code = {$device_code}")->getField("id");
        if(!$device_id){ //设备不存在 进行添加
        	$data = array();
        	$data['code'] = $device_code;
        	$data['registered'] = 0;
        	$data['closed'] = 0;
        	$data['add_time'] = time();
        	$data['keywords'] = $device_code;
        	$device_id = M("devices_app")->add($data);
        	unset($data);
        	if(!$device_id){
        		$this->ajaxReturn(array('code'=>'9999','msg'=>'添加设备失败'),'JSON');exit;
        	}else{
        		M("device_stat")->add(array("device_id"=>$device_id));
        	}
        }

        //验证是否重复上传
        if(M("device_app_call")->where(array('device_id'=>$device_id,'type'=>$event_type,'add_time'=>$add_time))->find()){
            $this->ajaxReturn(array('code'=>'0000','msg'=>'记录已经长传过'),'JSON');exit;
        }
        //处理数据统计
        //对设备统计进行数据处理
        $device_stat = M("device_app_stat")->where("device_id = {$device_id}")->getField('id');
        if(!$device_stat){
            $device_stat = M("device_app_stat")->add(array('device_id'=>$device_id));
        }
        $model = M("device_app_stat");
        $model->device_id = $device_id;
        $model->id = $device_stat;
        $model->last_time = time();
        switch($event_type){
            case 10:
            	$model->comeing = array('exp','comeing + 1');
            break;
            case 9:
            	$model->outgoing = array('exp','`outgoing`+ 1');
            break;
            case 11:
            $model->missed = array('exp','missed + 1');
            break;
            case 12:
                $model->missed = array('exp','missed + 1');
                break;
            case 13:
                $model->missed = array('exp','missed + 1');
                break;

        }
        $date && $data['device_time'] = $date;
        $date && $model->device_time = $date;
        $model->save();
        //进行添加
        

        //如果通话人是通讯录中 更新铜须路
        $caller && $contact_id = M("contacts")->where("tel1 = {$caller}")->getField("id");
        if($contact_id){
        	$model = M("contact_stat");
        	$model->id = $contact_id;
            switch($event_type){
                case 10:
                    $model->comeing = array('exp','comeing + 1');//来电
                break;
                case 9:
                    $model->outgoing = array('exp','`outgoing`+ 1');//去电
                break;
                case 11:
                    $model->missed = array('exp','missed + 1');//未接
                break;
                case 12:
                    $model->missed = array('exp','missed + 1');//未接
                    break;
                case 13:
                    $model->missed = array('exp','missed + 1');//未接
                    break;
                case 50:
                	$model->videod = array('exp','videod + 1');
                break;
            }
            $model->last_time = time();
            $model->save();
        }

        //设备记录统计
        $data = array();
        $data['keywords'] =  $caller.','.$device_id;
        $data['device_id'] = $device_id;
        $data['type'] = $event_type;
        $duration && $data['recording_time'] = round($duration/1000,0);
        $data['add_time'] = $add_time;
        $TimeLong && $data['call_time'] = round($TimeLong/1000,0);
        $contact_id && $data['contact_id'] = $contact_id;
        $FilePath && $data['files'] = $FilePath;
        $caller && $data['tel'] = $caller;
        $data['call_date'] = $add_time;
        $date && $data['stime'] = $date;
        $data['note']=I('note');
        $data['record_stop_time']=(int)I('record_stop_time');
        $data['record_start_time']=(int)I('record_start_time');
        $data['tel_name']=I('tel_name');
        $data['longitude']=I('longitude');
        $data['latitude']=I('latitude');
        $data['location']=I('location');
        $call_id = M("device_app_call")->add($data);
        $this->_updateAppCallData($data['device_id'],$data['type'],$data['call_time'],$data['recording_time'],$data['add_time']);
        unset($data);
        if($call_id){
        	$this->ajaxReturn(array('code'=>'0000','msg'=>'通话记录上传成功'),'JSON');exit;
        }else{
        	$this->ajaxReturn(array('code'=>'0008','msg'=>'通话记录上传失败'),'JSON');exit;
        }
    }

    public function test(){
    }
     /**
     * 文件上传
     * @param device_id 设备编码
     * @param createdtime 生成记录时间
     */
     public function Files(){
     	$code = I('request.device_id'); //设备的id
         $duration = I('request.duration'); //录音时长
         $TimeLong = I('request.timeLong'); //通话时间
        $call_time = I('request.createdtime'); //生成记录时间戳
        if(!$code){
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递设备编码'),'JSON');exit;
        }
        if(!$call_time){
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递记录生成时间'),'JSON');exit;
        }
        $device = M("devices_app")->where(array('code'=>$code))->find();
        if(!$device){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'设备编码错误，查询不到设备'),'JSON');exit;
        }
        if(!$_FILES){
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有文件上传'),'JSON');exit;
        }
        if (!is_array($_FILES['files']) || !is_uploaded_file($_FILES['files']['tmp_name'])) {
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有文件files上传'),'JSON');exit;
        }
        $call = M("device_app_call")->where(array('device_id'=>$device['id'],'add_time'=>$call_time))->find();
        if(!$call){
            $this->ajaxReturn(array('code'=>'0001','msg'=>'没有对应文件的记录'),'JSON');exit;
        }
        $date = date('YmdHis',$call_time); //当前的时间
        $filename = $code.'-'.$date.'-'.uniqid('xf-upload');//修改后的文件名称
         //上传的文件名 有空格 导致无法获取到后缀
         if(I('fileName')){
             $oldFileName=I('fileName');
         }else{
             $oldFileName=$_FILES['files']['name'];
         }
        //修改数据库
        $a = explode('.',$oldFileName);

        //判断上传的类型 如果是amr 格式*（手机播放格式 ） 需要程序转换成mp3格式
        if(false && 'amr' == $a[1]){
            $type = 'mp3';
        }else{
            $type = $a[count($a)-1];
        }
        $filename_c = $filename;
        $filename = $filename.'.'.$type;
        $filename1 = $filename_c.'.'.sox[1];
        $data=[];
         $duration && $data['recording_time'] = round($duration/1000,0);
         $TimeLong && $data['call_time'] = round($TimeLong/1000,0);
         $data['files']=$filename;
        if(!M("device_app_call")->where("id = {$call['id']}")->save($data)){
            $this->ajaxReturn(array('code'=>'0002','msg'=>'文件上传失败--修改数据库失败'),'JSON');exit;
        }
        //处理文件名称
        $file_dir = $this->getAppFileDir($filename); //   年/月/日/时/端口号/文件
        $full_dirs = UPLOAD_APP_PATH.'/'.$file_dir;
        if (!is_dir($full_dirs)) {
            $is = mkdir($full_dirs,0777,true);
			         if(!$is){
                $this->ajaxReturn(array('code'=>'0001','msg'=>'创建文件夹失败'),'JSON');exit;
            }
        }
        $upload_file = $full_dirs.$filename1;
        $upload_file1 = $full_dirs.$filename;
        $move_upload = @move_uploaded_file($_FILES['files']['tmp_name'], $upload_file);
        if ( empty($move_upload) ) {
            $this->ajaxReturn(array('code'=>'0002','msg'=>'文件上传失败--临时文件移动到永久位置失败'),'JSON');exit;
        }else{
            //上传成功  如果是amr 转换成mp3格式, 不在上传时转成mp3，在播放时再转
            if(false && 'amr' == $a[1]){
//                $str = "ffmpeg -i {$upload_file} {$upload_file1}";
                if (strtoupper(substr(PHP_OS,0,3))==='WIN'){
                    $str = "D:\sox\sox-14-4-2\sox {$upload_file} {$upload_file1}";
                }else{
                    $str = "sox {$upload_file} {$upload_file1}";
                }
                exec($str);
//                if(file_exists($upload_file1)){
//                    unlink($upload_file);
//                }
            }
            $this->ajaxReturn(array('code'=>'0000','msg'=>'文件上传成功'),'JSON');exit;
        }
     }
     protected function _shardFilesCheck(){
         $code = I('request.device_id'); //设备的id
         $duration = I('request.duration'); //录音时长
         $TimeLong = I('request.timeLong'); //通话时间
         $call_time = I('request.createdtime'); //生成记录时间戳
         $shard_index=I('shard_index');
         $shard_count=I('shard_count');
         $filename=I('fileName');
    
         if($shard_index===''||!$shard_count){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有文件分片下标、分片总数'),'JSON');exit;
         }
         if(!$code){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递设备编码'),'JSON');exit;
         }
         if(!$filename){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递文件名称'),'JSON');exit;
         }
         if(!$call_time){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有传递记录生成时间'),'JSON');exit;
         }
         if(!$TimeLong || !$duration){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有通话时长、录音时长'),'JSON');exit;
         }
         if(!$_FILES['shard_file']){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有分片内容上传'),'JSON');exit;
         }
         $device = M("devices_app")->where(array('code'=>$code))->find();
         if(!$device){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'设备编码错误，查询不到设备'),'JSON');exit;
         }
         $call = M("device_app_call")->where(array('device_id'=>$device['id'],'add_time'=>$call_time))->find();
         if(!$call){
             $this->ajaxReturn(array('code'=>'0001','msg'=>'没有对应文件的记录'),'JSON');exit;
         }
         return $call;
     }
    public function shardFiles(){
        $code = I('request.device_id'); //设备的id
        $duration = I('request.duration'); //录音时长
        $TimeLong = I('request.timeLong'); //通话时间
        $call_time = I('request.createdtime'); //生成记录时间戳
        $shard_index=I('shard_index');
        $shard_count=I('shard_count');
        $fileName=I('fileName');
        $call = $this->_shardFilesCheck();
        //文件名有 xf-upload-ok 表示已经上传完成
        if(stripos($call['files'],'xf-upload-ok')!==false){
            $this->ajaxReturn(array('code'=>'0000','msg'=>'文件上传过！'),'JSON');exit;
        }
        
        $whereExist=['app_call_id'=>$call['id'],'shard_index'=>$shard_index];
        $exist = M('app_shard_file')->where($whereExist)->find();
        if(!$exist){
            $tmpFilePath=$_FILES['shard_file']['tmp_name'];
            $fp=fopen($tmpFilePath,'r');
            $shard_content = fread($fp, filesize($tmpFilePath));
            $appendData=['addtime'=>date('Y-m-d H:i:s'),
                         'shard_count'=>$shard_count,'shard_content'=>$shard_content];
            M('app_shard_file')->add(array_merge($whereExist,$appendData));
        }
        $allShardCount=M('app_shard_file')->where(['app_call_id'=>$call['id']])->count();
        //分片未完全上传完成时，不执行后续文件生成
        if($allShardCount!=$shard_count){
            $this->ajaxReturn(array('code'=>'0000','msg'=>'文件分片上传成功'),'JSON');
            exit;
        }
        //分片上传完成，文件开始生成
        $date = date('YmdHis',$call_time); //当前的时间
        //上传的文件名 有空格 导致无法在tmp文件中 获取到后缀
        //修改数据库
        $a = explode('.',$fileName);
        $filename = $code.'-'.$date.'-'.uniqid('xf-upload');//修改后的文件名称
        if( 'amr' == $a[1]){
           // $type = 'mp3';
            $type = $a[count($a)-1];
        }else{
            $type = $a[count($a)-1];
        }

        $filename.='.'.$type;
        $data=[];
        $duration && $data['recording_time'] = round($duration/1000,0);
        $TimeLong && $data['call_time'] = round($TimeLong/1000,0);
        $data['files']=$filename;
        $data['yun_files_prefix']=(string)$_ENV['ALI_OSS']['PATH_PREFIX'];
        $filesInDb=$call['files'];
        if(stripos($filesInDb,'xf-upload')===false){
            if(!M("device_app_call")->where("id = {$call['id']}")->save($data)){
                $this->ajaxReturn(array('code'=>'0002','msg'=>'文件上传失败--修改数据库失败'),'JSON');
                exit;
            }
        }else{
            $filename=$filesInDb?$filesInDb:$filename;
        }
        //开始上传
        $shards=M('app_shard_file')->where(['app_call_id'=>$call['id']])->order('shard_index asc')->select();
        $fileContent='';
        foreach ($shards as $v){
            $fileContent.=$v['shard_content'];
        }
    
        //处理文件名称
        $file_dir = $this->getAppFileDir($filename); //   年/月/日/时/端口号/文件
        $full_dirs = UPLOAD_APP_PATH.'/'.$file_dir;
        if (!is_dir($full_dirs)) {
            $is = mkdir($full_dirs,0777,true);
            if(!$is){
                $this->ajaxReturn(array('code'=>'0001','msg'=>'创建文件夹失败'),'JSON');exit;
            }
        }
        $upload_file = $full_dirs.$filename;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs, 0777, true);
        }
        // 文件上传过判断
        //文件名有 xf-upload-ok 表示已经上传完成
        $files=M('device_app_call')->where(['id'=>$call['id']])->getField('files');
        //根据最新的文件名判断是否上传过
        if(stripos($files,'xf-upload-ok')!==false){
            $this->ajaxReturn(array('code'=>'0000','msg'=>'文件已经上传过！'),'JSON');exit;
        }
        $upload_file=str_replace('xf-upload','xf-upload-ok',$upload_file);
        $move_upload= file_put_contents($upload_file,$fileContent);
        if (!$move_upload) {
            $file=str_replace('xf-upload-ok','xf-upload',$filename);
            M('device_app_call')->where(['id'=>$call['id']])->save(['files'=>$file]);
            $this->ajaxReturn(array('code'=>'0002','msg'=>'文件上传失败--临时文件移动到永久位置失败'),'JSON');exit;
        }else{
            $file=str_replace('xf-upload','xf-upload-ok',$filename);
            M('device_app_call')->where(['id'=>$call['id']])->save(['files'=>$file]);
            M('app_shard_file')->where(['app_call_id'=>$call['id']])->delete();
            $this->ajaxReturn(array('code'=>'0000','msg'=>'文件上传成功'),'JSON');exit;
        }
    }
    /**
     * 更新当天device_app_call_data表
     * @param $deviceId
     * @param $type  10表示来电  9表示去电,11=未接来电,14=来电号码  50=现场视频 28=留言，语音
     * @param $callTime
     * @param $recordingTime
     * @param $addTime
     * @return bool
     */
    private function _updateAppCallData($deviceId,$type,$callTime,$recordingTime,$addTime){
        $data=[
            'comeing'=>0,'comeing_time'=>0,'outgoing'=>0,'outgoing_time'=>0,
            'missed'=>0,'audio'=>0,'message'=>0,'vedio'=>0,'call_time'=>(int)$callTime,
            'recording_time'=>(int)$recordingTime,'all'=>1
        ];
        if($type==9){
            $data['outgoing']=1;
            $data['outgoing_time']=(int)$callTime;
        }elseif($type==10){
            $data['comeing']=1;
            $data['comeing_time']=(int)$callTime;
        }elseif($type==11){
            $data['missed']=1;
        }elseif($type==50){
            $data['vedio']=1;
        }elseif($type==28){
            $data['audio']=1;
        }else{
            return false;
        }
        
        $time = date('Y-m-d',$addTime);
        $con=['device_id'=>$deviceId,'time'=>$time];
        $record = M('device_app_call_data')->field('id',true)->where($con)->find();
        if($record){
            foreach ($data as $k=>$v){
                $record[$k] = $record[$k]+$v;
            }
            M('device_app_call_data')->where($con)->save($record);
        }else{
            M('device_app_call_data')->add(array_merge($data,$con));
        }
        //更新记录总览
        $totalCon = ['type'=>1,'device_id'=>0];
        $totalRecord =  M('device_app_call_data')->field('id',true)->where($totalCon)->limit(0,1)->find();
        if($totalRecord){
            foreach ($data as $k=>$v){
                $totalRecord[$k] = $totalRecord[$k]+$v;
            }
            M('device_app_call_data')->where($totalCon)->save($totalRecord);
        }else{
            M('device_app_call_data')->add(array_merge($data,$totalCon));
        }
        return true;
    }
     /**
     * 手机设备录音文件名称处理为路径
     * @param filename 文件名称
     */
    function getAppFileDir($filename){
        if ( empty($filename) ) {
            return false;
        }
        $file_dir = substr($filename,0,11).'/'.substr($filename,12,4).'/'.substr($filename,16,2).'/'.substr($filename,18,2).'/'.substr($filename,21,2).'/';
        return $file_dir;
    }
    
    public function _tokenCheck(){
        $params=I('');
        if(!$params['token1']){
           return 1;
        }
        if(abs(time()-$params['timestamp'])>600){
            exit(['code'=>300,'msg'=>'token timestamp overtime!']);
        }
        $token=md5( $params['timestamp']. $_SERVER['HTTP_USER_AGENT'] . $this->_token_key);
        if($token!=$params['token1']){
            exit(['code'=>300,'msg'=>'token error']);
        }
    }
    public function recordList(){
        $code = I('code');
        $code = $code ? $code : I('device_id');
        $start_call_time = I('start_call_time');
        $start_call_time = $start_call_time ? strtotime($start_call_time) : '';
        $end_call_time = I('end_call_time');
        $end_call_time= $end_call_time ? strtotime($end_call_time) : '';
        $max_recording = I('max_recording','','int');
        $min_recording = I('min_recording','','int');
        $tel = I('tel');
        $search_type = I('event_type');
        $sort = I('sort') ? 'asc' : 'desc';
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        $device_id=M('devices_app',null)->where(['code'=>$code])->getField('id');
        $where=['device_id'=>$device_id];
        $start_call_time && $where['call_date']=['egt',$start_call_time];
        $end_call_time && $where['call_date']=['elt',$end_call_time];
        $min_recording && $where['recording_time']=['egt',$min_recording];
        $max_recording && $where['recording_time']=['elt',$max_recording];
//        $tel&&$where['tel']=['like','%'.$tel.'%'];
        $tel&&$where['tel']=['eq',$tel];
        $search_type && $where['type']=['in',$search_type];
        //分页处理
        $count=M('device_app_call',null)->where($where)->count();
        //查询列表    record_stop_time
        $data=M('device_app_call',null)->where($where)->limit(($p-1)*$limit,$limit)->order(' add_time '.$sort)->select();
        //print_r(M()->_sql());
        foreach ($data as $k=>$v){
            $data[$k]['stime1']=date('Y-m-d H:i:s',$v['stime']);
//            $data[$k]['call_time']=date('Y-m-d H:i:s',$v['call_time']);
            $data[$k]['call_date1']=date('Y-m-d H:i:s',$v['call_date']);

//         
            if($v['files']){
                $data[$k]['filePath']=@D("Common/DeviceCall")->getAppFilePlayDir($v['files']);;
                $data[$k]['filePath'] = 'http://' .$_SERVER['HTTP_HOST'].str_replace('/index.php','',$_SERVER['SCRIPT_NAME']) . trim($data[$k]['filePath'],'.').$v['files'];
            }

        }
        $r['pageInfo']=[
            'pages'=>ceil($count/$limit),
            'count'=>$count,
            'page'=>$p,
            'limit'=>$limit,
        ];
        $r['recordList']=(array)$data;
        exit(json_encode(['code'=>'0000','data'=>$r,'msg'=>'success']));
    }
    public function updateLastTime(){
	
        $code = I('code');
        $revision = I('revision');
        $modeln = I('modeln');
        $endtime = I('endtime');
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
		
	
        $deviceId=M('devices_app',null)->where(['code'=>$code])->getField('id');
        M('device_app_stat',null)->where(['device_id'=>$deviceId])->save(['last_time'=>time(),'revision'=>$revision,'modeln'=>$modeln,'endtime'=>$endtime]);
		
        exit(json_encode(['code'=>'0000','msg'=>'success']));
		/*
		
        $code = I('code');
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        $deviceId=M('devices_app',null)->where(['code'=>$code])->getField('id');
        M('device_app_stat',null)->where(['device_id'=>$deviceId])->save(['last_time'=>time()]);
        exit(json_encode(['code'=>'0000','msg'=>'success']));
		*/
    }
}
