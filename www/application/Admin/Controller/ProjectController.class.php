<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
class ProjectController extends ShopbaseController
{
    protected $nav_id = 90;
    
    /**
     * 前置操作
     */
    public function _initialize()
    {
        parent::_initialize();
        $second_nav = array(
            array('id'=>1,'a'=>U('/Project/index'),'name'=>'项目列表'),
            array('id'=>2,'a'=>U('/Project/add'),'name'=>'添加项目'),
        );
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }
    
    /**
     * 项目列表
     * @param name 权限名称
     * @param add_time_min 添加权限时间最小值
     * @param add_time_max 添加权限时间最大值
     * @param edit_time_min 编辑权限时间最小值
     * @param edit_time_max 编辑权限时间最大值
     * @param app 模块
     * @param model 控制器
     * @param action 操作
     */
    public function index()
    {
        $p             = I('p') ? I('p') : 1;
        $limit         = I('limit') ? I('limit') : 20;
        $searchArr=I('');
        $where = array();
        if($_GET['import']){
            $p=1;
            $limit=10000;
        }
        $searchArr['name'] && $where['name'] = array('like', "%{$searchArr['name']}%");
        $searchArr['date_time_min'] && $where['datetime']=['egt',$searchArr['date_time_min']];
        $searchArr['date_time_max'] && $where['datetime']=['elt',$searchArr['date_time_max']];
        $searchArr['technical_director'] && $where['technical_director'] = $searchArr['technical_director'];
        $searchArr['business_director'] && $where['business_director'] = $searchArr['business_director'];
        $where['is_del']=0;
        $count = M("project_info")->where($where)->count();
        $page  = $this->page($count, $limit, array("name" => $searchArr['name'],'technical_director'=>I('technical_director'),'business_director'=>I('business_director'),'date_time_max'=>I('date_time_max'),'date_time_min'=>I('date_time_min'),));
        $data  = M("project_info")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        if($_GET['import']){
            $this->import($data);
        }
        $this->assign('searchArr',I(''));
        $this->assign('second_nav_id',1);
        $this->assign('Page', $page->show());
        $this->assign('data', $data);
        $this->_assignList();
        $this->display();
    }
    protected function import($data){
        header("Content-type: application/vnd.ms-excel; charset=gbk");
        header("Content-Disposition: attachment; filename=project.csv");
        $str="日期\t名称\t产品型号\t商务负责人\t技术负责人\t是否到款\t备注\n";

        foreach ($data as $val){
            $isPayedArr=['未到款','已到款'];
            $isPayed=$isPayedArr[$val['is_payed']];
            $str.="{$val['datetime']}\t{$val['name']}\t{$val['product_type']}\t{$val['business_director']}\t{$val['technical_director']}\t{$isPayed}\t{$val['remark']}\n";
        }
        echo $str;die;
    }
    public function add(){
        $data=I('');
        $id=$data['id'] ? $data['id'] : $_GET['id'];
        $id=intval($id);
        if(!$data['submit']){
            if($id){
                $data=M('project_info',null)->find($id);
                $this->assign('data',$data);
            }
            $this->assign('second_nav_id',2);
            $this->_assignList();
            $this->display();
        }else{
            if(!$data['name']){
                exit(json_encode(['status'=>200,'msg'=>'名称不能为空']));
            }
            if(!$data['id']){
                $info = M('project_info',null)->where(['name'=>$data['name']])->find();
                if($info){
                    exit(json_encode(['status'=>300,'msg'=>'名称已经存在']));
                }
                $id=M('project_info',null)->add($data);
            }else{
                $info = M('project_info',null)->where(['name'=>$data['name'],'id'=>['neq',$id]])->find();
                if($info){
                    exit(json_encode(['status'=>300,'msg'=>'名称已经存在']));
                }
                $id=M('project_info',null)->save($data);
            }
            exit(json_encode(['status'=>200,'msg'=>'操作成功']));
        }
    }
    public function delete(){
       $id=I('id') ? I('id') : $_GET['id'];
       $id=intval($id);
       if(empty($id)){
           $this->redirect('Project/index','',3,'缺少参数');
       }
       M('project_info',null)->delete($id);
       $this->redirect('Project/index','',2,'删除成功');
    }
    
//    public function save(){
//        $data=I('');
//        if(!$data['submit']){
//            $this->_assignList();
//            $this->display();
//        }else{
//            $id=M('project_info',null)->where(['id'=>$data['id']])->save($data);
//            exit(['status'=>200,'msg'=>'操作成功']);
//        }
//    }
    private function _assignList(){
        $this->_busiDirectorList();
        $this->_techDirectorList();
        $this->_typeList();
    }
    
    private function _techDirectorList(){
        $a=(array)M('project_info',null)->query('select distinct(technical_director) as a from project_info');
        $this->assign('techList',array_values(array_filter(array_column($a,'a'))));
    }
    private function _busiDirectorList(){
        $a=(array)M('project_info',null)->query('select distinct(business_director) as a from project_info');
        $this->assign('businessList',array_values(array_filter(array_column($a,'a'))));
    }
    private function _typeList(){
        $a=(array)M('project_info',null)->query('select distinct(product_type) as a from project_info');
        $this->assign('typeList',array_values(array_filter(array_column($a,'a'))));
    }
}
