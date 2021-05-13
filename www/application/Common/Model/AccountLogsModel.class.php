<?php
namespace Common\Model;
use Common\Model\CommonModel;
use Common\Utils\GetIpUtil;
class AccountLogsModel extends CommonModel {
    protected $table = '';
    public $timestamps = false;
     //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('account_id', 'require', '管理员id不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('content', 'require', '日志内容不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('ip', 'require', 'ip不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('utime', 'require', '时间不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );
  
    /**
     * 添加一条操作日志
     * @param log_content 操作内容
     */
    function addLog($log_content){
        $id = $_SESSION['ACCOUNTS']['id'];
        $ip = GetIpUtil::get_client_ip();
        if (!$id || empty($log_content)  ) {
            return false;
        }
        $data = array(
            'account_id' =>$id,
            'content'=>$log_content,
            'ip'=>$ip,
            'utime'=>time(),
        );
        $this -> add($data);
    }
}