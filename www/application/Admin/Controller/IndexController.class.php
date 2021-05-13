<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
class IndexController extends ShopbaseController {
	  protected $nav_id = 1;
    /**
     * 前置操作
     */
    public function _initialize() {
    	parent::_initialize ();
        //获取当前账号有权限的设备并按组分好
        $purviews = $this->accountPurview->getData($this->account_id);
        $this->assign('all_purviews',$purviews); //将数据输送模板
       	$this->assign('nav_id',$this -> nav_id);
    }


    /**
     * 设备首页
     * 客户端设备版本首页
     */
    public function index() {
        //板块1 数据
        //账号登录数据
        $account_login = $this->accounts->where("id = {$this->account_id}")->field("last_time,login_time")->find();
        $this->assign('account_login',$account_login);
        //日志总数
        $log_count = $this->accountLogs ->where("account_id = $this->account_id")->count();
        $this->assign('log_count',$log_count);
        //权限设备统计
        $account_devices = $this->accounts->getAccountDevicesCount($this->account_id);
        $this->assign('account_devices',$account_devices);
        //关注统计
        $att = $this->accountPurview->getAttentionsCount($this->account_id);
        $this->assign('att',$att);
       
        //账号统计
        $accounts = $this->accounts->getAccountsCount();
        $accounts['round'] = 100 * round(($accounts['inline'] / $accounts['total']),2)."%";

        $this->assign('accounts',$accounts);
        //总设备统计
        $devices = $this->device->getDevicesCount();
        $devices['round'] = 100 * round(($devices['inline'] / $devices['total']),2)."%";
        $this->assign('devices',$devices);
        
        //通话记录统计
        $data = M("device_call_data")->where("type = 1")->find();
        $data['round'] = 100 * round(( ($data['all'] - $data['missed']) / $data['all']),2)."%";
        $this->assign('data',$data);

        //本账户 最近的10记录
        $account_logs = M("account_logs")->where("account_id = {$this->account_id}")->order("id desc")->limit(10)->select();
        $this->assign('account_logs',$account_logs);

        //获取系统信息
        $a = array(
             'os' => $_SERVER["SERVER_SOFTWARE"], //获取服务器标识的字串
            'version' => PHP_VERSION, //获取PHP服务器版本
             'osname' => php_uname(), //获取系统类型及版本号
             'max_upload' => ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled", //最大上传
             'max_ex_time' => ini_get("max_execution_time")."秒", //脚本最大执行时间
        );
        $b = M()->query("select version() as verion");
        $a['mysql_version'] = $b[0]['verion'];//mysql版本
        //磁盘空间
        $a['free'] = round((@disk_free_space('/')/(1024*1024*1024)),2);
        $a['total'] = round((@disk_total_space('/')/(1024*1024*1024)),2);
        $a['use'] = $a['total'] - $a['free'];
        $a['round'] = 100 * round($a['use'] / $a['total'],2);
        $this->accountLogs->addLog('首页查看');
        $this->assign('a',$a);
        $this->display();
    }
    
    /**
     * 设备首页
     * 手机app版本    
     */
    public function index_App() {
        //板块1 数据
        //账号登录数据
        $account_login = $this->accounts->where("id = {$this->account_id}")->field("last_time,login_time")->find();
        $this->assign('account_login',$account_login);
        //日志总数
        $log_count = $this->accountLogs ->where("account_id = $this->account_id")->count();
        $this->assign('log_count',$log_count);
        //权限设备统计
        $account_devices = $this->accounts->getAccountAppDevicesCount($this->account_id);
        $this->assign('account_devices',$account_devices);
        //关注统计
        $att = $this->accountPurview->getAppAttentionsCount($this->account_id);
        $this->assign('att',$att);
       
        //账号统计
        $accounts = $this->accounts->getAccountsCount();
        $accounts['round'] = 100 * round(($accounts['inline'] / $accounts['total']),2)."%";

        $this->assign('accounts',$accounts);
        //总设备统计
        $devices = $this->device->getAppDevicesCount();
        $devices['round'] = 100 * round(($devices['inline'] / $devices['total']),2)."%";
        $this->assign('devices',$devices);
        
        //通话记录统计
        $data = M("device_app_call_data")->where("type = 1")->find();
        $data['round'] = 100 * round(( ($data['all'] - $data['missed']) / $data['all']),2)."%";
        $this->assign('data',$data);

        //本账户 最近的10记录
        $account_logs = M("account_logs")->where("account_id = {$this->account_id}")->order("id desc")->limit(10)->select();
        $this->assign('account_logs',$account_logs);

        //获取系统信息
        $a = array(
             'os' => $_SERVER["SERVER_SOFTWARE"], //获取服务器标识的字串
            'version' => PHP_VERSION, //获取PHP服务器版本
             'osname' => php_uname(), //获取系统类型及版本号
             'max_upload' => ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled", //最大上传
             'max_ex_time' => ini_get("max_execution_time")."秒", //脚本最大执行时间
        );
        $b = M()->query("select version() as verion");
        $a['mysql_version'] = $b[0]['verion'];//mysql版本
        //磁盘空间
        $a['free'] = round((@disk_free_space('/')/(1024*1024*1024)),2);
        $a['total'] = round((@disk_total_space('/')/(1024*1024*1024)),2);
        $a['use'] = $a['total'] - $a['free'];
        $a['round'] = 100 * round($a['use'] / $a['total'],2);
        $this->accountLogs->addLog('首页查看');
        $this->assign('a',$a);
        $this->display();
    }

