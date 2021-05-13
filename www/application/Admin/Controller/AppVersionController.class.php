<?php

namespace Admin\Controller;

use Common\Controller\ShopbaseController;
use PHPExcel_IOFactory;
use PHPExcel;

/**
 * 通讯录板块
 */
class AppVersionController extends ShopbaseController
{
    public function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/AppVersion/index'),'name'=>'版本管理'),
            array('id'=>2,'a'=>U('/AppVersion/add'),'name'=>'添加'),
        );
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }
    public function index(){
        $list=M('app_version')->order('status desc,id desc')->select();
        $this->assign('list',$list);
        $this->display();
    }
    public function add(){
        $id=I('id');
        $info=M('app_version')->find($id);
        $this->assign('data',$info);
        $this->display();
    }
    public function doAdd(){
        $data=I('');
        if($data['id']){
            M('app_version')->where(['id'=>$data['id']])->save($data);
        }else{
            M('app_version')->add($data);
        }
        exit(json_encode(['status'=>200,'msg'=>'操作成功']));
    }
    public function delete(){
        M('app_version')->where(['id'=>I('id')])->delete();
        exit(json_encode(['status'=>200,'msg'=>'操作成功']));
    }
}