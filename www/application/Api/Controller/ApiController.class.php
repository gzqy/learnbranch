<?php
namespace Api\Controller;
use Think\Controller;
use Common\Utils\MailUtil;
class ApiController extends Controller{
    private $_token_key = 'HVbXAIUT1G2GRmzW'; //加密token 用于验证机制
    private $_token_open = false;
    function _initialize() {
    }

    public function test(){
        $data = I('data');
        foreach($data as $k=>$v){
            file_put_contents('./test.text',date().$k.'=>'.$v.'<br/>');
        }
    }

    /**
     * 汉云语音转文字异步回调接口
     */
    public function hanyunAsyncCallBack(){
        $content = file_get_contents("php://input");
        $content = json_decode($content,1);
        //结果处理
        $data = array();
        if($content['fanyi_result']){
            $data['content'] = $content['fanyi_result'];
            $data['status'] = '1';
            $data['truncate_time'] = time();
            M("device_call_text_hanyun")->where("uid = '{$content['unique_id']}'")->save($data);
        }
        echo 'success';
    }

    /**
     * D系列 录音仪 记录上传接口
     * @param serial_no 设备编码
     * @param rec_channel 通道号
     * @param rec_start_time_secs 开始时间
     * @param rec_duration_secs 通话时长 s
     * @param rec_direction 呼叫类型，in 呼入；out 呼出；unkown，未接
     * @param rec_callid_dialed 电话号码
     * @param extnumber 分机号
     * @param id 录音编号
     * @param recorded 是否录音,1有录音文件；0无
     * @param satellite_hash 内容校验码
     * @param file 录音文件
     */
    public function D_event(){
        // echo json_encode(array('status'=>0,'next_id'=>1));exit;
        $id = I('record_id');
        $status = json_decode(htmlspecialchars_decode(I('status')),1);//设备及端口的一些状态
        // if(!$status){
        //     //定时上传 设备10点到12点
        //     $hour = date("G",time());//当前小时
        //     if($hour >17 or $hour <16){  //不在规定时间内
        //         $id = I('record_id');         
        //         echo json_encode(array('status'=>0,'period'=>'16-17','next_id'=>(int)$id));exit;
        //     }
        // }
        $device_code = I('serial_no');//设备编码
        $line = I('channel');//端口
        $caller = I('callid_dialed');//电话号码
        $is_file = I('recorded');//费否上传文件
        $event_type = I('direction');
        $duration = I('duration_secs');
        $date = I('start_time_secs');
        $answered = I('answered');//来电是 这个参数为1 表示来电 否则表示未接
        
        if($duration && $duration > 5400){ //超过一个半小时的 不要上传
            echo json_encode(array('status'=>0,'next_id'=>$id+1));exit;
        }

        if(!$device_code){ //没有上传录音编码或者端口
            echo json_encode(array('status'=>-1,'next_id'=>$id));exit;
        }
        //查找设备是否注册
        $device = M("devices")->where("code = {$device_code}")->find();
        if($device){
            if($line > $device['line']){ //上传的端口比设备设置的端口还大 设备部合法
                echo json_encode(array('status'=>'-1','next_id'=>$id));exit;
            }
        }else{ //添加未注册设备
            if($device_id = M("devices")->add(array('code'=>$device_code))){
                //添加设备信息表
                M("device_stat")->add(array("device_id"=>$device_id));
                $device = array();
                $device['id'] = $device_id;
                $device['code'] = $device_code;
                $device['name'] = '';
            }else{ //添加失败
                echo json_encode(array('status'=>'-1','next_id'=>$id));exit;
            }           
        }
        //判断当前记录时来电 去电还是 未接
        if($event_type == "in"){
            if(1== $answered){ //表示来电
                $event_type = "in";
            }else{ //表示来电未接
                $event_type = "unkown";//表示未接
                $duration = 0;
                
            }
        }
        //查找端口或者注册端口
        if($line){
            $line_id = M("device_line")->where(array('device_id'=>$device['id'],'code'=>$line))->getField("id");
            if(!$line_id){ //尚未注册过
                $data = array();
                $data['device_id'] = $device['id'];
                $data['code'] = $line;
                $data['last_time'] = time();
                $line_id = M("device_line")->add($data);
                unset($data);
            }
            //端口数据更新
            $model = M("device_line");
            $model->id = $line_id;
            switch($event_type){
                case 'in':
                $model->comeing = array('exp','comeing + 1');//来电
                break;
                case 'out':
                    $model->outgoing = array('exp','outgoing + 1');//去电
                break;
                case 'unkown':
                    $model->missed = array('exp','missed + 1');//未接
                break;
            }
            $model->case = array('exp','`case` + 1');//视频
            $model->last_time = time();
            
            if ( $caller ) {
                $caller = str_replace(['A','B'],['*','#'],$caller);
                 $model->tel = $caller;
            } else {
                $model->tel = '';
            }
            $model->save();
        }

        //设备数据统计处理
        $device_stat = M("device_stat")->where("device_id = {$device['id']}")->getField("id");
        if(!$device_stat){
            $device_stat = M("device_stat")->add(array('device_id'=>$device['id']));
        }
        $model = M("device_stat");
        $model->id = $device_stat;
        $model->device_id = $device['id'];
        switch($event_type){
            case 'in':
                $model->comeing = array('exp','comeing + 1');//来电
            break;
            case 'out':
                $model->outgoing = array('exp','outgoing + 1');//去电
            break;
            case 'unkown':
                $model->missed = array('exp','missed + 1');//未接
            break;
        }
        $model->case = array('exp','`case` + 1');//事件+1
        $model->last_time = time();//最后在线
        $model->device_time = date('Y-m-d H:i:s',time());//最后在线
        // $date && $model->device_time = $date;
        $CPU && $model->CPU = $CPU;
        $TotalStore && $model->TotalStore = $TotalStore;
        $TotalFreeStore && $model->TotalFreeStore = $TotalFreeStore;
        $TotalMem && $model->TotalMem = $TotalMem;
        $TotalFreeMem && $model->TotalFreeMem = $TotalFreeMem;
        $IP && $model->IP = $IP;
        $model->save();

        //如果当前对方电话在通讯录中 要更新通讯录
        $contact_id = 0;
        $caller && $contact_id = M("contacts")->where("tel1 = '{$caller}' ")->getField("id");
        if($contact_id){
            $model = M("contact_stat");
            $model->id = $contact_id;
            switch($event_type){
                case 'in':
                    $model->comeing = array('exp','comeing + 1');//来电
                break;
                case 'out':
                    $model->outgoing = array('exp','`outgoing`+ 1');//去电
                break;
                case 'unkown':
                    $model->missed = array('exp','missed + 1');//未接
                break;
            }
            $model->last_time = time();
            $model->save();
        }

        //记录数据表更新
        $call_types = array('in','out','unkown');
        if(in_array($event_type, $call_types)){
            if(!$date){ //记录上传必须上传时间
                echo json_encode(array('status'=>-1,'next_id'=>$id));exit;
            }
            if(!$line || $line < 0){
               echo json_encode(array('status'=>-1,'next_id'=>$id));exit;
            }
            //检测是否已经上传过
            $add_time = $date;
            switch($event_type){
                case 'in':
                    $call_type = 10;
                break;
                case 'out':
                   $call_type = 9;
                break;
                case 'unkown':
                    $call_type = 11;
                break;
            }
            if(M("device_call")->where(array('device_id'=>$device['id'],'line_id'=>$line,'type'=>$call_type,'add_time'=>$add_time))->find()){
                echo json_encode(array('status'=>'-1','next_id'=>$id));exit;
            }
            //如果有文件上传 需要先进行文件处理
            if($is_file){
                $FilePath = self::D_files($device_code);
            }
            //添加数据
            $data = array();
            //修改文件后缀 如果是wav的改为MP3
            $data['device_id'] = $device['id'];
            $data['line_id'] = $line;
            $data['line'] = $line_id;
            switch($event_type){
                case 'in':
                    $data['type'] = 10;
                break;
                case 'out':
                   $data['type'] = 9;
                break;
                case 'unkown':
                    $data['type'] = 11;
                break;
            }
            
            $duration && $data['recording_time'] = $duration;
            $data['add_time'] = $add_time;
            $duration && $data['call_time'] = $duration;
            $FilePath && $data['files'] = $FilePath;
            $caller && $data['tel'] = $caller;
            $data['call_date'] = date('Y-m-d H:i:s',$date);
            $call_id = M("device_call")->add($data);
            $this->_updateCallData($data['device_id'],$data['type'],$data['call_time'],$data['recording_time'],$data['add_time']);
            unset($data);
        }

        //端口状态处理
        if($status){
            $lines = $status['channels'];
            if($lines){
                foreach($lines as $k=>$v){
                    $LINE = M("device_line")->where("code = {$v['ch']} and device_id = {$device['id']}")->find();
                    switch($v['state']){
                        case 0:
                            $case_type = 13;
                        break;
                        case 1:
                            $case_type = 8;
                        break;
                        case 2:
                            $case_type = 6;
                        break;
                        case 3:
                            $case_type = 5;
                        break;
                        case 4:
                            $case_type = 7;
                        break;
                        case 5:
                            $case_type = 13;
                        break;
                    }
                    if($LINE){
                        M("device_line")->where("id = {$LINE['id']}")->save(array('case_type'=>$case_type,'last_time'=>time()));
                    }else{
                        M("device_line")->add(array('device_id'=>$device['id'],'code'=>$v['ch'],'case_type'=>$case_type,'last_time'=>time()));
                    }
                }
            }
        }
        // 不是公共需求 2019-4-16 同步设备每天产生的录音文件数量和录音文件大小，按照天来统计。
        $this->_deviceDateTotal($device_code, I('request.TotalFileNum'),I('request.TotalFileSize'),date("Y-m-d",$date),$device_code);
        // //事件记录处理
        // if(in_array($event_type, $call_types)){
        //     $data = array();
        //     $data['content'] = @http_build_query($_REQUEST);
        //     $data['device_id'] = $device['id'];
        //     $data['line_id'] = $line;
        //     $data['type'] = $event_type;
        //     $data['add_time'] = time();
        //     M("device_case")->add($data);
        //     unset($data);
        // }

        //掉电警告 发送邮件
        // if(13 == $event_type){
        //     self::checkLines($device,$line);
        // }
        $next_id = $id + 1;
        echo json_encode(array('status'=>0,'next_id'=>$next_id));exit;
    }

