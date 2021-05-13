<?php
namespace Common\Model;
use Common\Model\CommonModel;

class AccountPurviewModel extends CommonModel {
    protected $table = 'account_purview';
    public $timestamps = false;
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('account_id', 'require', '管理员id不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('group_id', 'require', '组id不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('device_id', 'require', '设备id不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );

    /**
     * 获取我的关注统计
     * @param account_id 管理员id
     */
    public function getAttentionsCount($account_id){
        if(!$account_id || !is_int((int)$account_id)){
            return false;
        }
        $where = array();
        $where['account_id'] = (int)$account_id;
        $where['attention'] = 1;
        $data = array();
        $data['device'] = $this->where($where)->count();//我关注的设备数量
        $data['log'] = M('device_call_flag')->where("account_id = {$account_id}")->count();//我关注的记录数量
        $data['concat'] = M('contacts')->count();//通讯录数量
        return $data;
    }

     /**
     * 获取我的手机设备关注统计
     * @param account_id 管理员id
     */
    public function getAppAttentionsCount($account_id){
        if(!$account_id || !is_int((int)$account_id)){
            return false;
        }
        $where = array();
        $where['account_id'] = (int)$account_id;
        $where['attention'] = 1;
        $data = array();
        $data['device'] = M("account_app_purview")->where($where)->count();//我关注的设备数量
        $data['log'] = M('device_app_call_flag')->where("account_id = {$account_id}")->count();//我关注的记录数量
        $data['concat'] = M('contacts')->count();//通讯录数量
        return $data;
    }
    function getData0($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $_data = self::where(["account_id"=>$account_id])->select();
        if ($_data) {
            foreach($_data as $data) {
                $result[$data['group_id']][] = $data['device_id'];
            }
        }
        return $result;
    }
    
    /**
     * 获取权限设备组和设备
     * @param account_id 管理员id
     */
    function getData($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $where = array();
        $where['a.account_id'] = $account_id;
        $data = $this->alias('a')->join('left join devices b on a.device_id = b.id')->join("left join device_group d on a.group_id = d.id")->join('left join device_stat c on a.device_id = c.device_id')->field('a.group_id,a.device_id,b.name,c.last_time,d.name as gname')->where($where)->select();
        //对结果进行分组
        foreach($data as $k=>$v){
            $_data[$v['group_id']][$k] = $v;   
            $_data[$v['group_id']]['gname'] = $v['gname'];
            //判断是否在线
            $_data[$v['group_id']][$k]['status'] = $v['last_time'] >= (time() - $this->_last_time) ? 1 : 0;
        }
        unset($data);
        //计算每个分组总数和在线
        foreach($_data as $k=>$v){
            $_data[$k]['total'] = count($_data[$k]) - 1;
            $_data[$k]['inline'] = 0;
            $_data[$k]['out'] = 0;
            foreach($v as $key =>$val){
                if(is_int($key)){
                    if(1 == $val['status']){
                        $_data[$k]['inline'] += 1;
                    }else{
                        $_data[$k]['out'] += 1;
                    }
                }
            }
            
        }
        return $_data;
    }
    
    /**
     * 获取权限设备组和设备
     * @param account_id 管理员id
     */
    function getAppData($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $where = array();
        $where['a.account_id'] = $account_id;
        $data = M("account_app_purview")->alias('a')->join('left join devices_app b on a.device_id = b.id')->join("left join device_group d on a.group_id = d.id")->join('left join device_app_stat c on a.device_id = c.device_id')->field('a.group_id,a.device_id,b.name,c.last_time,d.name as gname')->where($where)->select();
        //对结果进行分组
        foreach($data as $k=>$v){
            $_data[$v['group_id']][$k] = $v;   
            $_data[$v['group_id']]['gname'] = $v['gname'];
            //判断是否在线
            $_data[$v['group_id']][$k]['status'] = $v['last_time'] >= (time() - $this->_last_time) ? 1 : 0;
        }
        unset($data);
        //计算每个分组总数和在线
        foreach($_data as $k=>$v){
            $_data[$k]['total'] = count($_data[$k]) - 1;
            $_data[$k]['inline'] = 0;
            $_data[$k]['out'] = 0;
            foreach($v as $key =>$val){
                if(is_int($key)){
                    if(1 == $val['status']){
                        $_data[$k]['inline'] += 1;
                    }else{
                        $_data[$k]['out'] += 1;
                    }
                }
            }
            
        }
        return $_data;
    }

    function isAttention($account_id,$device_id){
        if ( empty($account_id) || empty($device_id) ) {
            return false;
        }
        $_data = self::where("account_id",$account_id)->where("device_id",$device_id)->first();
        if ($_data->attention) {
            return true;
        }
        return false;
    }
}