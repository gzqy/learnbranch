<?php
namespace Common\Model;
use Common\Model\CommonModel;
class AccountPurviewLineModel extends CommonModel {
    protected $table = 'account_purview_line';
    public $timestamps = false;

    /**
     * 获取管理员某个设备的权限端口列表
     * @param $id 设备id
     * @param $account_id 管理员id
     */
    public function getDeviceAuthLines($id,$account_id){
        if(!$id || !$account_id){
            return false;
        }
        // $lines = $this->alias('a')->join("left join device_line b on a.line_id = b.code")->where(array('a.account_id'=>$account_id,'a.device_id'=>$id,'b.device_id'=>$id))->field("b.*")->select();
        $lines = M("device_line")->where("device_id = {$id}")->order("code asc")->select();
        return $lines;
    }



    function getData($account_id,$device_id=0,$line_id=0){
        if ( empty($account_id) ) {
            return false;
        }
        $where=[
            'account_id'=>$account_id
        ];
        if($device_id || $line_id){
            $where[]=$device_id ? ['device_id'=>$device_id] : ['line_id'=>$line_id];
        }
        $_data = M('account_purview_line',null)->where($where)->select();
        if ($_data) {
            foreach($_data as $data) {
                if ($device_id ) {
                    $result[$data['line_id']] = 1;
                } else {
                    $result[$data['device_id']][$data['line_id']] = 1;
                }
            }
        }
        return $result;
    }
    
}