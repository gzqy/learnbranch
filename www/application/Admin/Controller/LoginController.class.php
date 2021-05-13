<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use Common\Utils\LoginUtil;
/*
 * 登录相关控制
 */
class LoginController extends ShopbaseController{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 重载构造方法 不要检测账号
     */
    public function _initialize() {
        $seo = C('seo');
        $this->assign('SEO',$seo);
        self::modelInit();//模型初始化
         parent::checkType();//检测当前是手机登录还是pc登录
    }

    /**
     * 登录页面
     */
    public function Index() {
        $account = I('session.ACCOUNTS');
        if ( $account ) {//session中存储账户
            //检测数据库
            $_data = $this->accounts->where("id=".$account['id'])->find();
            if($_data){ //能找到数据库数据
                $this->redirect('/');
            }else{ //找不到数据库数据
                $this->display();
            }
        }else{
            $this->display();
        }
    }

    public function testForZhongXin(){
        $zhongxin = new LoginUtil;
        $a = $zhongxin->Login('0001','123456');
        //中信证券 三方登录系统对接测试
        if(IS_POST){
            $account = I('post.account');
            $password = I('post.password');
            if(!$account || !$password){
                echo json_encode(array('status'=>300,'msg' =>'请输入正确的账号或密码!',));exit;
            }
            //进行对接验证
            $data = LoginUtil::Login($account,$password);
            if(1 == $data['code']){
                //登录成功 进行处理 检验是否已经在系统中注册过
                $user = $this->accounts->where(array('account'=>$account))->find();
                if(!$user){ //没有注册过 进行注册处理 新注册的账号默认没有登录权限 没有任何页面权限
                    $data1 = [];
                    $data1['account'] = $data['user'];
                    $data1['is_login'] = 0;
                    $data1['add_time'] = time();
                    $data1['upd_time'] = time();
                    $data1['last_time'] = time();
                    $data1['login_time'] = time();
                    $is = $this->accounts->add($data);
                    if(!$is){
                        echo json_encode(array('status'=>300,'msg' =>'工号注册失败',));exit;
                    }
                    $this->accountLogs->addLog("添加管理员,管理员id：{$is}");
                    $user = $this->accounts->where(array('account'=>$account))->find();
                }
                //进行登陆过处理
                if($user['is_login']){
                    $_SESSION['ACCOUNTS'] = $user;
                    //处理日志登录 账户登录处理
                    $this->accountLogs->addLog('登入系统');
                    $this->accounts->dologin($user);
                    echo json_encode(array('status'=>200,));exit;
                }else{
                    echo json_encode(array('status'=>300,'msg' =>'该账号不允许登录',));exit;
                }
            }else{ //登录失败
                echo json_encode(array('status'=>300,'msg' =>'请输入正确的账号或密码!',));exit;
            }
        }
    }
    
    /**
     * 处理登录方法
     * @param account 账号
     * @param password 密码
     * @param code 验证码
     */
    function dologin(){
        if(IS_POST){
            $account = I('post.account');
            $password = I('post.password');
            // $code = I('post.code');
            //验证参数
            // if(!$code){
            //     echo json_encode(array('status'=>300,'msg'=> '请输入正确的验证码'));exit;
            // }
            if(!$account || !$password){
                echo json_encode(array('status'=>300,'msg' =>'请输入正确的账号或密码!',));exit;
            }
            //验证码验证
            // $verify = new \Think\Verify();
            // if(!$verify->check($code, 1)){
            //     echo json_encode(array( 'status'=>300,'msg'=> '请输入正确的验证码'));exit;
            // }
            //验证账号密码
            $account = strtolower($account);
            $password = MD5($password.$this->_passwdkey);

            $user = $this->accounts->where(array('account'=>$account,'password'=>$password))->find();
            if($user){
                $_SESSION['ACCOUNTS'] = $user;
                //处理日志登录 账户登录处理
                $this->accountLogs->addLog('登入系统');
                $this->accounts->dologin($user);
                echo json_encode(array('status'=>200,));exit;
            }else{
                echo json_encode(array('status'=>300,'msg' =>'请输入正确的账号或密码!',));exit;
            }
        }else{
            $this->error('错误提交方式');
        }
    }