    /**
     * 事件上传接口
     * @param event_type 事件类型 3=提机拨号,4=拨号内容,5=来电振铃,6=开始录音,7=来电接听,8=去电挂机/接听完毕,10表示来电  9表示去电,11=未接来电,14=来电号码  50=现场视频
     * @param device_id 设备编号 code
     * @param line 端口号
     * @param date 时间
     * @param voltage
     * @param ringcnt
     * @param caller 电话号
     * @param duration
     * @param token 验证秘钥
     * @param callnotes event_type=9 10 11 时作为备注
     */
    public function Event(){
        $token = I('token');   // md5(event_type+device_id+line+HVbXAIUT1G2GRmzW)
        $event_type = I('request.event_type');
        $device_code = I('request.device_id');
        $line = I('request.line') ? I('request.line') : '0' ;
        $voltage = I('request.voltage');
        $RingCnt = I('request.RingCnt');
        $date = I('request.date');
        $TotalStore = I('request.TotalStore');
        $TotalFreeStore = I('request.TotalFreeStore');
        $TotalMem = I('request.TotalMem');
        $TotalFreeMem = I('request.TotalFreeMem');
        $CPU = I('request.CPU');
        $Version = I('request.Version');
        $dtmf = I('request.dtmf'); //拨号内容,开始录音,去电挂机
        $caller = I('request.caller'); //来电号码,来电接听,接听完毕,未接来电
        $FilePath = I('request.FilePath');
        $duration = I('request.duration'); //录音时长
        $StartRec = I('request.StartRec');
        $PortName = I('request.PortName');
        $extension = I('request.extension');
        $throw_host = I('request.throw_host');
        $TimeLong = I('request.TimeLong'); //通话时间
        $IP = I('request.IP');
        $callnotes = I('callnotes');
        $caller = str_replace(['A','B'],['*','#'],$caller);
        //参数检测
        if (empty($device_code)  || empty($event_type)) { //没传递设备码
            $this->ajaxReturn('0001','HTML');
        }
        $md5_token = md5($event_type.$device_code.$line.$this->_token_key);
        if ( $token != $md5_token && $this->_token_open == true ) { //token验证错误 目前不验证
            $this->ajaxReturn('9999','HTML');
        }
        
        //首先判断 设备是否已经注册 如果没有注册 存储到未注册设备中
        $device = M("devices")->where("code = {$device_code}")->find();
        if($device){
            if($line > $device['line']){ //上传的端口比设备设置的端口还大 设备部合法
                $this->ajaxReturn('0003','HTML');
            }
        }else{ //添加未注册设备
            if($device_id = M("devices")->add(array('code'=>$device_code))){
                //添加设备信息表
                M("device_stat")->add(array("device_id"=>$device_id));
                $device = array();
                $device['id'] = $device_id;
                $device['code'] = $device_code;
                $device['name'] = '';
            }else{ //添加失败
                $this->ajaxReturn('0002','HTML');
            }           
        }

        //处理端口 如果是没有注册过的端口 进行注册
        if($line){
            $line_id = M("device_line")->where(array('device_id'=>$device['id'],'code'=>$line))->getField("id");
            if(!$line_id){ //尚未注册过
                $data = array();
                $data['device_id'] = $device['id'];
                $data['code'] = $line;
                $PortName && $data['PortName'] = $PortName;
                $data['last_time'] = time();
                $line_id = M("device_line")->add($data);
                unset($data);
            }
            //端口数据更新
            $line_types = array(3,4,5,6,7,8,13,40,41,42);
            $model = M("device_line");
            $model->id = $line_id;
            if (in_array($event_type, $line_types)) {
                $model->case_type  = $event_type;
            }
            
            switch($event_type){
                case 10:
                $model->comeing = array('exp','comeing + 1');//来电
                break;
                case 9:
                    $model->outgoing = array('exp','outgoing + 1');//去电
                break;
                case 11:
                    $model->missed = array('exp','missed + 1');//未接
                break;
                case 50:
                    $model->videod = array('exp','videod + 1');//视频
                break;
            }
            $model->case = array('exp','`case` + 1');//视频
            $model->last_time = time();
            if ( $voltage ) {
                $model->voltage  = $voltage;
            } else {
                 $model->voltage = '';
            }
            if ( $caller ) {
                 $model->tel = $caller;
            } else {
                $model->tel = '';
            }
            $PortName && $model->PortName = $PortName;
            $model->save();
        }

        //对设备统计进行数据处理
        //进行添加
        $device_stat = M("device_stat")->where("device_id = {$device['id']}")->getField("id");
        if(!$device_stat){
            $device_stat = M("device_stat")->add(array('device_id'=>$device['id']));
        }
        $model = M("device_stat");
        $model->id = $device_stat;
        $model->device_id = $device['id'];
        switch($event_type){
            case 10:
                $model->comeing = array('exp','comeing + 1');//来电
            break;
            case 9:
                $model->outgoing = array('exp','outgoing + 1');//去电
            break;
            case 11:
                $model->missed = array('exp','missed + 1');//未接
            break;
            case 50:
                $model->videod = array('exp','videod + 1');//视频
            break;
        }
        $model->case = array('exp','`case` + 1');//事件+1
        $model->last_time = time();//最后在线
        $Version && $model->Version = $Version;
        $date && $model->device_time = $date;
        $CPU && $model->CPU = $CPU;
        $TotalStore && $model->TotalStore = $TotalStore;
        $TotalFreeStore && $model->TotalFreeStore = $TotalFreeStore;
        $TotalMem && $model->TotalMem = $TotalMem;
        $TotalFreeMem && $model->TotalFreeMem = $TotalFreeMem;
        $IP && $model->IP = $IP;
        $model->save();

        //如果当前对方电话在通讯录中 要更新通讯录
        $contact_id = 0;
        $caller && $contact_id = M("contacts")->where("tel1 = '{$caller}' ")->getField("id");
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
                case 50:
                    $model->videod = array('exp','videod + 1');
                break;
            }
            $model->last_time = time();
            $model->save();
        }
