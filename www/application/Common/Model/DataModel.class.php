<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DataModel extends CommonModel {
    protected $table = '';
    public $timestamps = false;

    /**
     * 获取所有设备数量 注册数量 未注册数量 删除数量
     */
    public function getDeviceCount(){
    	$data = array();
    	$data['total'] = M("device")->count();
    	$data['registered'] = M("device")->where("registered = 1 and closed = 0")->count();
    	$data['unregistered'] = M("device")->where("registered = 0 and closed = 0")->count();
    	$data['closed'] = M("device")->where("closed = 1")->count();
    	return $data;
    }
}