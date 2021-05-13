<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DeviceLineModel extends CommonModel {
    protected $table = 'device_line';
    public $timestamps = false;
    private  $_device_status = array();
    private  $_cache_data = array();

    /**
     * 设备端口字段信息处理
     * @param data 端口信息
     */
    public  function forData($data){
        if(!$data){
            return false;
        }
        //先检测设备是否在线 如果设备不在线端口肯定不在线
        if ( self::getDeviceStatus($data['device_id'])  ) {
            $data['status'] = true;
        } else {
            self::setType($data['id'],13);
            $data['status'] = false;
            $data['case_type'] = 13;
        }

        if ( $data['last_time'] ) {
            $data['last_date'] = date("Y-m-d H:i",$data['last_time']);
        }
        if ( $data['case_type'] ) {
            $data['case_text'] = self::LineType($data['case_type']);
        } else {
            $data['case_text'] = '掉电';
        }

        $data['case_icon'] = self::icon($data['case_type']);
        if ( empty($data['case_icon']) ) {
            $data['case_icon'] = 'poweroff_b.png';
        }
        if ( $data['status'] == false ) {
            $data['case_text'] = '掉电';
            $data['case_icon'] = 'poweroff_b.png';
        }

        return $data;
    }
    
    /**
     * 检测设备是否在线
     */
    static private function getDeviceStatus($device_id){
        $device_status = D("Common/DeviceStat")->DeviceStatus($device_id);
        return $device_status;
    }

    public function last_status($key=0){
        $array = array(
            1=>'拨号中',
            2=>'通话中',
            3=>'挂机',
            4=>'',
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }
    
    /**
     * 设置端口状态
     * @param id 端口id
     * @param case_type 事件类型
     */
     private function setType($id,$case_type) {
        if ( empty($id) ) {
            return false;
        }
        $type = $this->where("id = {$id}")->getField("case_type");
        if($type && $type != $case_type){
            $this->where("id = {$id}")->save(array('case_type'=>$case_type));
        }
        return true;
    }

    /**
     * 获取端口状态对应文字
     * @param key case_type类型
     */
    public function LineType($key=0){
        $array = array(
            3=>'拨号',
            4=>'拨号',
            5=>'振铃',
            6=>'去电录音',
            7=>'来电录音',
            8=>'挂机',
            13=>'掉电',
            40=>'音频录音',
            41=>'静音',
            42=>'来电留言',
        );
        if ($array[$key]) {
            return $array[$key];
        }
    }

    /**
     * 端口状态对应图片
     */
    public function icon($key=0){
        $array = array(
            3=>'hookoff_b.png',
            4=>'hookoff_b.png',
            5=>'ring_b.png',
            6=>'outbound_b.png',
            7=>'inbound_b.png',
            8=>'hookon_b.png',
            13=>'poweroff_b.png',
            40=>'voice_b.png',
            41=>'silence_b.png',
            42=>'autoanswer.png',
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }

    /**
     * 获取设备端口列表
     * @param device_id 设备id
     */
    function getLines($device_id){
        if ( empty($device_id) ) {
            return false;
        }
        $data = $this->where("device_id = {$device_id}")->order("code asc")->select();
        if($data){
            return $data;
        }
        return false;
    }
    
    function getData($id){
        if ( empty($id) ) {
            return false;
        }
        $file=SITE_PATH . '/data/runtime/device_line_refresh'.$id .'.log';
        $lastTime=file_get_contents($file);
        if(!$lastTime||time()-$lastTime>1800){
            $counts=M('device_call')->field('count(1) as count,type')->where(['line_id'=>$id])->group('type')->select();
            $counts=array_column($counts,'count','type');
            $deviceData['outgoing']=(int)$counts[9];
            $deviceData['comeing']=(int)$counts[10];
            $deviceData['missed']=(int)$counts[11];
            M('device_line',null)->where(['id'=>$id])->save($deviceData);
            file_put_contents($file,time());
        }
        $data = $this->where("id = {$id}") ->find();
        if ($data) {
            $data = self::forData($data);
        }
        return $data;
    }
    
    
    
    
    
    
   
    
    function getPortName($device_id,$line_id){
        if ( empty($device_id) || empty($line_id) ) {
            return false;
        }
        if ( self::$_cache_data[$device_id][$line_id] ) {
            return self::$_cache_data[$device_id][$line_id];
        }
        $_data = self::where("device_id",$device_id)->where("code",$line_id)->first();
        if ($_data) {
            if ( $_data->PortName ) {
                $result = $_data->PortName ;
            }
        }
        self::$_cache_data[$device_id][$line_id] = $result;
        return $result;
    }
    
    
}