//        来电且刚有号码时的记录
        if($event_type==14){
            if (M('',null)->query("SHOW TABLES LIKE 'device_call_alert'")){
                M('device_call_alert')->add(['device_id'=>$device['id'],'tel'=>(string)$caller,'line_id'=>$line,'create_time'=>time(),'call_date'=>$date,'device_code'=>$device_code]);
            }
        }
        //记录数据表更新
        $call_types = array(9,10,11,28,29,50);
        if(in_array($event_type, $call_types)){
            if(!$date){ //记录上传必须上传时间
                $this->ajaxReturn('0005','HTML');
            }
            if(!$line || $line < 0){
                $this->ajaxReturn('0004','HTML');//端口错误
            }
            //检测是否已经上传过
            $add_time = strtotime($date);
             if(M("device_call")->where(array('device_id'=>$device['id'],'line_id'=>$line,'type'=>$event_type,'add_time'=>$add_time))->find()){
                 $this->ajaxReturn('0006','HTML');
             }
            //添加数据
            $data = array();
            //修改文件后缀 如果是wav的改为MP3
            if($FilePath){
                $file = explode('.',$FilePath);
                if('wav' == $file[1]){
                    $FilePath = $file[0].'.mp3';
                }
            }
            $data['device_id'] = $device['id'];
            $data['line_id'] = $line;
            $data['line'] = $line_id;
            $data['type'] = $event_type;
            $duration && $data['recording_time'] = $duration;
            $data['add_time'] = $add_time;
            $TimeLong && $data['call_time'] = $TimeLong;
            $FilePath && $data['files'] = $FilePath;
            $caller && $data['tel'] = $caller;
            $data['call_date'] = $date;
            $callnotes && $data['callnotes'] = $callnotes;
            M("device_call")->add($data);
            //2019-4-24 更新 device_call_data表当天的数据。
            $this->_updateCallData($data['device_id'],$data['type'],$data['call_time'],$data['recording_time'],$data['add_time']);
            unset($data);
        }
        // 不是公共需求 2019-4-16 同步设备每天产生的录音文件数量和录音文件大小，按照天来统计。
        $this->_deviceDateTotal($device_code, I('request.TotalFileNum'),I('request.TotalFileSize'),$date,$device_code);
        //事件记录处理
        // if(in_array($event_type, $call_types)){
        //     $data = array();
        //     $data['content'] = @http_build_query($_REQUEST);
        //     $data['device_id'] = $device['id'];
        //     $data['line_id'] = $line;
        //     $data['type'] = $event_type;
        //     $data['add_time'] = time();
        //     M("device_case")->add($data);
        //     unset($data);
        // }

        //掉电警告 发送邮件
        if(13 == $event_type){
            self::checkLines($device,$line);
        }
        $this->ajaxReturn('0000','HTML');
    }
