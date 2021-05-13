<?php

namespace Api\Controller;

use Think\Controller;

class AppVersionController extends Controller
{
    public function latestVersion(){
        $version=I('version');
        $info=M('app_version')->where(['status'=>1])->order('id desc')->find();
        $currentVersion=$info['version'];
        $info['update'] = version_compare($version,$currentVersion)<0 ? 1 : 0;
        exit(json_encode(['code'=>'200','data'=>$info,'msg'=>'操作成功']));
    }
}
