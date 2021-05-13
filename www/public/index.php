<?php
/**
 * 项目入口文件 
 */
ini_set('display_errors','On'); //显示所有错误信息
// PUBLIC_PATH
define('PUBLIC_PATH', __DIR__);//定义常量 public_path 为/public/
// bootstrap
require PUBLIC_PATH.'/../bootstrap.php';//引入框架加载文件