//    public function test3(){
//        $arr = [10,9,11,14,50];
//        $this->_updateCallData(1,$arr[random_int(0,4)],99,120,time());
//    }
    /**
     * 更新当天device_call_data表
     * @param $deviceId
     * @param $type  10表示来电  9表示去电,11=未接来电,14=来电号码  50=现场视频 28=留言，语音
     * @param $callTime
     * @param $recordingTime
     * @param $addTime
     * @return bool
     */
    private function _updateCallData($deviceId,$type,$callTime,$recordingTime,$addTime){
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
        $record = M('device_call_data')->field('id',true)->where($con)->find();
        if($record){
            foreach ($data as $k=>$v){
                $record[$k] = $record[$k]+$v;
            }
            M('device_call_data')->where($con)->save($record);
        }else{
            M('device_call_data')->add(array_merge($data,$con));
        }
        //更新记录总览
        $totalCon = ['type'=>1,'device_id'=>0];
        $totalRecord =  M('device_call_data')->field('id',true)->where($totalCon)->limit(0,1)->find();
        if($totalRecord){
            foreach ($data as $k=>$v){
                $totalRecord[$k] = $totalRecord[$k]+$v;
            }
            M('device_call_data')->where($totalCon)->save($totalRecord);
        }else{
            M('device_call_data')->add(array_merge($data,$totalCon));
        }
        
        return true;
    }
