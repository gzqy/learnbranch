<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DeviceCaseModel extends CommonModel {
    
    protected $table = 'device_case';
    
    public $timestamps = false;
    
    public function type($key=0){

        $array = array(
            1=>'设备开机',
            2=>'设备故障',
            3=>'提机拨号',
            4=>'拨号内容',
            5=>'来电振铃',
            6=>'开始录音',
            7=>'来电接听',
            8=>'挂机',
            9=>'去电',
            10=>'来电',
            11=>'未接来电',
            12=>'长时间提机',
            13=>'电话掉电',
            14=>'来电显示',
            15=>'FTP开始',
            16=>'FTP完成',
            17=>'FTP错误',
            18=>'设备开机',
            19=>'检查更新',
            20=>'开始更新',
            21=>'更新完毕',
            22=>'更新错误',
            23=>'SD卡容量',
            24=>'网络参数',
            25=>'电压过高',
            26=>'通话时间大于小时警告',
            27=>'存储空间不够',
            28=>'音频记录',
            29=>'来电留言',
            40=>'声控录音有声音',
            41=>'声控录音无声',
            42=>'来电留言',
			50=>'现场视频',
        );
        if ($array[$key]) {
            return $array[$key];
        }
        return  false;
    }
    
    
    
    
    
    
    function getLastType($device_id,$type){
        if ( empty($device_id) || empty($type) ) {
            return false;
        }
        $_data = self::where("device_id",$device_id)->where("type",$type)->orderBy("add_time",'DESC')->first();
        if ($_data) {
            return $_data->toArray();
        }
        return false;
    }
    
    
    function getCount($device_id,$line=0){
        if ( empty($device_id) ) {
            return false;
        }
        $_model = self::where("device_id",$device_id);
        if ( $line ) {
            $_model = $_model->where('line_id',$line);
        }
        $res = $_model->count();
        if ( $res ) {
            return $res;
        }
        return 0;
    }
    
}