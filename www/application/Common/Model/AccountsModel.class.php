<?php
namespace Common\Model;
use Common\Model\CommonModel;
class AccountsModel extends CommonModel {
    protected $table = 'accounts';
    public $timestamps = false;
    public  $_last_time = 200;
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '名称不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('account', 'require', '账号不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('password', 'require', '密码不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('add_time', 'require', '创建时间不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );
    
   /**
     * 获取某个账号的权限设备统计
     * @param account_id 账号id
     */
    public function getAccountDevicesCount($account_id){
        if(!$account_id || !is_int((int)$account_id)){
            return false;
        }
        $where = array();
        $where['a.account_id'] = (int)$account_id;
        $where['c.registered'] = 1;
        $where['c.closed'] = 0;
        $data = array();
        $data['total'] = M('account_purview')->alias('a')->join("left join devices c on a.device_id = c.id")->join("left join device_stat b on a.device_id = b.device_id")->where($where)->count();//总权限设备
        $where['b.last_time'] = array('egt',time() - $this->_last_time);
        $data['inline'] = M('account_purview')->alias('a')->join("left join devices c on a.device_id = c.id")->join("left join device_stat b on a.device_id = b.device_id")->where($where)->count();//总在线权限设备
        $data['out'] = $data['total'] - $data['inline'];
        return $data;
    }

    /**
     * 获取某个账号的权限app设备统计
     */
    public function getAccountAppDevicesCount($account_id){
        if(!$account_id || !is_int((int)$account_id)){
            return false;
        }
        $where = array();
        $where['a.account_id'] = (int)$account_id;
        $where['c.registered'] = '1';
        $where['c.closed'] = '0';
        $data = array();
        $data['total'] = M('account_app_purview')->alias('a')->join("left join devices_app c on a.device_id = c.id")->join("left join device_app_stat b on a.device_id = b.device_id")->where($where)->count();//总权限设备
        $where['b.last_time'] = array('egt',time() - $this->_last_time);
        $data['inline'] = M('account_app_purview')->alias('a')->join("left join devices_app c on a.device_id = c.id")->join("left join device_app_stat b on a.device_id = b.device_id")->where($where)->count();//总在线权限设备
        $data['out'] = $data['total'] - $data['inline'];
        return $data;
    }

    /**
    * 查询账号总数、在线账号、离线账号
    */
    public function getAccountsCount(){
        $data = array();
        $data['total'] = $this->count();
        $time = time() - $this->_last_time;
        $data['inline'] = $this->where("last_time > {$time}")->count();
        $data['out'] = $data['total'] - $data['inline'];
        return $data;
    }

    /**
    * 用户登录成功 更新相关信息
    */
    public function dologin($user){
        //更新最后在线和登录时间
        $data = array();
        $data['last_time'] = time();
        $data['login_time'] = time();
        $this->where("id = {$user['id']}")->save($data);
        $data['login_session'] = session_id();
        if(!M("account_login")->where("account_id = {$user['id']}")->save($data)){
            $data['account_id'] = $user['id'];
            M("account_login")->add($data);
        }
        return true;
    }    
}