//    public function test1(){
//        $device['id']=9;
//        $deviceCode = M('device')->where(['id'=>$device['id']])->find();
//        $deviceCode = $deviceCode['device_code'];
//        $date=I('request.date');
//        $this->_deviceDateTotal($device['id'], I('request.TotalFileNum'),I('request.TotalFileSize'),$date,$deviceCode);
//    }
    /**
     * 其他项目需求，不是公共需求
     * 同步设备每天产生的录音文件数量和录音文件大小，按照天来统计。
     * @param $deviceId
     * @param $totalFileNum
     * @param $totalFileSize
     * @param $date
     * @param $deviceCode
     * @return bool|mixed
     */
    private function _deviceDateTotal($deviceId,$totalFileNum,$totalFileSize,$date,$deviceCode){
        if(!$deviceId||!$totalFileNum||!$totalFileSize){
            return false;
        }
        if (!M('',null)->query("SHOW TABLES LIKE 'device_date_total'")){
            return false;
        }
        $data = ['device_id'=>(string)$deviceId,
                 'deviceCode'=>(string)$deviceCode,
                 'file_num'=>(int)$totalFileNum,
                 'file_size'=>(int)$totalFileSize,
                 'create_date'=>$date ? $date : date('Y-m-d')
            ];
        $data['create_date'] = date('Y-m-d',strtotime($data['create_date']));
        $condition= ['device_id'=>$data['device_id'],
                     'create_date'=>$data['create_date']
        ];
        if(M('device_date_total')->field('id')->where($condition)->find()){
            M('device_date_total')->where($condition)->save(['file_num'=>$data['file_num'],'file_size'=>$data['file_size']]);
        }else{
            M('device_date_total')->add($data);
        }
        return true;
    }
  
    /**
     * 文件上传
     */
    public function Files(){
        $token =I('token');
        $dst = 'files';
        if (!is_array($_FILES[$dst]) || !is_uploaded_file($_FILES[$dst]['tmp_name'])) {
            $this->ajaxReturn('0001','HTML');
        }
        $filename = $_FILES[$dst]['name'];
        $md5_token = $filename.$this->_token_key;
        if ( $token != $md5_token && $this->_token_open == true ) {
            $this->ajaxReturn('0009','HTML');
        }
        $file_dir = D("Common/DeviceCall")->getFileDir($filename); //   年/月/日/时/端口号/文件
        $full_dirs = $file_dir;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs,0777,true);
        }
        $upload_file = $full_dirs.$filename;
        $move_upload = @move_uploaded_file($_FILES['files']['tmp_name'], $upload_file);
        if ( empty($move_upload) ) {
            $this->ajaxReturn('0003','HTML');
        }else{
            //转换格式
            $file = explode('.',$filename);
            if('wav' == $file[1]){
                $file = $file[0].'.mp3';
                $upload_file1 = $full_dirs.$file;
                if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                    $str = "D:\sox\sox-14-4-2\sox {$upload_file} {$upload_file1}";
                }else{
                    $str = "sox {$upload_file} {$upload_file1}";
                }
                exec($str);
                if (file_exists($upload_file1)){
                    unlink($upload_file);
                }
            }
            $this->ajaxReturn('0000','HTML');
        }
    }

   
    
    /**
     * D系列文件上传
     * @param code 设备code
     */
    public function D_files($code){
        $dst = 'file';
        if (!is_array($_FILES[$dst]) || !is_uploaded_file($_FILES[$dst]['tmp_name'])) {
            return false;
        }
        $filename = $_FILES[$dst]['name'];
        $filenames = explode('.',$filename);
        //重写文件名称
        $filename = $code.'-'.date("YmdHis").'-'.$filenames[0].'.'.$filenames[1];
        $file_dir = D("Common/DeviceCall")->getFileDir($filename); //   年/月/日/时/端口号/文件
        $full_dirs = $file_dir;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs,0777,true);
        }
        $upload_file = $full_dirs.$filename;
        $move_upload = @move_uploaded_file($_FILES['file']['tmp_name'], $upload_file);
        if ( empty($move_upload) ) {
            return false;
        }else{
            //转换格式
            $file = explode('.',$filename);
            if('wav' == $file[1]){
                $file = $file[0].'.mp3';
                $upload_file1 = $full_dirs.$file;
                if (strtoupper(substr(PHP_OS,0,3))==='WIN'){
                    $str = "D:\sox\sox-14-4-2\sox {$upload_file} {$upload_file1}";
                }else{
                    $str = "sox {$upload_file} {$upload_file1}";
                }
                exec($str);
                if(file_exists($upload_file1)){
                    unlink($upload_file);
                }
            }

            return $file;
        }
    }

    /**
     * 端口异常报警
     * @param $device_id 设备数组信息
     * @param $line 端口编码
     */
    public function checkLines($device,$line){
        if(!$line || !$device){
            return false;
        }
        //获取本端口有权限的所有账户和对应的邮箱
        $emails = M("AccountPurview")->alias('a')
                ->join("left join accounts b on a.account_id = b.id")
                ->where("a.device_id = {$device['id']}")
                ->field("b.email")
                ->select();
        $Pemail = new \Common\Utils\MailUtil();
        if($emails){ //封装包体 邮件接收人
            $msg .= '<h3>'.$device['name'].'</h3>';
            $msg .= '<h4>设备ID： '.$device['code'].'</h4>';
            if ( $line ) {
                $msg .= '<h4>设备端口： '.sprintf("%04d", $line).'</h4>';
            }
            $msg .= '<h4>掉电时间： '.date("Y-m-d H:i:s",time()).'</h4>';
            $title = '设备['.$device['code'].'] 端口['.sprintf("%04d", $line).'] 电话线异常';
            
            //判断是否发送邮件 发送就要添加数据表
            $stmp = M('device_line_stmp')->where(array('device_id'=>$device['id'],'line'=>$line))->order('time desc')->limit(1)->getField("time");
            if($stmp){
                //判断时间是否超过24小时
                $time = time() - $stmp;
                if($time >= 24 * 60 * 60){ //超过要发送并记录
                    $is = 1;
                }else{
                    $is = 0;
                }
            }else{
                $is = 1;
            }
            if($is){
                $Pemail->setHeader($title);
                $Pemail->setBody($msg);
                $Pemail->setClients($emails);
                $status = $Pemail->send();
            }
            //如果发送成功 要记录数据
            if($status){
                $data = array();
                $data['device_id'] = $device['id'];
                $data['line'] = $line;
                $data['time'] = time();
                M("device_line_stmp")->add($data);
            }
        }
    }

    /**
     * 查询接口
     * @param startTime 开始时间
     * @param endTime 结束时间
     * @param phone 要查询的号码字符串 ,分割
     */
    public function getListByTimePhone(){
        // var_dump($_SERVER);exit;
        if(IS_POST){
            $data = htmlspecialchars_decode(I('key'));
            if(!$data){
                $this->ajaxReturn(array('status'=>300,'code'=>'缺少请求参数key'),'JSON');
            }
            $data = json_decode($data,1);
            if(!$data['startTime'] || empty($data['startTime'])){
                $this->ajaxReturn(array('status'=>400,'code'=>'缺少请求参数startTime'),'JSON');
            }
            if(!$data['endTime'] || empty($data['endTime'])){
                 $this->ajaxReturn(array('status'=>500,'code'=>'缺少请求参数endTime'),'JSON');
            }
            if(!$data['phone'] || empty($data['phone'])){
                $this->ajaxReturn(array('status'=>600,'code'=>'缺少请求参数phone'),'JSON');
            }
            $where = "";
            //三个参数进行处理
            $startTime = strtotime($data['startTime']);
            $endTime = strtotime($data['endTime']);
            $phones = str_replace(',', '|', $data['phone']);
            if($startTime && $endTime){
                $where = "add_time between {$startTime} and {$endTime} ";
            }else{
                $startTime && $where = "add_time >= {$startTime}";
                $endTime && $where = "add_time <= {$endTime}";
            }
            $phones && $where .=  "and tel regexp {$phones}";
            $lists = M("device_call")->where($where)->field("add_time,call_time,tel,files,type,id")->select();
            //处理成需要的结果
            if($lists){
                foreach($lists as $k=>$v){
                    if(!$v['files']){
                        $lists[$k]['files'] = 0;
                    }else{
                        $files = D("Common/DeviceCall")->getFileDir($v['files']).$v['files'];
                        if(!is_file($files)){
                            $lists[$k]['files'] = 0;
                        }else{
                            $lists[$k]['files'] = $_SERVER['HTTP_HOST'].U('Api/Api/DownloadLog',array('id'=>$v['id']));
                        }
                    }
                    unset($lists[$k]['id']);
                }
                $this->ajaxReturn(array('status'=>200,'code'=>'获取数据成功','data'=>$lists),'JSON');
            }else{
                $this->ajaxReturn(array('status'=>200,'code'=>'获取数据成功','data'=>'没有匹配数据'),'JSON');
            }
            
        }else{
            $this->ajaxReturn(array('status'=>100,'code'=>'请求方式错误'),'JSON');
        }
    }

    /**
     * 录音文件下载
     * @param id 录音id
     */
    public function DownloadLog(){
        $id = I('id','','int');
        if ($id) {
            $data = M("device_call")->where("id = {$id}")->find();
            if(!$data){
                exit("记录不存在");
            }
            if ($data['throw_host']) {
                $re_url = $data['throw_host'].'/Api/DownloadLog/?id='.$id;
                hearder("location:{$re_url}");
            }
            $_device = M("devices")->where("id = {$data['device_id']}")->find();

            $msg = '  [设备名称: '.$_device['name'].'. 设备端口:'.$data['line_id'].'. 记录时间:'.$data['call_date'].'. 记录文件: '.$data['files'].']';
            D("Common/DeviceCall")->FileDownload($data['files']);
        }else{
            exit("记录不存在");
        }
    }
    //最新event类型 14 时需要弹层 0 无弹层 1 有弹层
    public function telLayer(){
//        $_GET['test'] = 1;
        $time          = $_GET['test'] ? time() - 3600*24*30 : time() - 4;
        $con           = ['create_time' => ['egt', $time]];
        $data             = (array)M('device_call_alert')->where($con)->order('create_time asc')->limit(200)->select();
        $res = ['data'=>[],'status'=>0];
        foreach ($data as $r) {
            $cDate         = explode(" ", $r['call_date']);
            $res['data'][] = ['id'=>$r['id'],'msg'=>"来电号码：{$r['tel']},来电时间：{$cDate[1]},来电设备：{$r['device_code']},端口号：{$r['line_id']}"];
        }
        $res['status'] = $data ? 1 : 0;
        echo json_encode($res);
    }




    /**
     * 事件上传接口  -拾音器
     * @param event_type 事件类型 3=提机拨号,4=拨号内容,5=来电振铃,6=开始录音,7=来电接听,8=去电挂机/接听完毕,10表示来电  9表示去电,11=未接来电,14=来电号码  50=现场视频
     * @param device_id 设备编号 code
     * @param line 端口号
     * @param date 时间
     * @param voltage
     * @param ringcnt
     * @param caller 电话号
     * @param duration
     * @param token 验证秘钥
     * @param callnotes event_type=9 10 11 时作为备注
     */
    public function S_Event(){
        $token = I('token');   // md5(event_type+device_id+line+HVbXAIUT1G2GRmzW)
        $event_type = I('request.event_type');
        $device_code = I('request.device_id');
        $line = I('request.line') ? I('request.line') : '0' ;
        $voltage = I('request.voltage');
        $RingCnt = I('request.RingCnt');
        $date = I('request.date');
        $TotalStore = I('request.TotalStore');
        $TotalFreeStore = I('request.TotalFreeStore');
        $TotalMem = I('request.TotalMem');
        $TotalFreeMem = I('request.TotalFreeMem');
        $CPU = I('request.CPU');
        $Version = I('request.Version');
        $dtmf = I('request.dtmf'); //拨号内容,开始录音,去电挂机
        $caller = I('request.caller'); //来电号码,来电接听,接听完毕,未接来电
        $FilePath = I('request.FilePath');
        $duration = I('request.duration'); //录音时长
        $StartRec = I('request.StartRec');
        $PortName = I('request.PortName');
        $extension = I('request.extension');
        $throw_host = I('request.throw_host');
        $TimeLong = I('request.TimeLong'); //通话时间
        $IP = I('request.IP');
        $callnotes = I('callnotes');
        $caller = str_replace(['A','B'],['*','#'],$caller);




        if (empty($device_code)  || empty($event_type) ) { //没传递设备码

            $this->ajaxReturn('0001','HTML');
        }
        $md5_token = md5($event_type.$device_code.$line.$this->_token_key);
        if ( $token != $md5_token && $this->_token_open == true ) { //token验证错误 目前不验证
            $this->ajaxReturn('9999','HTML');
        }

        //首先判断 设备是否已经注册 如果没有注册 存储到未注册设备中
        $device = M("devices")->where("code = {$device_code}")->find();
        if($device){
            if($line > $device['line']){ //上传的端口比设备设置的端口还大 设备部合法
                $this->ajaxReturn('0003','HTML');
            }
        }else{ //添加未注册设备
            if($device_id = M("devices")->add(array('code'=>$device_code))){
                //添加设备信息表
                M("device_stat")->add(array("device_id"=>$device_id));
                $device = array();
                $device['id'] = $device_id;
                $device['code'] = $device_code;
                $device['name'] = '';
            }else{ //添加失败
                $this->ajaxReturn('0002','HTML');
            }
        }

        //处理端口 如果是没有注册过的端口 进行注册
        if($line){
            $line_id = M("device_line")->where(array('device_id'=>$device['id'],'code'=>$line))->getField("id");
            if(!$line_id){ //尚未注册过
                $data = array();
                $data['device_id'] = $device['id'];
                $data['code'] = $line;
                $PortName && $data['PortName'] = $PortName;
                $data['last_time'] = time();
                $line_id = M("device_line")->add($data);
                unset($data);
            }
            //端口数据更新
            $line_types = array(3,4,5,6,7,8,13,40,41,42);
            $model = M("device_line");
            $model->id = $line_id;
            if (in_array($event_type, $line_types)) {
                $model->case_type  = $event_type;
            }

            switch($event_type){
                case 10:
                    $model->comeing = array('exp','comeing + 1');//来电
                    break;
                case 9:
                    $model->outgoing = array('exp','outgoing + 1');//去电
                    break;
                case 11:
                    $model->missed = array('exp','missed + 1');//未接
                    break;
                case 50:
                    $model->videod = array('exp','videod + 1');//视频
                    break;
            }
            $model->case = array('exp','`case` + 1');//视频
            $model->last_time = time();
            if ( $voltage ) {
                $model->voltage  = $voltage;
            } else {
                $model->voltage = '';
            }
            if ( $caller ) {
                $model->tel = $caller;
            } else {
                $model->tel = '';
            }
            $PortName && $model->PortName = $PortName;
            $model->save();
        }

        //对设备统计进行数据处理
        //进行添加
        $device_stat = M("device_stat")->where("device_id = {$device['id']}")->getField("id");
        if(!$device_stat){
            $device_stat = M("device_stat")->add(array('device_id'=>$device['id']));
        }
        $model = M("device_stat");
        $model->id = $device_stat;
        $model->device_id = $device['id'];
        switch($event_type){
            case 10:
                $model->comeing = array('exp','comeing + 1');//来电
                break;
            case 9:
                $model->outgoing = array('exp','outgoing + 1');//去电
                break;
            case 11:
                $model->missed = array('exp','missed + 1');//未接
                break;
            case 50:
                $model->videod = array('exp','videod + 1');//视频
                break;
        }
        $model->case = array('exp','`case` + 1');//事件+1
        $model->last_time = time();//最后在线
        $Version && $model->Version = $Version;
        $date && $model->device_time = $date;
        $CPU && $model->CPU = $CPU;
        $TotalStore && $model->TotalStore = $TotalStore;
        $TotalFreeStore && $model->TotalFreeStore = $TotalFreeStore;
        $TotalMem && $model->TotalMem = $TotalMem;
        $TotalFreeMem && $model->TotalFreeMem = $TotalFreeMem;
        $IP && $model->IP = $IP;
        $model->save();

        //如果当前对方电话在通讯录中 要更新通讯录
        $contact_id = 0;
        $caller && $contact_id = M("contacts")->where("tel1 = '{$caller}' ")->getField("id");
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
                case 50:
                    $model->videod = array('exp','videod + 1');
                    break;
            }
            $model->last_time = time();
            $model->save();
        }
