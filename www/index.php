<?php

/**
 * 项目入口文件
 * Some rights reserved：www.simplewind.net
 */
if (ini_get('magic_quotes_gpc')) {
	function stripslashesRecursive(array $array){
		foreach ($array as $k => $v) {
			if (is_string($v)){
				$array[$k] = stripslashes($v);
			} else if (is_array($v)){
				$array[$k] = stripslashesRecursive($v);
			}
		}
		return $array;
	}
	$_GET = stripslashesRecursive($_GET);
	$_POST = stripslashesRecursive($_POST);
}
//开启调试模式
define("APP_DEBUG",true);
define("SHOW_PAGE_TRACE",true);
//网站当前路径
define('SITE_PATH', dirname(__FILE__)."/");
//项目路径，不可更改
define('APP_PATH', SITE_PATH . 'application/');
//项目相对路径，不可更改
define('SPAPP_PATH',   SITE_PATH.'simplewind/');
//
define('SPAPP',   './application/');
//项目资源目录，不可更改
define('SPSTATIC',   SITE_PATH.'public/');
// define('UPLOAD_PATH',   "http://39.105.2.43:90/Record");
define('UPLOAD_PATH',   SITE_PATH."public/Record");
define('UPLOAD_PATH1',   "./public/Record");
define('DOWNLOAD_PATH',  SITE_PATH.'public/Download');
define('UPLOAD_APP_PATH',  SITE_PATH.'public/Record');
define('UPLOAD_APP_PATH1',  './public/Record');
//定义缓存存放路径
define("RUNTIME_PATH", SITE_PATH . "data/runtime/");
//静态缓存目录
define("HTML_PATH", SITE_PATH . "data/runtime/Html/");
//版本号
define("SIMPLEWIND_CMF_VERSION", 'X1.6.0');

define("THINKCMF_CORE_TAGLIBS", 'cx,Common\Lib\Taglib\TagLibSpadmin,Common\Lib\Taglib\TagLibHome');

if(function_exists('saeAutoLoader') || isset($_SERVER['HTTP_BAE_ENV_APPID'])){
	
}else{
	if(file_exists("install") && !file_exists("install/install.lock")){
		header("Location:./install");
		exit();
	}
}
//uc client root
define("UC_CLIENT_ROOT", './api/uc_client/');

if(file_exists(UC_CLIENT_ROOT."config.inc.php")){
	include UC_CLIENT_ROOT."config.inc.php";
}
//引入网页编辑
require APP_PATH.'Common/Utils/simple_html_dom.php';
//载入框架核心文件
require SPAPP_PATH.'Core/ThinkPHP.php';


