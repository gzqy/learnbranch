<?php 
namespace Common\Utils;
/**
 * 设备组方法类
 */
class DeviceGroupUtil{
	/**
	 * 获取所有设备组
	 */
	public static function getGroups(){
		// $data = M('device_group')->order('add_time asc')->select();
  //   	if ( $data ) {
  //           $groups = array();
  //           foreach($data as $k=>$v){
  //               $groups[$v['id']] = $v['name'];
  //           }
  //       }
  //       return $groups;
		$a = new \Common\Model\DeviceGroupModel();
		$model = D('Common/DeviceGroup');exit;
		$model -> getOption();
	}
}