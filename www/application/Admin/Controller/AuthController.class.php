<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
class AuthController extends ShopbaseController {
	 protected $nav_id = 11;
    /**
     * 前置操作
     */
    public function _initialize() {
    	parent::_initialize ();
    	$second_nav = array(
            array('id'=>1,'a'=>'./index.php?s=/Admin/Auth/index','name'=>'权限列表'),
            array('id'=>2,'a'=>'./index.php?s=/Admin/Auth/GroupList','name'=>'权限组管理'),
        );
        $this->assign('second_nav',$second_nav);
       	$this->assign('nav_id',$this -> nav_id);
    }

    /**
     * 权限列表
     * @param name 权限名称
     * @param add_time_min 添加权限时间最小值
     * @param add_time_max 添加权限时间最大值
     * @param edit_time_min 编辑权限时间最小值
     * @param edit_time_max 编辑权限时间最大值
     * @param app 模块
     * @param model 控制器
     * @param action 操作 
     */
    public function index(){
    	$p = I('p') ? I('p') : 1;
    	$limit = I('limit') ? I('limit') : 20;
    	$name = I('name');
    	$add_time_min = I('add_time_min');
    	$add_time_max = I('add_time_max');
    	$edit_time_min = I('edit_time_min');
    	$edit_time_max = I('edit_time_max');
    	$app = I('app');
    	$model = I('model');
    	$action = I('action');
    	$groupId = I('group_id');

    	$where = array();
    	$name && $where['name'] = array('like',"%{$name}%");
        if($add_time_min && $add_time_max){
            $where['add_time'] = array('between',array(strtotime($add_time_min),strtotime($add_time_max)));
        }else{
            $add_time_min && $where['add_time'] = array('gt',strtotime($add_time_min));
            $add_time_max && $where['add_time'] = array('gt',strtotime($add_time_max));
        }
    	if($edit_time_min && $edit_time_max){
            $were['edit_time'] = array('between',array(strtotime($edit_time_min),strtotime($edit_time_max)));
        }else{
            $edit_time_min && $where['edit_time'] = array('gt',strtotime($edit_time_min));
            $edit_time_max && $where['edit_time'] = array('gt',strtotime($edit_time_max)); 
        }
    	$app && $where['app'] = array('like',"%{$app}%");
    	$model && $where['model'] = array('like',"%{$model}%");
    	$action && $where['action'] = array('like',"%{$action}%");
    	$groupId && $where['group_id'] = array('eq',$groupId);

    	$count = M("auth_list")->where($where)->count();
    	$page = $this->page($count,$limit,array('group_id'=>$groupId,'p'=>$p,"name"=>$name,"add_time_min"=>$add_time_min,"add_time_max"=>$add_time_max,"edit_time_min"=>$edit_time_min,"edit_time_max"=>$edit_time_max,"app"=>$app,"model"=>$model,"action"=>$action));
    	$data = M("auth_list")->where($where)->order("group_id desc,id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $this->accountLogs->addLog("查询权限列表，查询条件：规则名称：{$name},模块名称：{$app},控制器：{$model},方法：{$action}");
        $groupList = (array)M('auth_group',null)->select();
        $this->assign('groupList',$groupList);
        $this->assign('second_nav_id',1);
    	$this->assign('name',$name);

        $this->assign('groupId',$groupId);
    	$this->assign('add_time_min',$add_time_min);
    	$this->assign('add_time_max',$add_time_max);
    	$this->assign('edit_time_min',$edit_time_min);
    	$this->assign('edit_time_max',$edit_time_max);
    	$this->assign('app',$app);
    	$this->assign('model',$model);
    	$this->assign('action',$action);
    	$this->assign('Page',$page->show());
    	$this->assign('data',$data);
    	$this->display();
    }
    
    /**
     * 设备组列表
     * @param p 分页 当前页默认为1
     * @param limit 分页 页数默认20
     * @param name 组名称
     * @param add_time_min 添加最小时间
     * @param add_time_max 添加最大时间
     * @param upd_time_min 修改最小时间
     * @param upd_time_max 修改最大时间
     */
    public function GroupList(){
        $p = I("p") ? I("p") : 1;
        $limit = I("limit") ? I("limit") : 20;
        $name  = I("name");
        $add_time_min = I('add_time_min');
        $add_time_max  =I('add_time_max');
        $upd_time_min = I('upd_time_min');
        $upd_time_max = I('upd_time_max');
        
        //where条件处理
        $name && $where['name'] = array('like',"%{$name}%");
        if($add_time_min && $add_time_max){
            $where['add_time'] = array('between',"{$add_time_min},{$add_time_max}");
        }else{
            $add_time_min && $where['add_time'] = array('gt',$add_time_min);
            $add_time_max && $where['add_time'] = array('lt',$add_time_max);
        }
        if($upd_time_min && $upd_time_max){
            $where['upd_time'] = array('between',"{$upd_time_min},{$upd_time_max}");
        }else{
            $upd_time_min && $where['upd_time'] = array('gt',$upd_time_min);
            $upd_time_max && $where['upd_time'] = array('lt',$upd_time_max);
        }
        //分页处理
        $count = M("auth_group")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"add_time_min"=>$add_time_min,"add_time_max"=>$add_time_max,"upd_time_min"=>$upd_time_min,"upd_time_max"=>$upd_time_max));
        $data = M("auth_group")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        //统计查询每个分组的设备数量
        foreach($data as $k=>$v){
            $data[$k]['num'] = M("auth_list")->where("group_id = {$v['id']} ")->count();
        }
        $this->assign('name',$name);
        $this->assign('data',$data);
        $this->assign('add_time_min',$add_time_min);
        $this->assign('add_time_max',$add_time_max);
        $this->assign('upd_time_min',$upd_time_min);
        $this->assign('upd_time_max',$upd_time_max);
        $this->assign('Page',$page->show());
        $this->assign('second_nav_id',2);
        $this->display();
    }
    /**
     * 添加、编辑分组
     * @param id 分组id
     */
    public function addGroup(){
        $id = I('id');
        if($id){ //编辑分组
            $group = M("auth_group")->find($id);
            if(!$group){
                $this->error('分组不存在');
            }
            $this->assign('data',$group);
            $this->assign('title',2);
        }else{ //添加分组
            $this->assign('title',1);
        }
        $this->assign('second_nav_id',2);
        $this->display();
    }
    public function index_excel(){
        if(1 == I('excel')){
            $name = I('name');
            $add_time_min = I('add_time_min');
            $add_time_max = I('add_time_max');
            $edit_time_min = I('edit_time_min');
            $edit_time_max = I('edit_time_max');
            $app = I('app');
            $model = I('model');
            $action = I('action');

            $where = array();
            $name && $where['name'] = array('like',"%{$name}%");
            if($add_time_min && $add_time_max){
                $where['add_time'] = array('between',array(strtotime($add_time_min),strtotime($add_time_max)));
            }else{
                $add_time_min && $where['add_time'] = array('gt',strtotime($add_time_min));
                $add_time_max && $where['add_time'] = array('gt',strtotime($add_time_max));
            }
            if($edit_time_min && $edit_time_max){
                $were['edit_time'] = array('between',array(strtotime($edit_time_min),strtotime($edit_time_max)));
            }else{
                $edit_time_min && $where['edit_time'] = array('gt',strtotime($edit_time_min));
                $edit_time_max && $where['edit_time'] = array('gt',strtotime($edit_time_max)); 
            }
            $app && $where['app'] = array('like',"%{$app}%");
            $model && $where['model'] = array('like',"%{$model}%");
            $action && $where['action'] = array('like',"%{$action}%");
            ini_set('memory_limit', '256M');
            ini_set("max_execution_time", "3600");
            $data = M("auth_list")->where($where)->order("id desc")->select();
            header ( "Content-type:application/vnd.ms-excel" );  
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
            $head =array('规则名称','模块名称','控制器名称','方法名称','添加时间','编辑时间');
            foreach($head as $k=>$v){
                $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
            }
            $i = 0;
            $fp = fopen('php://output', 'a');
            fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
            
            foreach($data as $k => $v){
                $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['app']);
                $excels[$i][] = iconv('utf-8','gbk',$v['model']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['action']);
                $excels[$i][] = date("Y-m-d H:i:s",$v['add_time']);                
                $excels[$i][] = date("Y-m-d H:i:s",$v['edit_time']);                
                fputcsv($fp,$excels[$i]);
                $i++;
            }
            $this->accountLogs->addLog("导出规则表格");
            unset($excels);  
            ob_flush();  
            flush();    
        }else{  
            $this->error("请求方式错误");
        }
    }

    /**
     * 添加权限规则
     */
    public function add(){
        $groupList = (array)M('auth_group',null)->select();
        $this->assign('groupList',$groupList);
        $this->assign('second_nav_id',1);
    	$this->display();
    }

    /**
     * 保存添加权限
     * @param name 规则名称
     * @param app 应用 默认Admin
     * @param model 控制器名称
     * @param action 方法名称
     */
    public function doAdd(){
    	if(IS_AJAX){
    		$name = I('name');
    		$app = I('app') ? I('app') : 'Admin';
    		$model = I('model');
    		$action = I('action');

    		//参数验证
    		if(!$name || !$model || !$action){
    			echo json_encode(array('code'=>300,'msg'=>'请完整填写资料'));exit;
    		}
    		if(strlen($name) > 40){
    			echo json_encode(array('code'=>300,'msg'=>'规则名称不能超过10个字'));exit;
    		}
    		$data = array();
    		$data['name'] = $name;
    		$data['app'] = $app;
    		$data['action'] = $action;
    		$data['model'] = $model;
    		$data['add_time'] = time();
    		$data['edit_time'] = time();
    		if(M('auth_list')->add($data)){
                $this->accountLogs->addLog("添加规则，规则名称：{$name},规则：{$app}/{$model}/{$action}");
    			unset($data);
    			echo json_encode(array('code'=>200,'msg'=>'添加规则成功'));exit;
    		}else{
    			echo json_encode(array('code'=>300,'msg'=>'添加规则失败'));exit;
    		}
    	}else{
    		echo json_encode(array('code'=>300,'msg'=>'请求方式不合法'));exit;
    	}
    }

    /**
     * 编辑权限规则
     * @param id 规则id
     */
    public function edit(){
    	$id = I('id','int');
    	if(!$id){
    		$this->error('请选择要编辑的规则');
    	}
    	$auth = M('auth_list')->find($id);
    	if(!$auth){
    		$this->error("规则不存在");
    	}
        $groupList = (array)M('auth_group',null)->select();
    	$this->assign('data',$auth);
        $this->assign('groupList',$groupList);
    	$this->display();
    }

    /**
     * 保存编辑规则
     * @param name 规则名称
     * @param app 应用 默认Admin
     * @param model 控制器名称
     * @param action 方法名称
     * @param id 权限id
     */
    public function doEdit(){
    	if(IS_AJAX){
    		$name = I('name');
    		$app = I('app') ? I('app') : 'Admin';
    		$model = I('model');
    		$action = I('action');
    		$id = I('id','int');

    		//参数验证
    		if(!$id){
    			echo json_encode(array('code'=>300,'msg'=>'编辑的权限不存在'));exit;
    		}
    		if(!M('auth_list')->find($id)){
    			echo json_encode(array('code'=>300,'msg'=>'编辑的权限不存在'));exit;
    		}
    		if(!$name || !$model || !$action){
    			echo json_encode(array('code'=>300,'msg'=>'请完整填写资料'));exit;
    		}
    		if(strlen($name) > 40){
    			echo json_encode(array('code'=>300,'msg'=>'规则名称不能超过10个字'));exit;
    		}
    		$data = array();
    		$data['name'] = $name;
    		$data['app'] = $app;
    		$data['action'] = $action;
    		$data['model'] = $model;
    		$data['group_id'] = (int)I('group_id');
    		$data['edit_time'] = time();
    		if(M('auth_list')->where("id = {$id}")->save($data)){
    			unset($data);
                $this->accountLogs->addLog("添加规则，规则名称：{$name},规则：{$app}/{$model}/{$action}");
    			echo json_encode(array('code'=>200,'msg'=>'编辑规则成功'));exit;
    		}else{
    			echo json_encode(array('code'=>300,'msg'=>'编辑规则失败'));exit;
    		}
    	}else{
    		echo json_encode(array('code'=>300,'msg'=>'请求方式不合法'));exit;
    	}
    }

    /**
     * 删除规则
     * @param id 规则id
     */
    public function delete(){
    	$id = I('id','int');
    	if(!$id){
    		$this->error('请选择要删除的规则');
    	}
    	$auth = M('auth_list')->find($id);
    	if(!$auth){
    		$this->error('规则不存在');
    	}
    	//删除 需要删除管理员有缘本权限的内容	
    	if(M('auth_list')->delete($id)){
    		M('accounts_auth')->where("auth_id = {$id}")->delete();
            $this->accountLogs->addLog("删除规则，规则名称：{$auth['name']},规则：{$auth['app']}/{$auth['model']}/{$auth['action']}");
    		$this->redirect('Auth/index','',3,'删除规则成功');
    	}else{ //删除失败
    		$this->error('删除规则失败');
    	}	
    }
    /**
     * 保存编辑添加分组
     * @param id 分组id
     * @param name 分组名称
     */
    public function saveGroup(){
        if(IS_AJAX){
            $data = I('data');
            if($data['id']){ //编辑
                if(!M('auth_group')->find($data['id'])){
                    echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
                }
                $data['upd_time'] = date("Y-m-d H:i:s",time());
                if(M("auth_group")->save($data)){
                    $this->accountLogs->addLog("修改分组,分组id：{$data['id']}");
                    echo json_encode(array('code'=>200,'msg'=>'编辑分组成功'));exit;
                }else{
                    echo json_encode(array('code'=>200,'msg'=>'编辑分组失败'));exit;
                }
            }else{ //添加
                if(M("auth_group")->where(array("name"=>$data['name']))->find()){
                    echo json_encode(array('code'=>200,'msg'=>'分组已经存在'));exit;
                }else{
                    //添加分组
                    $data['add_time'] = date("Y-m-d H:i:s",time());
                    $data['upd_time'] = date("Y-m-d H:i:s",time());
                    if($id = M("auth_group")->add($data)){
                        $this->accountLogs->addLog("添加分组,设备id：{$id}");
                        echo json_encode(array('code'=>200,'msg'=>'添加分组成功'));exit;
                    }else{
                        echo json_encode(array('code'=>200,'msg'=>'添加分组成功'));exit;
                    }
                }
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }
    /**
     * 删除分组
     * @param id 分组id
     */
    public function deleteGroup(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!M("auth_group")->find($id)){
                echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
            }
            //删除分组
            if(M("auth_group")->delete($id)){
                echo json_encode(array('code'=>200,'msg'=>'删除分组成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除分组失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }
}