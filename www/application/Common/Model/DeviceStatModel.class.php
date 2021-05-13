<?php
namespace Common\Model;
use Common\Model\CommonModel;

class DeviceStatModel extends CommonModel {
    protected $table = 'device_stat';
    public $timestamps = false;
    private static $_cache_data = array();
    protected static  $_last_time1 = 600;    

    function getData($device_id){
        if ( empty($device_id) ) {
            return false;
        }
        if ( self::$_cache_data['getData'][$device_id] ) {
            return self::$_cache_data['getData'][$device_id];
        }
        $file=SITE_PATH . '/data/runtime/device_stat_refresh'.$device_id .'.log';
        $lastTime=file_get_contents($file);
        if(!$lastTime||time()-$lastTime>300){
            $counts=M('device_call')->field('count(1) as count,type')->where(['device_id'=>$device_id])->group('type')->select();
            $counts=array_column($counts,'count','type');
            $deviceData['outgoing']=(int)$counts[9];
            $deviceData['comeing']=(int)$counts[10];
            $deviceData['missed']=(int)$counts[11];
            M('device_stat',null)->where(['device_id'=>$device_id])->save($deviceData);
            file_put_contents($file,time());
        }
        $data = $this->where("device_id=".$device_id)->find();
        if ($data) {
            if ( self::isOnline($data['last_time'])  ) {
                $data['status'] = true;
            } else {
                $data['status'] = false;
            }
            if ( $data['totalfreestore'] && $data['totalstore'] ) {
                $data['Store'] = round(($data['totalstore'] - $data['totalfreestore']) / $data['totalstore'],2)*100;
            }
            if ( $data['totalfreemem'] && $data['totalmem'] ) {
                $data['Mem'] = round(($data['totalmem'] - $data['totalfreemem']) / $data['totalmem'],2)*100;
            }
            unset($data['id']);
        }
        self::$_cache_data['getData'][$device_id] = $data;
        return $data;
    }
    
    function getAn($device_id){
        if ( empty($device_id) ) {
            return false;
        }
        $_data = self::where("device_id",$device_id)->first();
        if ($_data) {
            $data = $_data->toArray();
            return $data;
        }
        return false;
    }
    
    
    function getLastTime(){
        return self::$_last_time;
    }
    
    function getCSS($val){

        if ($val > 0 && $val < 70 ) {
            $result = 'success';
        }
        if ($val >= 70 && $val < 90 ) {
            $result = 'warning';
        }
        if ($val >= 90 ) {
            $result = 'error';
        }
        return $result;
    }
    
    /**
     * 检测设备状态
     */
    public function DeviceStatus($device_id){
        if ( empty($device_id) ) {
            return false;
        }
        $last_time = $this->where("device_id = {$device_id}")->getField("last_time");
        if ($last_time) {
            if ( self::isOnline($last_time)  ) {
                return true;
            }
        }
        return false;
    }
    
    /**
      * 判断设备在线
      * @param last_time 最后在线时间
      */ 
    static private function isOnline($last_time){
        $time = time();
        if (( $time - self::$_last_time1 ) < $last_time && $last_time > 0 ) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}