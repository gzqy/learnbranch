<?php

namespace Admin\Controller;

use Common\Controller\ShopbaseController;
use Common\Utils\LoginUtil;

/*
 */

class LogController extends ShopbaseController
{
    
    function _initialize()
    {
        parent::_initialize();
        if(!$this->isAdmin){
            $this->error('Admin账号才有此权限');
        }
    }
    public function index(){
        $path    = SITE_PATH . 'runtime_log.txt';
        $open=file_get_contents($path);
        $this->assign('open',$open);
        $this->display();
    }
    public function update(){
        $path    = SITE_PATH . 'runtime_log.txt';
        file_put_contents($path,I('open'));
    }
    public function import(){
        $s=I('model');
        $s=$s=='Api'?'Api':'Admin';
        $logPath='data/runtime/Logs/'.$s.'/'.date('Ym').'/'.date('d').'.log';
        $file=date('d').'.log';
        $files=$logPath;
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
    
        //Use the switch-generated Content-Type
        header("Content-Type: text/html");
        $len = filesize($files);
        //Force the download
        $header="Content-Disposition: attachment; filename=".$file.";";
        header( $header );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$len);
        @readfile($files);
    }
}
