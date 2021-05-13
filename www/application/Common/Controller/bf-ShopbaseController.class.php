<?php
/**
 * 初始化控制器
 */
namespace Common\Controller;
use Common\Controller\AppframeController;
class ShopbaseController extends AppframeController {
	protected $deviceGroupModel;
	protected $accountPurview;
	protected $accountLogs;
	protected $device;
	protected $deviceStat;
	protected $accounts;
	protected $accountPurviewLine;
	protected $deviceCall;
	//一级导航
	protected $first_nav = array();
	protected $nav_id;
	protected $_passwdkey = 'Xrz9ENVSZiHbCjkW';
    protected $account = array();
    protected $account_id = null;
    protected $isAdmin=false;
	public function __construct() {
		parent::__construct();

	}

	function _initialize() {
    	parent::_initialize();
    	self::loadLogin(); //检测登录状态
        self::navInit();//菜单初始化
        self::modelInit();//模型初始化
        self::checkType();//检测当前是手机登录还是pc登录
        self::checkAuth();//权限检测
    }

    protected function checkType(){
    	$is = self::isMobile();
    	if($is){ //如果是一定端设备 动态修改配置
    		C('SP_ADMIN_TMPL_PATH','tmp_mobile');
            self::mobile_navInit();
    	}
    }