//        来电且刚有号码时的记录
        if($event_type==14){
            if (M('',null)->query("SHOW TABLES LIKE 'device_call_alert'")){
                M('device_call_alert')->add(['device_id'=>$device['id'],'tel'=>(string)$caller,'line_id'=>$line,'create_time'=>time(),'call_date'=>$date,'device_code'=>$device_code]);
            }
        }
        //记录数据表更新
        $call_types = array(9,10,11,28,29,50);
        if(in_array($event_type, $call_types)){
            if(!$date){ //记录上传必须上传时间
                $this->ajaxReturn('0005','HTML');
            }
            if(!$line || $line < 0){
                $this->ajaxReturn('0004','HTML');//端口错误
            }
            //检测是否已经上传过
            $add_time = strtotime($date);
            if(M("device_call")->where(array('device_id'=>$device['id'],'line_id'=>$line,'type'=>$event_type,'add_time'=>$add_time))->find()){
                $this->ajaxReturn('0006','HTML');
            }
            //添加数据
            $data = array();
            //修改文件后缀 如果是wav的改为MP3
            if($FilePath){
                $file = explode('.',$FilePath);
                if('wav' == $file[1]){
                    $FilePath = $file[0].'.mp3';
                }
            }
            $data['device_id'] = $device['id'];
            $data['line_id'] = $line;
            $data['line'] = $line_id;
            $data['type'] = $event_type;
            $duration && $data['recording_time'] = $duration;
            $data['add_time'] = $add_time;
            $TimeLong && $data['call_time'] = $TimeLong;
            $FilePath && $data['files'] = $FilePath;
            $caller && $data['tel'] = $caller;
            $data['call_date'] = $date;
            $callnotes && $data['callnotes'] = $callnotes;
            M("device_call")->add($data);
            //2019-4-24 更新 device_call_data表当天的数据。
            $this->_updateCallData($data['device_id'],$data['type'],$data['call_time'],$data['recording_time'],$data['add_time']);
            unset($data);
        }
        // 不是公共需求 2019-4-16 同步设备每天产生的录音文件数量和录音文件大小，按照天来统计。
        $this->_deviceDateTotal($device_code, I('request.TotalFileNum'),I('request.TotalFileSize'),$date,$device_code);
        //事件记录处理
        // if(in_array($event_type, $call_types)){
        //     $data = array();
        //     $data['content'] = @http_build_query($_REQUEST);
        //     $data['device_id'] = $device['id'];
        //     $data['line_id'] = $line;
        //     $data['type'] = $event_type;
        //     $data['add_time'] = time();
        //     M("device_case")->add($data);
        //     unset($data);
        // }

        //掉电警告 发送邮件
        if(13 == $event_type){
            self::checkLines($device,$line);
        }
        $this->ajaxReturn('0000','HTML');
    }

    /**
     * 文件上传
     */
    public function S_Files(){
        $token =I('token');
        $dst = 'files';
        if (!is_array($_FILES[$dst]) || !is_uploaded_file($_FILES[$dst]['tmp_name'])) {
            $this->ajaxReturn('0001','HTML');
        }
        $filename = $_FILES[$dst]['name'];
        $md5_token = $filename.$this->_token_key;
        if ( $token != $md5_token && $this->_token_open == true ) {
            $this->ajaxReturn('0009','HTML');
        }
        $file_dir = D("Common/DeviceCall")->getFileDir($filename); //   年/月/日/时/端口号/文件
        $full_dirs = $file_dir;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs,0777,true);
        }
        $upload_file = $full_dirs.$filename;
        $move_upload = @move_uploaded_file($_FILES['files']['tmp_name'], $upload_file);
        if ( empty($move_upload) ) {
            $this->ajaxReturn('0003','HTML');
        }else{
            //转换格式
            $file = explode('.',$filename);
            if('wav' == $file[1]){
                $file = $file[0].'.mp3';
                $upload_file1 = $full_dirs.$file;
                if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                    $str = "D:\sox\sox-14-4-2\sox {$upload_file} {$upload_file1}";
                }else{
                    $str = "sox {$upload_file} {$upload_file1}";
                }
                exec($str);
                if (file_exists($upload_file1)){
                    unlink($upload_file);
                }
            }
            $this->ajaxReturn('0000','HTML');
        }
    }

    
}