    /**
     * 语音转换测试
     */
    public function truncateTest(){
        $this->display();
    }

    public function doTruncate(){
        $upload_file = "/var/www/tpxianfeng/public/test/";
        $type = I('type');
        $a = move_uploaded_file($_FILES['files']['tmp_name'],$upload_file.$_FILES['files']['name']);
        if(!$a){
            $this->error('上传失败');
        }
        $file = $upload_file.$_FILES['files']['name'];
        $appid = '5aebccd1';
        $appkey = 'f5880b16d22fd0475c376d0619c9e1b5';
        $param = ['engine_type' => $type,'aue' => 'raw'];
        $time = (string)time();
        $x_param = base64_encode(json_encode($param));
        $header_data = [
            'X-Appid:'.$appid,
            'X-CurTime:'.$time,
            'X-Param:'.$x_param, 
            'X-CheckSum:'.md5($appkey.$time.$x_param),        
            'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
        ];    //Body
        $file_content = file_get_contents($file);
        $body_data = 'audio='.urlencode(base64_encode($file_content));    //Request
        $url = "http://api.xfyun.cn/v1/service/v1/iat";   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_data);    
        $result = curl_exec($ch);
        curl_close($ch);
        dump(json_decode($result,1));
    }

    /**
     * ajax 查询设备状态
     * @param ids 权限内设备id集合
     * @param gids 权限内组id集合
     */
    function GetStatus(){
        if(IS_AJAX){
            $ids = I('ids');
            $gids = I('gids');
            $is_screen_pops = false;
            $time = time();
            $data = array();
            foreach($ids as $key=>$val){
                $stat = D("Common/DeviceStat")->getData($val);
                if ( $stat ) {
                    $data[$val] = $stat;
                }
            }
            $last_time = $time - D("Common/DeviceStat")->_last_time;
            foreach($gids as $key=>$val){
                 $gdata[$val] = M("devices")
                            ->join("left join device_stat on devices.id = device_stat.device_id")
                            ->join("left join account_purview on devices.id = account_purview.device_id")
                            ->field("devices.*,device_stat.*,account_purview.device_id")->where("devices.registered = 1 and devices.closed = 0 and account_purview.account_id = {$this->account_id} and devices.group_id = {$val} and device_stat.last_time >= {$last_time}")->count();
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'非法请求'));exit;
        }
        
        $loginout = D("Common/AccountLogin")->last($this->account_id);
        if ( $loginout == false ) {
            echo json_encode(array('status'=>400,));exit;
        }
        echo json_encode(array('status'=>200,'data' =>$data,'gdata' =>$gdata, ));
    }
    
    function showGetLineStatus(){
        $ids = \Esy\Requests::get('ids');
        
        $_models = new \Models\DeviceLine();
        while(list($key,$val)=@each($ids)){
            $stat = $_models->getData($val);
            if ( $stat ) {
                if ( $val == 'case') {
                    $data['cased'] = $stat;
                }
                $data[$val] = $stat;
            }
        }
        \Esy\View::json(array(
            'status'=>200,
            'data' =>$data,
        ));
    }
    
    /**
     * 检测设备和账号在线在线情况 
     * 检测是否登录状态 ：目前做法是用户登录的时候记录登录时间，然后检测当前时间减去登录时间 如果超出200s说明 没有登录 这个做法是错误的 如果用户保持登录 不去操作 但不退出 一般来说session是20分钟左右，那么显然用户登录中但是服务端会认为是登出
     修改方法：
        使用session处理 将用户最新的sessionid存储数据库中，服务器检测，查询所有当前的session，进行匹配，能匹配说明在线，匹配不上说明不在线
     */
    function GetAll(){
        $time = time();
        $account_total = M("account_login")->count(); //所有账户数量
        $last_time = $time - D("Common/AccountLogin")->getLastTime();//在线的最早时间条件
        $account_online = M("account_login")->where("last_time > {$last_time}")->count();//在线账户数量

        $device_total = M("devices")
                        ->Join("left join device_stat on devices.id = device_stat.device_id")
                        ->join("left join account_purview on devices.id = account_purview.device_id")
                        ->field("devices.id")
                        ->where("devices.registered = 1 and devices.closed = 0 and account_purview.account_id =  {$this->account_id}")
                        ->count();
        $last_time = time() - D("Common/Devices")->_last_time;//设备最后在线时间
        $device_online = M("devices")
                        ->Join("left join device_stat on devices.id = device_stat.device_id")
                        ->join("left join account_purview on devices.id = account_purview.device_id")
                        ->field("devices.id")
                        ->where("devices.registered = 1 and devices.closed = 0 and account_purview.account_id =  {$this->account_id} and device_stat.last_time >= {$last_time}")
                        ->count();
        //只要用户进行了这项操作 就更新session
        D("Common/AccountLogin")->updateSession();
        echo json_encode(array(
            'status'=>200,
            'account_total' => $account_total,
            'account_online' => $account_online,
            'device_total' => $device_total,
            'device_online' => $device_online,
        ));
    }
    public function updateVersion(){
        $log =  SITE_PATH . '/version.txt';
        $content=I('content');
        if(empty($content)){
            exit(['status'=>300,'msg'=>'内容不能为空']);
        }
        // linux的换行是 \n  windows是 \r\n
        // FILE_APPEND 不写第三个参数默认是覆盖，写的话是追加
        file_put_contents($log, $content);
        echo json_encode(['status'=>200,'msg'=>'操作成功']);
    }
}