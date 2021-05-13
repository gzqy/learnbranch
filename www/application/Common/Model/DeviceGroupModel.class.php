<?php
namespace Common\Model;
use Think\Model;
/**
 * 设备组 模型
 */
class DeviceGroupModel extends Model {
    protected $table = 'device_group'; //模型对应数据表
    public $timestamps = false;//不需要eloquent自动维护create_at update_at字段（可能不存在）
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '设备组名称不能为空', 1, 'regex', Model:: MODEL_BOTH ),
        array('add_time', 'require', '设备组创建时间不能为空', 1, 'regex', Model:: MODEL_BOTH ),
    );
    //自动完成
    protected $_auto = array(
            //array(填充字段,填充内容,填充条件,附加规则)
    );

    /**
     * 获取设备组
     */
    function getOption(){
       $data = $this -> order('add_time asc')->select();
        if ( $data ) {
            $groups = array();
            foreach($data as $k=>$v){
                $groups[$v['id']] = $v['name'];
            }
        }
        return $groups;
    }
}