    /**
     * 生成验证码控制器
     */
    public function ValidateCode() {
       $config = array(
            'expire'    =>  1800,            // 验证码过期时间（s）
            'useImgBg'  =>  false,           // 使用背景图片
            'fontSize'  =>  30,              // 验证码字体大小(px)
            'useCurve'  =>  false,           // 是否画混淆曲线
            'useNoise'  =>  false,            // 是否添加杂点
            'length'    =>  5,               // 验证码位数
            'bg'        =>  array(243, 251, 254),  // 背景颜色
            'reset'     =>  true,           // 验证成功后是否重置
        );
        $Verify = new \Think\Verify($config);
        $Verify->codeSet = '0123456789';
        $Verify->entry(1);
    }

    /**
     * 登出系统
     */
    public function Logout() {
        $this->accountLogs->addLog('账号退出系统');
        unset($_SESSION['ACCOUNTS']);
        $this->redirect('/Admin/Login/Index');
    }
    
    /**
     * 忘记密码
     */
    public function Forget(){
        $this->display();
    }
    
    /**
     * 处理密码找回
     * @param email 用户邮箱
     * @param code 验证码
     */
    function doForget(){
        $email = \Esy\Requests::post('email');
        $code = \Esy\Requests::post('code');
        //参数验证
        if (empty($email) ) {
            \Esy\View::json(array('status'=>300,'msg' =>'请填写账号邮件！',));
        }
        if(!\Helper\Icode::chkCode($code)){
            \Esy\View::json(array('status'=>300,'msg'=> '请输入正确的验证码'));
        }
        //邮箱检测
        if(!self::checkEmail($email)){
            \Esy\View::json(array('status'=>300,'msg' =>'请输入正确的账号邮件!',));
        }
        //处理
        $_model = new \Models\Accounts();
        $_data = $_model->where("email",$email)->first();
        if ( $_data ) {
            $_data_login = \Models\AccountLogin::where('account_id',$_data->id)->first();
            if ( $_data_login->id ) {
                $time = time();
                $forget_password = md5($_data->id.$_data->email.$time);
                $_data_login->forget_password = $forget_password;
                $_data_login->forget_time = $time;
                $_data_login->save();
                $forget_link = \Esy\Config::getUrl('home_url').'/Login/toForget/?forget_key='.$forget_password;;
                $msg .= '<h3> '.$_data->email.'，你好!</h3>';
                $msg .= '<h4>已经收到了你的找回密码请求，请点击 <a href="'.$forget_link.'" target="_blank">此链接重置密码</a>。</h4>';
                $msg .= '<h4>如上述链接无法点击, 请复制链接 '.$forget_link.' 在浏览器直接打开</h4>';
                $msg .= '<h4>（本链接将在1天后失效）</h4>';
                \Helper\Mail::send(array('title'=>'找回密码','content'=>$msg,'to'=>array($_data->email=>$_data->name,),
                ));
            }
            \Esy\View::json(array('status'=>200,'msg' =>'密码重置邮件已发送至你的邮箱!','url'=>'login',));
        }else{
             \Esy\View::json(array('status'=>300,'msg' =>'请输入正确的账号和邮件!',));
        }
    }
    
    public function showToForget(){
        $forget_key = \Esy\Requests::get('forget_key');
        if ( empty($forget_key) ) {
            $this->redirect('/login');
        }
        $_data_login = \Models\AccountLogin::where('forget_password',$forget_key)->first();
        if ( empty($_data_login->id) ) {
            $this->redirect('/login');
        }
        $time = time();
        if ( $time - $_data_login->forget_time > 86400 ) {
            $this->redirect('/login');
        }
        \Esy\View::newd()->with('forget_key',$forget_key);
        $this->view();
    }
    
    
    function dotoForget(){
        $forget_key = \Esy\Requests::post('forget_key');
        $password = \Esy\Requests::post('password');
        $apassword = \Esy\Requests::post('apassword');
        if ( empty($forget_key) || $password != $apassword ) {
            \Esy\View::json(array(
                'status'=>300,
                'msg' =>'请输入两次相同的登入密码!',
            ));
        }
        $_data_login = \Models\AccountLogin::where('forget_password',$forget_key)->first();
        $time = time();

        if ( empty($_data_login->id) || $time - $_data_login->forget_time > 86400 ) {
            \Esy\View::json(array(
                'status'=>300,
                'msg' =>'找回密码链接已失效!',
            ));
        }
        $_data_login->forget_password = '';
        $_data_login->save();
        $md5_password = MD5($password.$this->_passwdkey);

        \Models\Accounts::where('id',$_data_login->account_id)->update(array(
            'password'=>$md5_password,
        ));
        \Esy\View::json(array(
            'status'=>200,
            'msg' =>'找回密码成功, 请用新的密码登入!',
            'url'=>'/login',
        ));
    }
    
    
}