    /**
     * 检测客户端是手机还是pc
     */
    private function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }
        return false;
    }

	/**
     * 登录检测
     */
    public function loadLogin(){
        $account = I("session.ACCOUNTS");
    //     $account = array(
    //     	"id" =>"1",
			 // "name" =>  "Admin",
			 //  "account" => "admin",
			 //  "password" =>  "e74f29fc74af6d01775f26f430dfe98b",
			 //  "branch" => "test",
			 //  "tel" => "134365446041",
			 //  "email" =>"575158@qq.com",
			 //  "purview" => "C|D|E|I|J|K|M|B|F|L|A|G|H|N",
			 //  "keywords" =>"Admin,admin,tets,575158@qq.com",
			 //  "add_time" =>  "2016-01-24 15:33:26",
			 //  "upd_time" =>  "0000-00-00 00:00:00",
			 //  "last_time" => "1531453371",
			 //  "login_time" =>  "1531453371"
    //     );
        if (!$account) {
            // $this->redirect(U("Login/Index/login"));
            exit("<script>parent.location='".U("Admin/Login/Index")."'</script>");
        }
        $this->account = $account;
        $this->account_id = $account['id'];
        if(in_array($this->account_id,[10000000])){
            $this->isAdmin=true;
        }
        $seo = C('seo');
        $this->assign('ACCOUNT',$this->account);
        $this->assign('SEO',$seo);
    }

    public function checkAuth(){
    	$account_id = $this->account_id;
    	$auths = M("auth_list")->field("id,app,model,action")->select();
    	$auth = M("accounts_auth")->where("account_id = {$account_id}")->getField('auth_id',true);
    	foreach($auths as $k=>$v){
    		if(MODULE_NAME == $v['app'] && CONTROLLER_NAME == $v['model'] && ACTION_NAME == $v['action']){
    			//检测是否有权限
    			if(!in_array($v['id'],$auth)){
    				$this->error("您没有权限进行操作！");
    			}
    		}
    	}
    }

	/**
     * 初始化菜单和初始数据
     */
    public function navInit(){

        $this->first_nav=$this->getNavType()['first_nav'][C('APP_TYPE')];
        $authIds = M("accounts_auth")->where(["account_id" =>$this->account_id])->select();
        $authIds=array_column($authIds,'auth_id');
        $model=[];
        if($authIds){
            $auth=M('auth_list')->where(['id'=>['in',$authIds]])->field('DISTINCT(model) as model')->select();
            $model = array_column($auth,'model');
        }
        $nav=[];
        if($model){
            foreach ($this->first_nav as $v){
                $v1=explode("s=",$v['a']);
                $t=explode('/',$v1[1])[2];
                if(in_array($t,$model)||in_array(ucfirst($t),$model)){
                    $nav[]=$v;
                }
            }
            $nav[]= array('id'=>19,'a'=>U('Admin/AppVersion/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>app版本');
            $nav[]= array('id'=>20,'a'=>U('Admin/Concats/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>拨号管理');

        }


        if(stripos($_SERVER['HTTP_HOST'],'pioneerw')!==false){
            $this->first_nav=array_merge($this->first_nav,[
                array('id'=>90,'a'=>U('Admin/Project/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>项目信息'),
            ]);
        }
        $account = I("session.ACCOUNTS");
        $this->account_id = $account['id'];
        if(in_array($this->account_id,[10000000])){
            $this->isAdmin=true;
        }
        if($this->isAdmin){
            $nav[]=
                array('id'=>21,'a'=>U('Admin/Log/index'),'class'=>'boss','name'=>'<i class="icon-user"></i>程序日志');
        }
  
        $this->first_nav=$nav;
        $nav = $this->first_nav;
        $this->assign('nav',$nav);
    }

    public function mobile_navInit(){
        $nav[-1]['name'] = "首页";
        $nav[-1]['a'] = U('Admin/Index/index');
        $nav[0]['name'] = "设备状态";
        $nav[0]['a'] = U('/Admin/Device/status');
        $nav[1]['name'] = "手机设备状态";
        $nav[1]['a'] = U('/Admin/DeviceApp/status');
        $nav[2]['name'] = "语音转写";
        $nav[2]['a'] = U('Admin/Truncate/index');
        $nav[3]['name'] = "我的关注";
        $nav[3]['a'] = "Line";
        $nav[3]['child'][0]['name'] = "关注设备";
        $nav[3]['child'][0]['a'] = U('Admin/Attention/index');
        $nav[3]['child'][1]['name'] = "关注记录";
        $nav[3]['child'][1]['a'] = U('Admin/Attention/logList');

        $nav[4]['name'] = "数据统计";
        $nav[4]['a'] = "Line";
        $nav[4]['child'][0]['name'] = "数据总览";
        $nav[4]['child'][0]['a'] = U('Admin/Data/index_calls');
        $nav[4]['child'][1]['name'] = "数据统计";
        $nav[4]['child'][1]['a'] = U('Admin/Data/Statistics');
        $nav[4]['child'][2]['name'] = "报表生成";
        $nav[4]['child'][2]['a'] = U('Admin/Data/forms');

        $nav[5]['name'] = "app数据统计";
        $nav[5]['a'] = "Line";
        $nav[5]['child'][0]['name'] = "app数据总览";
        $nav[5]['child'][0]['a'] = U('Admin/DataApp/index_calls');
        $nav[5]['child'][1]['name'] = "app数据统计";
        $nav[5]['child'][1]['a'] = U('Admin/DataApp/Statistics');
        $nav[5]['child'][2]['name'] = "app报表生成";
        $nav[5]['child'][2]['a'] = U('Admin/DataApp/forms');


        $nav[6]['name'] = "客户资料";
        $nav[6]['a'] = "Line";
        $nav[6]['child'][0]['name'] = "联系人";
        $nav[6]['child'][0]['a'] = U('Admin/Concat/index');
        $nav[6]['child'][1]['name'] = "客户分类";
        $nav[6]['child'][1]['a'] = U('Admin/Concat/GroupList');

        $nav[7]['name'] = "设备管理";
        $nav[7]['a'] = "Line";
        $nav[7]['child'][0]['name'] = "设备管理";
        $nav[7]['child'][0]['a'] = U('Admin/DeviceCT/index');
        $nav[7]['child'][1]['name'] = "设备组管理";
        $nav[7]['child'][1]['a'] = U('Admin/DeviceCT/GroupList');

        $nav[8]['name'] = "账户管理";
        $nav[8]['a'] = "Line";
        $nav[8]['child'][0]['name'] = "账号列表";
        $nav[8]['child'][0]['a'] = U('Admin/Admins/index');
        $nav[8]['child'][1]['name'] = "添加账号";
        $nav[8]['child'][1]['a'] = U('Admin/Admins/add');
        $this->assign('nav',$nav);
    }



    public function modelInit() {
		$this->deviceGroupModel = D("Common/deviceGroup");
		$this->accountPurview = D("Common/AccountPurview");
		$this->accountLogs = D("Common/AccountLogs");
		$this->device = D("Common/Devices");
		$this->deviceStat = D("Common/DeviceStat");
		$this->accounts = D("Common/Accounts");
		$this->accountPurviewLine = D("Common/AccountPurviewLine");
		$this->deviceCall = D("Common/DeviceCall");
	}

    /**
     * PHPExcel导入导出的方法
     * excel导出
     * @param expTitle 标题
     * @param expCellName 头部
     * @param expTableData 数据
    */
    protected function exportExcel($expTitle,$expCellName,$expTableData){
        //原来的名称不使用，防止出现中文
        $expTitle = str_replace(['s=','/Admin'],['',''],$_SERVER['QUERY_STRING']);
        $expTitle = explode('/',$expTitle);
        $expTitle = $expTitle[1];
       $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
       $fileName = time();//导出excal 文件名称
       $cellNum = count($expCellName);//有多少列
       $dataNum = count($expTableData);//有多少行
       ini_set("memory_limit", "1024M");
       //生成文件
        $path = './public/CSV/'.$expTitle.date("Y-m-d").'.csv';

        $fp = fopen($path, 'w');
       for($i=0;$i<$cellNum;$i++){
          $header[$i] = iconv('UTF-8','gb2312',$expCellName[$i][1]);
       }
       fputcsv($fp, $header);// 将数据通过fputcsv写到文件句柄
       //从第三行开始插入数据
       $i=0;
       foreach($expTableData as $k=>$v){
            foreach($expCellName as $x=>$y){
                $value[$i][] = iconv('UTF-8','gb2312',$v[$y[0]]);
            }
            fputcsv($fp, $value[$i]);// 将数据通过fputcsv写到文件句柄
            $i++;
       }
       fclose($fp);

       return $path;
    }

	/**
	 * 消息提示
	 *
	 * @param type $message
	 * @param type $jumpUrl
	 * @param type $ajax
	 */
	public function success($message = '', $jumpUrl = '', $ajax = false) {
		parent::success ( $message, $jumpUrl, $ajax );
	}

	/**
	 * 模板显示
	 *
	 * @param type $templateFile
	 *        	指定要调用的模板文件
	 * @param type $charset
	 *        	输出编码
	 * @param type $contentType
	 *        	输出类型
	 * @param string $content
	 *        	输出内容
	 *        	此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
	 */
	public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
		parent::display ( $this->parseTemplate ( $templateFile ), $charset, $contentType );
	}

	/**
	 * 自动定位模板文件
	 *
	 * @access protected
	 * @param string $template
	 *        	模板文件规则
	 * @return string
	 */
	public function parseTemplate($template = '') {
		$tmpl_path = C ( "SP_ADMIN_TMPL_PATH" );
		// 获取当前主题名称
		$theme = C ( 'SP_ADMIN_DEFAULT_THEME' );
		if (is_file ( $template )) {
			// 获取当前主题的模版路径
			define ( 'THEME_PATH', $tmpl_path . $theme . "/" );
			return $template;
		}
		$depr = C ( 'TMPL_FILE_DEPR' );
		$template = str_replace ( ':', $depr, $template );

		// 获取当前模块
		$module = MODULE_NAME . "/";
		if (strpos ( $template, '@' )) { // 跨模块调用模版文件
			list ( $module, $template ) = explode ( '@', $template );
		}
		// 获取当前主题的模版路径
		define ( 'THEME_PATH', $tmpl_path . $theme . "/" );

		// 分析模板文件规则
		if ('' == $template) {
			// 如果模板文件名为空 按照默认规则定位
			$template = CONTROLLER_NAME . $depr . ACTION_NAME;
		} elseif (false === strpos ( $template, '/' )) {
			$template = CONTROLLER_NAME . $depr . $template;
		}

		C ( "TMPL_PARSE_STRING.__TMPL__", __ROOT__ . "/" . THEME_PATH );

		C ( 'SP_VIEW_PATH', $tmpl_path );
		C ( 'DEFAULT_THEME', $theme );

		$file = THEME_PATH . $module . $template . C ( 'TMPL_TEMPLATE_SUFFIX' );
		if (! is_file ( $file )){
			E ( L ( '_TEMPLATE_NOT_EXIST_' ) . ':' . $file );
		}

		return $file;
	}

	/**
     * 权限检测
     */
    protected function chkPurview($purview,$my_purview=null){
        if ( empty($purview) ) {
            return false;
        }
        $my_purview = empty($my_purview) ? $this->account['purview'] : $my_purview;
        $all_purview = explode('|', $my_purview);
        if ( in_array($purview,$all_purview)) {
            return true;
        }
        return false;
    }

	public function page($Total_Size = 1, $Page_Size = 0,$config) {
        import ( 'Page' );

        $Page = new \Think\Page ( $Total_Size, $Page_Size,$config);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('last', '末页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
        $Page->lastSuffix = false;//最后一页不显示为总页数
        return $Page;
    }

    public function getNavType(){
        $r=[];
        $r['first_nav'][0] = array(
            array('id'=>1,'a'=>U('Admin/Index/index'),'class'=>'system','name'=>'<i class="icon-home"></i>首页'),
            array('id'=>9,'a'=>U('/Admin/Device/status'),'class'=>'boss','name'=>'<i class="icon-off"></i>设备状态'),
            array('id'=>14,'a'=>U('/Admin/DeviceCall/showLogs'),'class'=>'boss','name'=>'<i class="icon-off"></i>记录查询'),
            array('id'=>13,'a'=>U('/Admin/DeviceApp/status'),'class'=>'boss','name'=>'<i class="icon-phone"></i>手机设备'),
            //            array('id'=>10,'a'=>U('Admin/Truncate/index'),'class'=>'boss','name'=>'<i class="icon-exchange"></i>语音转写'),
            array('id'=>2,'a'=>U('Admin/Attention/index'),'class'=>'boss','name'=>'<i class="icon-star"></i>我的关注'),
            array('id'=>3,'a'=>U('Admin/Data/index_devices'),'class'=>'boss','name'=>'<i class="icon-list-alt"></i>数据统计'),
            array('id'=>4,'a'=>U('Admin/DataApp/index_devices'),'class'=>'boss','name'=>'<i class="icon-list-alt"></i>app数据统计'),
            array('id'=>6,'a'=>U('Admin/Concat/index'),'class'=>'boss','name'=>'<i class="icon-group"></i>通讯录'),
            array('id'=>7,'a'=>U('/Admin/DeviceCT/index'),'class'=>'boss','name'=>'<i class="icon-list"></i>设备管理'),
            array('id'=>12,'a'=>U('/Admin/DeviceApp/index'),'class'=>'boss','name'=>'<i class="icon-phone-sign"></i>手机管理'),
            array('id'=>8,'a'=>U('Admin/Admins/index'),'class'=>'boss','name'=>'<i class="icon-user"></i>账户管理'),
            array('id'=>11,'a'=>U('Admin/Auth/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>权限管理'),
            array('id'=>19,'a'=>U('Admin/AppVersion/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>app版本'),
        );

        $nav[-1]['name'] = "首页";
        $nav[-1]['a'] = U('Admin/Index/index');
        $nav[0]['name'] = "设备状态";
        $nav[0]['a'] = U('/Admin/Device/status');
        $nav[1]['name'] = "手机设备状态";
        $nav[1]['a'] = U('/Admin/DeviceApp/status');
//        $nav[2]['name'] = "语音转写";
//        $nav[2]['a'] = U('Admin/Truncate/index');
        $nav[3]['name'] = "我的关注";
        $nav[3]['a'] = "Line";
        $nav[3]['child'][0]['name'] = "关注设备";
        $nav[3]['child'][0]['a'] = U('Admin/Attention/index');
        $nav[3]['child'][1]['name'] = "关注记录";
        $nav[3]['child'][1]['a'] = U('Admin/Attention/logList');

        $nav[4]['name'] = "数据统计";
        $nav[4]['a'] = "Line";
        $nav[4]['child'][0]['name'] = "数据总览";
        $nav[4]['child'][0]['a'] = U('Admin/Data/index_calls');
        $nav[4]['child'][1]['name'] = "数据统计";
        $nav[4]['child'][1]['a'] = U('Admin/Data/Statistics');
        $nav[4]['child'][2]['name'] = "报表生成";
        $nav[4]['child'][2]['a'] = U('Admin/Data/forms');

        $nav[5]['name'] = "app数据统计";
        $nav[5]['a'] = "Line";
        $nav[5]['child'][0]['name'] = "app数据总览";
        $nav[5]['child'][0]['a'] = U('Admin/DataApp/index_calls');
        $nav[5]['child'][1]['name'] = "app数据统计";
        $nav[5]['child'][1]['a'] = U('Admin/DataApp/Statistics');
        $nav[5]['child'][2]['name'] = "app报表生成";
        $nav[5]['child'][2]['a'] = U('Admin/DataApp/forms');


        $nav[6]['name'] = "通讯录";
        $nav[6]['a'] = "Line";
        $nav[6]['child'][0]['name'] = "联系人";
        $nav[6]['child'][0]['a'] = U('Admin/Concat/index');
        $nav[6]['child'][1]['name'] = "通讯组";
        $nav[6]['child'][1]['a'] = U('Admin/Concat/GroupList');

        $nav[7]['name'] = "设备管理";
        $nav[7]['a'] = "Line";
        $nav[7]['child'][0]['name'] = "设备管理";
        $nav[7]['child'][0]['a'] = U('Admin/DeviceCT/index');
        $nav[7]['child'][1]['name'] = "设备组管理";
        $nav[7]['child'][1]['a'] = U('Admin/DeviceCT/GroupList');

        $nav[8]['name'] = "账户管理";
        $nav[8]['a'] = "Line";
        $nav[8]['child'][0]['name'] = "账号列表";
        $nav[8]['child'][0]['a'] = U('Admin/Admins/index');
        $nav[8]['child'][1]['name'] = "添加账号";
        $nav[8]['child'][1]['a'] = U('Admin/Admins/add');
        $r['m_nav'][0]=$nav;

        $r['first_nav'][1] = array(
            array('id'=>1,'a'=>U('Admin/Index/index'),'class'=>'system','name'=>'<i class="icon-home"></i>首页'),
            array('id'=>9,'a'=>U('/Admin/Device/status'),'class'=>'boss','name'=>'<i class="icon-off"></i>设备状态'),
            array('id'=>14,'a'=>U('/Admin/DeviceCall/showLogs'),'class'=>'boss','name'=>'<i class="icon-off"></i>记录查询'),
            // array('id'=>10,'a'=>U('Admin/Truncate/index'),'class'=>'boss','name'=>'<i class="icon-exchange"></i>语音转写'),
            array('id'=>2,'a'=>U('Admin/Attention/index'),'class'=>'boss','name'=>'<i class="icon-star"></i>我的关注'),
            array('id'=>3,'a'=>U('Admin/Data/index_devices'),'class'=>'boss','name'=>'<i class="icon-list-alt"></i>数据统计'),
            array('id'=>6,'a'=>U('Admin/Concat/index'),'class'=>'boss','name'=>'<i class="icon-group"></i>通讯录'),
            array('id'=>7,'a'=>U('/Admin/DeviceCT/index'),'class'=>'boss','name'=>'<i class="icon-list"></i>设备管理'),
            array('id'=>8,'a'=>U('Admin/Admins/index'),'class'=>'boss','name'=>'<i class="icon-user"></i>账户管理'),
            array('id'=>11,'a'=>U('Admin/Auth/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>权限管理'),
        );
        $nav=[];
        $nav[0]['name'] = "首页";
        $nav[0]['a'] = U('Admin/Index/index');
        $nav[1]['name'] = "设备状态";
        $nav[1]['a'] =  U('Admin/Device/status');
//$nav[2]['name'] = "语音转写";
//$nav[2]['a'] = U('Admin/Truncate/index');
        $nav[3]['name'] = "我的关注";
        $nav[3]['a'] = "Line";
        $nav[3]['child'][0]['name'] = "关注设备";
        $nav[3]['child'][0]['a'] = U('Admin/Attention/index');
        $nav[3]['child'][1]['name'] = "关注记录";
        $nav[3]['child'][1]['a'] = U('Admin/Attention/logList');

        $nav[4]['name'] = "数据统计";
        $nav[4]['a'] = "Line";
        $nav[4]['child'][0]['name'] = "数据总览";
        $nav[4]['child'][0]['a'] = U('Admin/Data/index_calls');
        $nav[4]['child'][1]['name'] = "数据统计";
        $nav[4]['child'][1]['a'] = U('Admin/Data/Statistics');
        $nav[4]['child'][2]['name'] = "报表生成";
        $nav[4]['child'][2]['a'] = U('Admin/Data/forms');

        $nav[5]['name'] = "通讯录";
        $nav[5]['a'] = "Line";
        $nav[5]['child'][0]['name'] = "联系人";
        $nav[5]['child'][0]['a'] = U('Admin/Concat/index');
        $nav[5]['child'][1]['name'] = "通讯组";
        $nav[5]['child'][1]['a'] = U('Admin/Concat/GroupList');

        $nav[6]['name'] = "设备管理";
        $nav[6]['a'] = "Line";
        $nav[6]['child'][0]['name'] = "设备管理";
        $nav[6]['child'][0]['a'] = U('Admin/DeviceCT/index');
        $nav[6]['child'][1]['name'] = "设备组管理";
        $nav[6]['child'][1]['a'] = U('Admin/DeviceCT/GroupList');

        $nav[7]['name'] = "账户管理";
        $nav[7]['a'] = "Line";
        $nav[7]['child'][0]['name'] = "账号列表";
        $nav[7]['child'][0]['a'] = U('Admin/Admins/index');
        $nav[7]['child'][1]['name'] = "添加账号";
        $nav[7]['child'][1]['a'] = U('Admin/Admins/add');
        $r['m_nav'][1]=$nav;
//app
        $r['first_nav'][2] = array(
            array('id'=>1,'a'=>U('Admin/Index/index_App'),'class'=>'system','name'=>'<i class="icon-home"></i>首页'),
            array('id'=>13,'a'=>U('/Admin/DeviceApp/status'),'class'=>'boss','name'=>'<i class="icon-phone"></i>手机设备'),
            array('id'=>2,'a'=>U('Admin/Attention/app_index'),'class'=>'boss','name'=>'<i class="icon-star"></i>我的关注'),
            array('id'=>4,'a'=>U('Admin/DataApp/index_devices'),'class'=>'boss','name'=>'<i class="icon-list-alt"></i>app数据统计'),
            array('id'=>6,'a'=>U('Admin/Concat/index'),'class'=>'boss','name'=>'<i class="icon-group"></i>通讯录'),
            array('id'=>12,'a'=>U('/Admin/DeviceApp/index'),'class'=>'boss','name'=>'<i class="icon-phone-sign"></i>手机管理'),
            array('id'=>8,'a'=>U('Admin/Admins/index'),'class'=>'boss','name'=>'<i class="icon-user"></i>账户管理'),
            array('id'=>11,'a'=>U('Admin/Auth/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>权限管理'),
            array('id'=>19,'a'=>U('Admin/AppVersion/index'),'class'=>'boss','name'=>'<i class="icon-check"></i>app版本'),
        );
        $nav=[];
        $nav[0]['name'] = "首页";
        $nav[0]['a'] = U('Admin/Index/index_App');
        $nav[1]['name'] = "设备状态";
        $nav[1]['a'] = U('/Admin/Device/status');
//$nav[2]['name'] = "语音转写";
//$nav[2]['a'] = U('Admin/Truncate/index');
        $nav[3]['name'] = "我的关注";
        $nav[3]['a'] = "Line";
        $nav[3]['child'][0]['name'] = "关注设备";
        $nav[3]['child'][0]['a'] = U('Admin/Attention/index');
        $nav[3]['child'][1]['name'] = "关注记录";
        $nav[3]['child'][1]['a'] = U('Admin/Attention/logList');

        $nav[4]['name'] = "数据统计";
        $nav[4]['a'] = "Line";
        $nav[4]['child'][0]['name'] = "数据总览";
        $nav[4]['child'][0]['a'] = U('Admin/Data/index_calls');
        $nav[4]['child'][1]['name'] = "数据统计";
        $nav[4]['child'][1]['a'] = U('Admin/Data/Statistics');
        $nav[4]['child'][2]['name'] = "报表生成";
        $nav[4]['child'][2]['a'] = U('Admin/Data/forms');

        $nav[5]['name'] = "通讯录";
        $nav[5]['a'] = "Line";
        $nav[5]['child'][0]['name'] = "联系人";
        $nav[5]['child'][0]['a'] = U('Admin/Concat/index');
        $nav[5]['child'][1]['name'] = "通讯组";
        $nav[5]['child'][1]['a'] = U('Admin/Concat/GroupList');

        $nav[6]['name'] = "设备管理";
        $nav[6]['a'] = "Line";
        $nav[6]['child'][0]['name'] = "设备管理";
        $nav[6]['child'][0]['a'] = U('Admin/DeviceCT/index');
        $nav[6]['child'][1]['name'] = "设备组管理";
        $nav[6]['child'][1]['a'] = U('Admin/DeviceCT/GroupList');

        $nav[7]['name'] = "账户管理";
        $nav[7]['a'] = "Line";
        $nav[7]['child'][0]['name'] = "账号列表";
        $nav[7]['child'][0]['a'] = U('Admin/Admins/index');
        $nav[7]['child'][1]['name'] = "添加账号";
        $nav[7]['child'][1]['a'] = U('Admin/Admins/add');

        $r['m_nav'][2]=$nav;
        return $r;
    }



}
