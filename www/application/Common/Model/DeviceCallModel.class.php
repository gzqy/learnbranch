<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DeviceCallModel extends CommonModel {
    protected $table = 'device_call';
    public $timestamps = false;
    
    /**
     * 通话记录类型
     */
    public function callTypes(){
        $arr = array(
            array('id'=>9,'name'=>'去电记录'),
            array('id'=>10,'name'=>'来电记录'),
            array('id'=>11,'name'=>'未接来电'),
            array('id'=>28,'name'=>'音频记录'),
            array('id'=>29,'name'=>'来电留言'),
            array('id'=>50,'name'=>'现场视频'),
        );
        return $arr;
    }

    /**
     * 记录类型验证
     */
    public function type($key=0){
        $array = array(
            1=>'拨号中',
            2=>'通话中',
            3=>'挂机',
            4=>'',
            9=>'去电记录',
            10=>'来电记录',
            11=>'未接来电',
            12=>'去电未接',
            13=>'未接主动挂断',
            28=>'音频记录',
            29=>'来电留言',
            50=>'现场视频',
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }
    
    /**
     * 端口状态图片
     */
    public function icon($key=0){
        $array = array(
            9=>array(   //去电
                'icon'=>'jt_03.png',
                'color'=>'info message',
            ),
            10=>array(  //来电
                'icon'=>'jt_04.png',
                'color'=>'success message',
            ),
            11=>array(  //未接
                'icon'=>'b_call.png',
                'color'=>'error message',
            ),
            12=>array(  //未接
                'icon'=>'b_call.png',
                'color'=>'error message',
            ),
            13=>array(  //未接
                'icon'=>'b_call.png',
                'color'=>'error message',
            ),
            28=>array(  //音频记录
                'icon'=>'RecFileFlag.png',
                'color'=>'success message',
            ),
            29=>array(  //来电留言
                'icon'=>'voice.png',
                'color'=>'info  message',
            ),
            50=>array(  //现场视频
                'icon'=>'video2.jpg',
                'color'=>'success  message',
            ),
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }

    /**
     * 删除记录
     */
    function deleteLog($log){
        if(!$log){
            return false;
        }
        //获取对应文件路径
        if($log['files']){
            $file = self::getFileDir($log['files']).$log['files'];
        }
        //删除录音
        $is = M("device_call")->where("id = {$log['id']}")->delete();

        if($is){
            if($file){
                //删除对应文件
                unlink($file);
            }
            //更新数据
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?s=Api/Cron/changeByDay';
             $curl = curl_init();
             //设置抓取的url
            curl_setopt($curl, CURLOPT_URL, $url);
            //设置头文件的信息作为数据流输出
            curl_setopt($curl, CURLOPT_HEADER, 1);
            //设置获取的信息以文件流的形式返回，而不是直接输出。
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
           //执行命令
            $data = curl_exec($curl);
            //关闭URL请求
            curl_close($curl);
        }
        return $is;
    }
        
    /**
     * 获取设备播放列表
     * @param $id 设备id
     * @param $lineIds 设备端口id
     * return array
     */
    public function getCallList($id,$lineIds=[]){
    
        $limit              = I('limit') > 0 ? I('limit') : 20;
        $limit              = min($limit, 5000);
        $p = I('p') ? I('p') : 1;
        $start_date = I('start_date');
        $end_date = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time = I('end_call_time');
        $search_line = I('search_line');
        $search_type = I('search_type');
         $max_recording = I('max_recording','','int');
        $min_recording = I('min_recording','','int');
        $tel = I('tel');
        $keywords = I('keywords');
        $sort = I('sort');
        $sort = empty($sort) ? 'time' : $sort;
        $callnotes = I('callnotes');
    
        $account = I("session.ACCOUNTS");
        $account_id = $account['id'];
        //where条件处理
        $where = array();
        //根据设备搜索和根据端口搜索
        if(!$lineIds){
            $where['a.device_id'] = $id;
            $where['b.device_id'] = $id;
        }else{
            $where['b.id'] = ['in',$lineIds];
        }
        $where['c.account_id'] = $account_id;
        if($start_date && $end_date){
            $where['a.add_time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['a.add_time'] = array('gt',strtotime($start_date));
            $end_date && $where['a.add_time'] = array('lt',strtotime($end_date));
        }
        if($start_call_time && $end_call_time){
            $where['a.call_time'] = array('between',array($start_call_time,$end_call_time));
        }else{
             $start_call_time && $where['a.call_time'] = array('gt',$start_call_time);
            $end_call_time && $where['a.call_time'] = array('lt',$end_call_time);
        }
       
        $search_line && $where['a.line_id'] = ['in',$search_line];
        $search_type && $where['a.type'] = $search_type;
        if($max_recording && $min_recording){
            $where['a.recording_time'] = array('between',array($min_recording,$max_recording));
        }else{
            $min_recording && $where['a.recording_time'] = array('gt',$min_recording);
            $max_recording && $where['a.recording_time'] = array('lt',$max_recording);
        }
    
//        $keywords&& $where['a.keywords'] = ['like',"%{$keywords}%"];
        $tel && $where['a.tel'] = array('like',"%{$tel}%");
        $callnotes && $where['a.callnotes'] = array('like',"%{$callnotes}%");
        //排序处理
        switch ($sort) {
            case 'type':
                $order = 'a.type asc';
                break;
            case 'recording':
                $order = 'a.recording DESC';
                break;
            case 'call':
                $order = 'a.call DESC';
                break;
            case 'tel':
                $order = 'a.tel DESC';
                break;
            default:
                $order = 'a.id DESC';
                break;
        }
        $count = $this->alias("a")
            ->join("inner join device_line b on a.line_id = b.code and a.device_id=b.device_id")
            ->join("inner join account_purview_line c on c.line_id=b.id")
            ->where($where)
            ->field("a.id,a.line_id,a.type,a.add_time,a.call_time,a.tel,b.PortName")
            ->order($order)
            ->count();
        $page = $this->page($count,$limit,array('limit'=>$limit,'recent_date'=>I('recent_date'),'lineIds'=>$lineIds,'p'=>$p,"sort"=>$sort,"search_type"=>$search_type,"search_line"=>$search_line,"end_call_time"=>$end_call_time,"start_date"=>$start_date,"end_date"=>$end_date,'start_call_time'=>$start_call_time,'id'=>$id,'max_recording'=>$max_recording,'min_recording'=>$min_recording,'tel'=>$tel,'keywords'=>$keywords,'callnotes'=>$callnotes));
        $data = $this->alias("a")
            ->join("inner join device_line b on a.line_id = b.code and a.device_id=b.device_id")
            ->join("inner join account_purview_line c on c.line_id=b.id")
            ->where($where)
            ->field("a.id,a.line_id,a.type,a.add_time,a.call_time,a.tel,b.PortName,a.call_date,a.recording_time,a.files,a.score")
            ->order($order)
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        if($data){
            foreach($data as $k=>$v){
                //时间 s 转化为时间格式
                //录音类型判断
                $img = self::icon($v['type']);
                $type = self::type($v['type']);
                $data[$k]['type1'] = $v['type'];
                $data[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $data[$k]['call_time'] = self::getTime($v['call_time']);
                $data[$k]['recording_time'] = self::getTime($v['recording_time']);
            }
        }
        $data1 = array();
        $data1['data'] = $data;
        $data1['page'] = $page->show();
        return $data1;
    }

    function getTime($time=0){
        if(!is_numeric($time)){
            return $time;
        }
        if ( $time < 60) {
            return '00:00:'.sprintf("%02d", $time);
        }
        if ( $time >= 60 && $time < 60*60 ) {
            $i = $time / 60;
            $s = $time % 60;
            return '00:'.sprintf("%02d", $i).':'.sprintf("%02d", $s);
        }
        if ( $time >= 60*60 ) {
            $H = $time / 3600;
            $i = ($time % 3600) / 60;
            $s = ($time % 3600) % 60;
            return sprintf("%02d", $H).':'.sprintf("%02d", $i).':'.sprintf("%02d", $s);
        }
    }

    /**
     * 录音文件中如果包含# * 特殊字符 进行替换
     */
    public function replaceForFilename($filename=null){
        if(!$filename){
            return false;
        }
        $filename = str_replace('#','B',$filename);
        $filename = str_replace('*','A',$filename);
        return $filename;
    }
    
    /**
     * 设备录音文件存放地址
     * @param filename 文件名称
     */
     function getFileDir($filename=null){
        if ( empty($filename) ) {
            return false;
        }
        // $file_dir = UPLOAD_PATH.'/'.substr($filename,0,8).'/'.substr($filename,9,4).'/'.substr($filename,13,2).'/'.substr($filename,15,2).'/'.substr($filename,18,2).'/';
        $file_dir = UPLOAD_PATH.'/'.substr($filename,0,8).'/'.substr($filename,9,4).'/'.substr($filename,13,2).'/'.substr($filename,15,2).'/'.substr($filename,18,2).'/';
        return $file_dir;
    }

    /**
     * 播放录音文件地址
     */
    public function getFilePlayDir($filename){
        $file_dir = UPLOAD_PATH1.'/'.substr($filename,0,8).'/'.substr($filename,9,4).'/'.substr($filename,13,2).'/'.substr($filename,15,2).'/'.substr($filename,18,2).'/';
        return $file_dir;
    }

    /**
     * 手机设备录音文件名称处理为路径
     * @param filename 文件名称
     */
    function getAppFileDir($filename){
        if ( empty($filename) ) {
            return false;
        }
        $file_dir = UPLOAD_PATH.'/'.substr($filename,0,11).'/'.substr($filename,12,4).'/'.substr($filename,16,2).'/'.substr($filename,18,2).'/'.substr($filename,21,2).'/';
        return $file_dir;
    }

    /**
     * 手机设备录音文件名称处理为路径
     * @param filename 文件名称
     */
    function getAppFilePlayDir($filename){
        if ( empty($filename) ) {
            return false;
        }
        $file_dir = UPLOAD_PATH1.'/'.substr($filename,0,11).'/'.substr($filename,12,4).'/'.substr($filename,16,2).'/'.substr($filename,18,2).'/'.substr($filename,21,2).'/';
        return $file_dir;
    }

    /**
     * 获取录音文件类型
     */
    function getFileType($files){
        $filetype = trim(substr(strrchr($files, '.'), 1, 10)); 
        $filetype = strtolower($filetype);
        return $filetype;
    }
    
    function getUtime($device_id,$line_id,$type,$utime){
        if ( empty($device_id) || empty($line_id) || empty($type) || empty($utime) ) {
            return false;
        }
        $_data = self::where("device_id",$device_id)->where("line_id",$line_id)->where("type",$type)->where("add_time",$utime)->first();
        if ( $_data->id ) {
            return $_data->toArray();
        }
        return false;
    }
    
    function getTypeCount($device_id,$line_id=0,$type=0,$start_time=0,$end_time=0,$filter=array()){
        if ( empty($device_id) || empty($type) ) {
            return false;
        }

        //$_models = self::selectRaw('SUM(1) as  type_total')->where("device_id",$device_id);
        $_models = self::where("device_id",$device_id);
        if ( $line_id ) {
            $_models = $_models->where("line_id",$line_id);
        }
        if ( $filter['search_type'] ) {
            $_models = $_models->where("type",$filter['search_type']);
        } else if($type) {
            $_models = $_models->where("type",$type);
        }
        if ( $filter['keywords'] ) {
            $_models = $_models->where("keywords",'like', '%'.$filter['keywords'].'%');
        }
        if ( $start_time ) {
            $_models = $_models->where("add_time",'>=',$start_time);
        }
        if ( $end_time ) {
            $_models = $_models->where("add_time",'<',$end_time);
        }
        if ( $filter['start_call_time'] ) {
            $_models = $_models->where("call_time",'>=',$filter['start_call_time']);
        }
        if ( $filter['end_call_time'] ) {
            $_models = $_models->where("call_time",'<',$filter['end_call_time']);
        }
        /**
        $_data = $_models->first();
        if ( $_data->type_total ) {
            return $_data->type_total;
        }
         * 
         */
        $result = $_models->count();
        return $result;
    }
    

    function getRecordingCount($device_id,$line_id,$type,$start_time=0,$end_time=0,$filter=array()){
        if ( empty($device_id) || empty($type) ) {
            return false;
        }

        $_models = self::selectRaw('SUM('.$type.') as  type_total')->where("device_id",$device_id);
        if ( $line_id ) {
            $_models = $_models->where("line_id",$line_id);
        }
        if ( $filter['search_type'] ) {
            $_models = $_models->where("type",$filter['search_type']);
        }
        if ( $filter['keywords'] ) {
            $_models = $_models->where("keywords",'like', '%'.$filter['keywords'].'%');
        }
        if ( $start_time ) {
            $_models = $_models->where("add_time",'>=',$start_time);
        }
        if ( $end_time ) {
            $_models = $_models->where("add_time",'<',$end_time);
        }
        if ( $filter['start_call_time'] ) {
            $_models = $_models->where("call_time",'>=',$filter['start_call_time']);
        }
        if ( $filter['end_call_time'] ) {
            $_models = $_models->where("call_time",'<',$filter['end_call_time']);
        }
        $_data = $_models->first();
        if ( $_data->type_total ) {
            return $_data->type_total;
        }
        return 0;
    }
    
    function getFile($files){
        if ( empty($files) ) {
            return false;
        }
        $file = '/'.\Models\DeviceCall::getFileDir($files).substr($files,2);
        $file_res = UPLOAD_PATH.''.$file;
        if ( $file_res ) {
            return $file_res;
        }
        return false;
    }
    
    /**
     * 文件下载
     * @param $file 文件路径
     */
    function FileDownload($file){
        $files = D("Common/DeviceCall")->getFileDir($file).self::replaceForFilename($file);
        if (!is_file($files)) {
            exit('文件不存在['.$file.']');
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public"); 
        header("Content-Description: File Transfer");

        //Use the switch-generated Content-Type
        header("Content-Type: audio/x-wav");
        $len = filesize($files);
        //Force the download
        $header="Content-Disposition: attachment; filename=".$file.";";
        header( $header );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$len);
        @readfile($files);
    }
    
    /**
     * 手机上传文件下载
     * @param $file 文件路径
     */
    function AppFileDownload($file){
        $files = D("Common/DeviceCall")->getAppFileDir($file).$file;
        if (!is_file($files)) {
            exit('文件不存在['.$file.']');
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public"); 
        header("Content-Description: File Transfer");

        //Use the switch-generated Content-Type
        header("Content-Type: audio/x-wav");
        $len = filesize($files);
        //Force the download
        $header="Content-Disposition: attachment; filename=".$file.";";
        header( $header );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$len);
        @readfile($files);
    }
}