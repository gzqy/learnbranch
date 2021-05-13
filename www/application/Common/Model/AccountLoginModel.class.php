<?php
namespace Common\Model;
use Common\Model\CommonModel;
class AccountLoginModel extends CommonModel {
    protected $table = 'account_login';
    public $timestamps = false;
    public  $_last_time = 200;
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('account_id', 'require', '管理员id不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );
    
    /**
    * 统计管理员数量 
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
     * 账户登录处理
     * @param accoutn_id  管理员id
     */
    function login($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $session_id = session_id();
        $time = time();
        $_data = $this->where("account_id",$account_id)->find();
        $data = array();
        if ($_data) {
            $data['login_time'] = $time;
            $data['last_time'] = $time;
            $data['login_session'] = $session_id;
            $this->where("account_id = {$account_id}")->setField($data);
        } else {
            $data['account_id'] = $account_id;
            $data['login_time'] = $time;
            $data['last_time'] = $time;
            $data['login_session'] = $session_id;
            $this->add($data);
        }
        return true;
    }
    
    function getLastTime(){
        return $this->_last_time;
    }
    
    
    function last($account_id){
        if ( empty($account_id) ) {
            return false;
        }
        $time = time();
        $session_id = session_id();
        $_data = $this->where(array("account_id"=>$account_id))->find();
        if ($_data && $_data['login_session'] == $session_id  ) {
            $_data['last_time'] = $time;
            $this->save($_data);
            return true;
        }
        unset($_SESSION['ACCOUNTS']);
        return false;
        
    }

    public function updateSession(){
        $account = I("session.ACCOUNTS");
        if($account){
            //更新最后在线时间
            $this->where("account_id = {$account['id']}")->save(array('last_time'=>time()));
            M("accounts")->where("id = {$account['id']}")->save(array('last_time'=>time()));
            $_SESSION['ACCOUNTS'] = $account;
        }
        return true;
    }
}