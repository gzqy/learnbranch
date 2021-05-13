<?php

namespace Admin\Controller;
use Common\Controller\ShopbaseController;
/**
 * 管理员控制器
 */
class AdminsController extends ShopbaseController {
     protected $nav_id = 8;
     //控制设备在线
     protected $_last_time = 200;
    /**
     * 前置操作
     */
    public function _initialize() {
        parent::_initialize ();
        $this->assign('second_nav',$second_nav);
        $this->assign('nav_id',$this -> nav_id);
    }

    /**
     * 管理员列表
     * @param name 管理员名称
     * @param account 登录账户
     * @param status 在线状态 0 所有 1 在线 2 离线
     * @param keywords 关键字所有
     * @param start_date 创建最小时间
     * @param end_date 创建最大时间
     * @param page 分页当前页
     * @param total 总数
     */
    public function index(){
    	$name = I('name');
    	$account = I('account');
    	$status = I('status') ? I('status') : 0;
    	$keywords = I('keywords');
    	$start_date = I('start_date');
    	$end_date = I('end_date');
    	$p = I('p') ? I('p') : 1;
    	$limit = I('limit') ? I('limit') : 20;

    	//查询处理
    	$model = D("Common/Accounts");
    	$where = array();
    	$name && $where['name'] = array('like',"%{$name}%");
    	$account && $where['account'] = array('like',"%{$account}%");
    	if(1 == $status){ //在线
    		$where['last_time'] = array('gt',time()-$this->_last_time );
        }else if(2 == $status){ //离线
            $where['last_time'] = array('lt',time()-$this->_last_time );
        }
        $keywords && $where['keywords'] = array('like',"%{$keywords}%");
        if($start_date && $end_date){
            $where['add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['add_time'] = array('gt',strtotime($start_date));
            $end_date && $where['add_time'] = array('lt',strtotime($end_date));
        }
        
        //分页处理
        $count = $model->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"status"=>$status,"keywords"=>$keywords,"start_date"=>$start_date,"end_date"=>$end_date,'account'=>$account));
    	//获取列表
    	$data = $model->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
    	if($data){
    		$last_time = time()-$this->_last_time; //检测最后一次登录
    		foreach($data as $k=>$v){
        		if($v['last_time'] >= $last_time){
        			$data[$k]['is_online'] = 1;
        		}else{
        			$data[$k]['is_online'] = 0;
        		}
        		$data[$k]['last_time'] = $v['last_time'] ? date('Y-m-d H:i:s',$v['last_time']) : '尚未登录'; 
        	}
    	}
    	//数据输送模板
        $this->accountLogs->addLog("查询管理员列表,查询条件：账号：{$account},名称：{$name},状态：{$status},关键词：{$keywords},创建时间最小值：{$start_date},创建最大时间：{$end_date}");
    	$this->assign('name',$name);
    	$this->assign('account',$account);
    	$this->assign('status',$status);
    	$this->assign('keywords',$keywords);
    	$this->assign('start_date',$start_date);
    	$this->assign('end_date',$end_date);
    	$this->assign('Page',$page->show());
    	$this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
    	$this->display();
    }

    public function index_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "管理员列表";
                $header = array(
                            array('name','账号名称'),
                            array('is_online','状态'),
                            array('account','账号'),
                            array('tel','电话'),
                            array('email','邮箱'),
                            array('add_time','添加时间'),
                            array('login_time','最近登录'),
                            array('last_time','最后在线'),
                        );
                foreach($data as $k=>$v){
                    $data[$k]['is_online'] = $v['is_online'] ?'在线' : '离线';
                    $data[$k]['add_time']=date("Y-m-d H:i:s",$v['add_time']);
                    $data[$k]['login_time']=date("Y-m-d H:i:s",$v['login_time']);
                }
                $path = $this->exportExcel($title,$header,$data);
                $this->ajaxReturn(array('status'=>200,'url'=>$path));
            }else{
               exit('非法请求'); 
            }
        }else{
            exit('非法请求');
        }
    }
    public function filterAuth($auths){
        foreach ($auths as $k=>$v){
            $a1=['app设备关注','app记录关注'];
            $a2=['查看关注记录列表','查看关注设备列表'];
           if(C('APP_TYPE')==1 && in_array($v['name'],$a1)){
                unset($auths[$k]);
           }
           if(C('APP_TYPE')==2 && in_array($v['name'],$a2)){
               unset($auths[$k]);
           }
        }
        return $auths;
    }
    /**
     * 添加管理员
     */
    public function add(){
    	//查找权限列表
        $auths = M('auth_list',null)->join('left join auth_group on auth_list.group_id=auth_group.id')
            ->field("auth_list.name,auth_list.id,auth_group.name as group_name,auth_list.group_id")
            ->order('auth_list.group_id desc')->select();
        $auths = $this->filterAuth($auths);
        $this->assign("auths",$auths);
        //最开始的权限组名称,即左边菜单栏的栏目名称
        $authGroupFirst='
{"\u8d26\u6237\u7ba1\u7406":"8","\u624b\u673a\u7ba1\u7406":"12","\u8bbe\u5907\u7ba1\u7406":"7","\u5ba2\u6237\u8d44\u6599":"6","app\u6570\u636e\u7edf\u8ba1":"4","\u6570\u636e\u7edf\u8ba1":"3","\u6211\u7684\u5173\u6ce8":"2","\u8bed\u97f3\u8f6c\u5199":"10","\u624b\u673a\u8bbe\u5907":"13","\u8bb0\u5f55\u67e5\u8be2":"14","\u8bbe\u5907\u72b6\u6001":"9","\u9996\u9875":"1","\u6743\u9650\u7ba1\u7406":"11"}';
        $authGroupFirstArr=json_decode($authGroupFirst,true);
        if(!$authGroupFirstArr['通讯录']){
            $authGroupFirstArr['通讯录']=$authGroupFirstArr['客户资料'];
        }
        $navList=array_column($this->first_nav,'name');
        $navListIds=[];
        foreach ($navList as $k=>$v){
            $v=strip_tags($v);
            $navListIds[]=$authGroupFirstArr[$v];
        }
        $auths1 = [];
        foreach ($auths as $v){
            if(!in_array($v['group_id'],$navListIds)){
                continue;
            }
            $v['group_name'] = $v['group_name'] ? $v['group_name']:'未分组';
            $auths1[$v['group_id']][]=$v;
        }
        //        dump($auths1);die;
        //        $this->assign("auths",$auths);
        $this->assign("auths1",$auths1);
    	//查找设备
        $devices = D("Common/Devices")->getDevices();
        $app_devices = D("Common/DevicesApp")->getDevices();
        $this->assign('account_id',$this->account_id);
        $this->assign("devices",$devices);
        $this->assign('app_type',C('APP_TYPE'));
    	$this->assign("app_devices",$app_devices);
    	$this->display();
    }

    /**
     * 保存添加管理员
     * @param ids 权限设备的id 集合
     * @param auth 权限集合
     * @param data.name 名称
     * @param data.account 账号
     * @param data.password 密码
     * @param data.apassword 重复密码
     * @param data.tel 手机号码
     * @param data.email 邮箱
     * @param data.aids 手机设备
     */
    public function doAdd(){
        if(IS_POST){
           $data = I('data');
           $ids = I('ids');
           $auth = I('auth');
           $aids = I('aids');
           //参数验证
           if(!$data){
                echo json_encode(array('code'=>300,'msg'=>'参数提交错误'));exit;
            }
            if(!$data['name'] || !$data['account'] || !$data['password'] || !$data['apassword']){
                echo json_encode(array('code'=>300,'msg'=>'缺少参数'));exit;
            }
            if($data['password'] != $data['apassword']){
                echo json_encode(array('code'=>300,'msg'=>'密码和重复密码不一致'));exit;
            }
            $r=M('accounts')->where("account = '{$data['account']}'")->find();
            //检测账号是否重复
            if(M('accounts')->where("account = '{$data['account']}'")->find()){
                echo json_encode(array('code'=>300,'msg'=>'账号已经存在'));exit;
            }

            //密码处理
            $data['password'] = md5($data['password'].$this->_passwdkey);
            unset($data['apassword']);
            $data['add_time'] = time();
            $data['upd_time'] = time();
            $data['last_time'] = time();
            $data['login_time'] = time();
            $is = M("accounts")->add($data);
            if($is){ //添加账号成功 处理权限和设备权限
                //权限处理  
                if($auth){
                    $auth = substr($auth,0,strlen($auth)-1);
                    $auth = explode(',',$auth);
                    foreach($auth as $k=>$v){
                        //判断上传的规则是否有效
                        if(!M('auth_list')->find($v)){
                            continue; //不是有效的规则就跳过
                        }
                        $auth1[$k]['account_id'] = $is;
                        $auth1[$k]['auth_id'] = $v;
                    }
                    if(!empty($auth1)){
                        M("accounts_auth")->addall($auth1);
                    }
                }
                M('account_purview_line')->where(['account_id'=>(-$this->account_id)])
                    ->save(['account_id'=>$is]);
                M('account_purview')->where(['account_id'=>(-$this->account_id)])
                    ->save(['account_id'=>$is]);
                //设备权限处理
//                if($ids){
//                    //处理字符串
//                        $ids = substr($ids,0,strlen($ids)-1);
//                    $ids= M("account_purview",null)->field('device_id')->where(['account_id'=>-$this->account_id])->select();
//                    $ids=array_column($ids,'device_id');
//                    $gids = M("devices")->where(['id'=>['in',$ids]])->field("id as device_id,group_id")->select();
//                    foreach($gids as $k=>$v){
//                        $gids[$k]['account_id'] = $is;
//                        $gids[$k]['attention'] = 0;
//                        $gids[$k]['add_time'] = date("Y-m-d H:i:s",time());
//                        $gids[$k]['upd_time'] = date("Y-m-d H:i:s",time());
//                        unset($gids[$k]['id']);
//                    }
//                    M("account_purview")->addAll($gids);
//                }
                if(1||$ids){
                    //处理字符串
                    $ids= M("account_purview",null)->field('device_id')->where(['account_id'=>-$this->account_id])->select();
                    $ids=array_column($ids,'device_id');
                    $gids=[];
                    if($ids){
                        $gids = M("devices")->where(['id'=>['in',$ids]])->field("id as device_id,group_id")->select();
                    }
                    foreach($gids as $k=>$v){
                        $gids[$k]['account_id'] = $is;
                        $gids[$k]['attention'] = 0;
                        $gids[$k]['add_time'] = date("Y-m-d H:i:s",time());
                        $gids[$k]['upd_time'] = date("Y-m-d H:i:s",time());
                        unset($gids[$k]['id']);
                    }
                    M("account_purview",null)->where(['account_id'=>-$this->account_id])->save(['account_id'=>$is]);
                    M('account_purview_line',null)->where(['account_id'=>-$this->account_id])->save(['account_id'=>$is]);
                }
                if($aids){
                    $aids = substr($ids,0,strlen($aids)-1);
                    $agids = M("devices_app")->where("id in ({$aids})")->field("id as device_id,group_id")->select();
                    foreach($agids as $k=>$v){
                        $agids[$k]['account_id'] = $is;
                        $agids[$k]['attention'] = 0;
                        $agids[$k]['add_time'] = date("Y-m-d H:i:s",time());
                        $agids[$k]['upd_time'] = date("Y-m-d H:i:s",time());
                        unset($gids[$k]['id']);
                    }
                    M("account_app_purview")->addAll($gids);
                }
                $this->accountLogs->addLog("添加管理员,管理员id：{$is}");
                echo json_encode(array('code'=>200,'msg'=>'添加账号成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'添加账号失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }
    
    /**
     * 编辑管理员
     * @param id 
     */
    public function edit(){
        $id = I('id','int');
        if(!$id){
            $this->error('请选择要编辑的账号');
        }
        $account = D("Common/accounts")->find($id);
        if(!$account){
            $this->error('管理员不存在');
        }
        //查找权限列表
        $auths = M('auth_list',null)->join('left join auth_group on auth_list.group_id=auth_group.id')
            ->field("auth_list.name,auth_list.id,auth_group.name as group_name,auth_list.group_id")
            ->order('auth_list.group_id desc')->select();
        $auths = $this->filterAuth($auths);

        //查找账号权限
        $a_auths = M("accounts_auth")->where("account_id = {$id}")->getField("auth_id",true);


        //判断是否拥有权限
        if($a_auths){
            foreach($auths as $k=>$v){
                if(in_array($v['id'],$a_auths)){
                    $auths[$k]['is'] = 1;
                }else{
                    $auths[$k]['is'] = 0;
                }
            }
        }
        $auths1 = [];
        //最开始的权限组名称,即左边菜单栏的栏目名称
//        $authGroupFirst='
//{"\u8d26\u6237\u7ba1\u7406":"8","\u624b\u673a\u7ba1\u7406":"12","\u8bbe\u5907\u7ba1\u7406":"7","\u5ba2\u6237\u8d44\u6599":"6","app\u6570\u636e\u7edf\u8ba1":"4","\u6570\u636e\u7edf\u8ba1":"3","\u6211\u7684\u5173\u6ce8":"2","\u8bed\u97f3\u8f6c\u5199":"10","\u624b\u673a\u8bbe\u5907":"13","\u8bb0\u5f55\u67e5\u8be2":"14","\u8bbe\u5907\u72b6\u6001":"9","\u9996\u9875":"1","\u6743\u9650\u7ba1\u7406":"11"}';

        $authGroupFirst=' {"\u8D26\u6237\u7BA1\u7406":"8","\u624B\u673A\u7BA1\u7406":"12","\u8BBE\u5907\u7BA1\u7406":"7","\u5BA2\u6237\u8D44\u6599":"6","app\u6570\u636E\u7EDF\u8BA1":"4","\u6570\u636E\u7EDF\u8BA1":"3","\u6211\u7684\u5173\u6CE8":"2","\u8BED\u97F3\u8F6C\u5199":"10","\u624B\u673A\u8BBE\u5907":"13","\u8BB0\u5F55\u67E5\u8BE2":"14","\u8BBE\u5907\u72B6\u6001":"9","\u9996\u9875":"1","\u6743\u9650\u7BA1\u7406":"11","\u62E8\u53F7\u7BA1\u7406":"15"}';

        $authGroupFirstArr=json_decode($authGroupFirst,true);
        if(!$authGroupFirstArr['通讯录']){
            $authGroupFirstArr['通讯录']=$authGroupFirstArr['客户资料'];
        }
        $navList=array_column($this->first_nav,'name');




        $navListIds=[];
        foreach ($navList as $k=>$v){
            $v=strip_tags($v);
            $navListIds[]=$authGroupFirstArr[$v];
        }
        foreach ($auths as $v){
            if(!in_array($v['group_id'],$navListIds)){
                continue;
            }
            $v['group_name'] = $v['group_name'] ? $v['group_name']:'未分组';
            $auths1[$v['group_id']][]=$v;
        }



//        $this->assign("auths",$auths);
        $this->assign("auths1",$auths1);

        //查找设备
        $devices = D("Common/Devices")->getDevices();
        //查找已有权限的设备
        $dids = M("account_purview")->where("account_id = {$id}")->getField("device_id",true);
        //判断是否有权限
        if($dids){
             foreach($devices as $k=>$v){
                foreach($v['devices'] as $m=>$n){
                    if(in_array($n['id'],$dids)){
                        $devices[$k]['devices'][$m]['is'] = 1;
                    }else{
                        $devices[$k]['devices'][$m]['is'] = 0;
                    }
                }
            }
        }

        //手机设备
        $app_devices = D("Common/DevicesApp")->getDevices();
        $adids = M("account_app_purview")->where("account_id = {$id}")->getField("device_id",true);
        if($adids){
             foreach($app_devices as $k=>$v){
                foreach($v['devices'] as $m=>$n){
                    if(in_array($n['id'],$adids)){
                        $app_devices[$k]['devices'][$m]['is'] = 1;
                    }else{
                        $app_devices[$k]['devices'][$m]['is'] = 0;
                    }
                }
            }
        }
        $groups = D('Common/DeviceGroup')->getOption(); //设备组
        $purviews = D('Common/AccountPurview')->getData0($this->account['id']);
        $this->assign("purviews",$purviews);
        $this->assign("groups",$groups);
        $this->assign("devices",$devices);
        $this->assign("app_devices",$app_devices);
        $this->assign('app_type',C('APP_TYPE'));
        $this->assign('data',$account);
        $this->display();
    }
    public function deviceAuth(){
        $id = I('id','int');
        $groups = D('Common/DeviceGroup')->getOption(); //设备组
        if($id>0){
            $purviews = D('Common/AccountPurview')->getData0($id);
        }else{
            M('account_purview_line')->where(['account_id'=>$id])->delete();
            M('account_purview')->where(['account_id'=>$id])->delete();
            $purviews=[];
        }
        $this->assign("purviews",$purviews);
        $this->assign("groups",$groups);
        $this->assign('data',['id'=>$id]);
        $this->display('deviceAuth');
    }
    public function saveDeviceAuth(){
        $ids = I('device_ids');
        $data = I('data');
        if(I('is_add')){
            file_put_contents(SITE_PATH . '/data/runtime/lineAuth' . $this->account_id . '.log',json_encode(I("")));
        }
        //设备权限处理
        if($ids){
            //处理字符串
//            $ids = substr($ids,0,strlen($ids)-1);
            $gids = M("devices")->where(['id'=>['in',$ids]])->field("id as device_id,group_id")->select();
            foreach($gids as $k=>$v){
                $gids[$k]['account_id'] = $data['id'];
                $gids[$k]['attention'] = 0;
                $gids[$k]['add_time'] = date("Y-m-d H:i:s",time());
                $gids[$k]['upd_time'] = date("Y-m-d H:i:s",time());
                unset($gids[$k]['id']);
            }
            M("account_purview")->where(['account_id'=>$data['id']])->delete();
            M("account_purview")->addAll($gids);
            $line_ids = I('line_ids');
            if ($line_ids){
                M('account_purview_line',null)->where(['account_id'=>$data['id']])->delete();
                $lineData = [];
                $add_time = date('Y-m-d H:i:s');
                foreach ($line_ids as $key=>$val){
                    foreach ($val as $k=>$v){
                        $lineData[]=[
                            'account_id' => $data['id'],
                            'device_id' => $key,
                            'line_id'=>$v,
                            'add_time'=>$add_time
                        ];
                    }
                }
                M('account_purview_line',null)->addAll($lineData);
            }
        }
        echo json_encode(array(
            'status'=>200,
            'closePage'=>1,
            'msg' =>'操作成功!',
        ));
    }
    /**
     * 账号编辑 
     * @param ids 权限设备的id 集合
     * @param auth 权限集合
     * @param data.name 名称
     * @param data.account 账号
     * @param data.password 密码
     * @param data.apassword 重复密码
     * @param data.tel 手机号码
     * @param data.email 邮箱
     * @param data.aids 
     */
    public function doEdit(){
        if(IS_POST){
            $data = I('data');
            $ids = I('ids');
            $auth = I('auth');
            $aids = I('aids');
            if(!$data){
                echo json_encode(array('code'=>300,'msg'=>'参数提交错误'));exit;
            }
            if(!$data['name'] || !$data['account'] || !$data['id']){
                echo json_encode(array('code'=>300,'msg'=>'缺少参数'));exit;
            }
            if($data['password']){ //如果修改密码
                if($data['password'] != $data['apassword']){
                    echo json_encode(array('code'=>300,'msg'=>'密码和重复密码不一致'));exit;
                }
                //密码处理
                $data['password'] = md5($data['password'].$this->_passwdkey);
            }
           
            //检测账号是否存在
            if(!M('accounts')->where("account = '{$data['account']}'")->find()){
                echo json_encode(array('code'=>300,'msg'=>'账号不存在'));exit;
            }
            
            unset($data['apassword']);
            //如果没有修改密码 unset吊
            if(!$data['password']){
                unset($data['password']);
            }
            $data['upd_time'] = time();
            $is = M("accounts")->where("id = {$data['id']}")->save($data);
                //保存成功 修改auth 和 devices 权限
                if($auth){
                    //处理auth
                    $auth = substr($auth,0,strlen($auth)-1);
                    $auth = explode(',',$auth);
                    foreach($auth as $k=>$v){
                        //判断上传的规则是否有效
                        if(!M('auth_list')->find($v)){
                            continue; //不是有效的规则就跳过
                        }
                        $auth1[$k]['account_id'] = $data['id'];
                        $auth1[$k]['auth_id'] = $v;
                    }
                    if(!empty($auth1)){
                        M("accounts_auth")->where("account_id = {$data['id']}")->delete();
                        M("accounts_auth")->addall($auth1);
                    }
                }

                //设备权限处理
                if($ids){
                    //处理字符串
                    $ids = substr($ids,0,strlen($ids)-1);
                    $gids = M("devices")->where("id in ({$ids})")->field("id as device_id,group_id")->select();
                    foreach($gids as $k=>$v){
                        $gids[$k]['account_id'] = $data['id'];
                        $gids[$k]['attention'] = 0;
                        $gids[$k]['add_time'] = date("Y-m-d H:i:s",time());
                        $gids[$k]['upd_time'] = date("Y-m-d H:i:s",time());
                        unset($gids[$k]['id']);
                    }
                    M("account_purview")->where("account_id = {$data['id']}")->delete();
                    M("account_purview")->addAll($gids);
                    $line_ids = I('requets.line_ids');
                    if ($line_ids){
                        M('account_purview_line',null)->where(['account_id'=>$data['id']])->delete();
                        $lineData = [];
                        $add_time = date('Y-m-d H:i:s');
                        $lineRecords = M('device_line',null)->where(['id'=>['in',$line_ids]])->select();
                        foreach ($lineRecords as $v){
                            $lineData[] = [
                                'account_id' => $data['id'],
                                'device_id' => $v['device_id'],
                                'line_id'=>$v['id'],
                                'add_time'=>$add_time
                            ];
                        }
                        M('account_purview_line',null)->addAll($lineData);
                    }
                    
                }
                if($aids){
                    $aids = substr($aids,0,strlen($aids)-1);
                    $agids = M("devices_app")->where("id in ({$aids})")->field("id as device_id,group_id")->select();
                    foreach($agids as $k=>$v){
                        $agids[$k]['account_id'] = $data['id'];
                        $agids[$k]['attention'] = 0;
                        $agids[$k]['add_time'] = time();
                        $agids[$k]['upd_time'] = time();
                        unset($gids[$k]['id']);
                    }
                    M("account_app_purview")->where("account_id = {$data['id']}")->delete();
                    M("account_app_purview")->addAll($agids);
                }
                $this->accountLogs->addLog("编辑管理员,管理员id：{$data['id']}");

            $_SESSION['ACCOUNTS']['tel'] = $data['tel'];


                echo json_encode(array('code'=>200,'msg'=>'编辑账号成功'));exit;
            
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 删除管理员
     * @param id 管理员id
     */
    public function delete(){
        $id = I('id','','int');
        if(!$id){
            $this->error("要删除的管理员不存在！");
        }
        $admin = M("accounts")->find($id);
        if(!$admin){
            $this->error("要删除的管理员不存在！");
        }
        //进行删除操作
        //删除操作日志 删除登录信息 删除关注设备和记录 删除关注手机和记录 删除账号 添加操作日志
        M("account_app_purview")->where("account_id = {$id}")->delete();
        M("account_login")->where("account_id = {$id}")->delete();
        M("account_logs")->where("account_id = {$id}")->delete();
        M("account_purview")->where("account_id ={$id}")->delete();
        M("account_purview_line")->where("account_id ={$id}")->delete();
        M("accounts_auth")->where("account_id = {$id}")->delete();
        if(M("accounts")->delete($id)){
            $this->accountLogs->addLog("删除管理员 {$admin['name']}");
            $this->redirect("Admins/index",'',3,'删除管理员成功');
        }else{
            $this->error("删除管理员失败");
        }
    }

    /**
     * 日志列表
     * @param content 操作内容
     * @param ip 操作地点ip
     * @param start_date 操作时间最小值
     * @param end_date 操作时间最大值
     * @param id 管理员id
     */
    public function logs(){
        $content = I('content');
        $ip = I('ip');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p') ? I('p') : 1;
        $id = I('id','int');
        $limit = I('limit') ? I('limit') : 20;

        //where条件处理
        $where = array();
        $content && $where['content'] = array('like',"%{$content}%");
        $ip && $where['ip'] = $ip;
        if($start_date && $end_date){
            $where['utime'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['utime'] = array('gt',strtotime($start_date));
            $end_date && $where['utime'] = array('lt',strtotime($end_date)); 
        }
        
        $id && $where['account_id'] = $id;
        
        //分页处理
        $count = M("account_logs")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"content"=>$content,"ip"=>$ip,"start_date"=>$start_date,"end_date"=>$end_date,"id"=>$id));
        //查询
        $data = M("account_logs")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        $this->accountLogs->addLog("查询日志，管理员id：{$id},操作内容：{$content},ip：{$ip},记录日志最小时间：{$start_date},记录日志最大时间：{$end_date}");
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('id',$id);
        $this->assign('ip',$ip);
        $this->assign('content',$content);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->display();
    }

    public function logs_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "管理员日志列表";
                $header = array(
                            array('content','日志内容'),
                            array('ip','ip地址'),
                            array('add_time','操作时间'),
                        );
                foreach($data as $k=>$v){
                    $data[$k]['add_time'] = date("Y-m-d H:i:s",$v['utime']);
                }
                $path = $this->exportExcel($title,$header,$data);
                $this->ajaxReturn(array('status'=>200,'url'=>$path));
            }else{
               exit('非法请求'); 
            }
        }else{
            exit('非法请求');
        }
    }

    /**
     * 删除日志记录
     * @param id 日志id
     * @param aid 管理员id
     */
    public function deleteLog(){
        $id = I('id','int');
        $aid = I('aid','int');
        if(!$id){
            $this->error("请选择要删除的日志");
        }
        if(M("account_logs")->delete($id)){
            $this->accountLogs->addLog("删除日志，日志id：{$id}");
            $this->redirect("Admins/logs",array('id'=>$aid),3,'删除日志成功');
        }else{
            $this->error("删除日志失败");
        }
    }


    /**
     * 编辑管理员
     * @param id
     */
    public function edits(){
        $id = I('id','int');
        if(!$id){
            $this->error('请选择要编辑的账号');
        }
        $account = D("Common/accounts")->find($id);
        if(!$account){
            $this->error('管理员不存在');
        }
        //查找权限列表
        $auths = M('auth_list',null)->join('left join auth_group on auth_list.group_id=auth_group.id')
            ->field("auth_list.name,auth_list.id,auth_group.name as group_name,auth_list.group_id")
            ->order('auth_list.group_id desc')->select();
        $auths = $this->filterAuth($auths);
        //查找账号权限
        $a_auths = M("accounts_auth")->where("account_id = {$id}")->getField("auth_id",true);
        //判断是否拥有权限
        if($a_auths){
            foreach($auths as $k=>$v){
                if(in_array($v['id'],$a_auths)){
                    $auths[$k]['is'] = 1;
                }else{
                    $auths[$k]['is'] = 0;
                }
            }
        }
        $auths1 = [];
        //最开始的权限组名称,即左边菜单栏的栏目名称
        $authGroupFirst='
{"\u8d26\u6237\u7ba1\u7406":"8","\u624b\u673a\u7ba1\u7406":"12","\u8bbe\u5907\u7ba1\u7406":"7","\u5ba2\u6237\u8d44\u6599":"6","app\u6570\u636e\u7edf\u8ba1":"4","\u6570\u636e\u7edf\u8ba1":"3","\u6211\u7684\u5173\u6ce8":"2","\u8bed\u97f3\u8f6c\u5199":"10","\u624b\u673a\u8bbe\u5907":"13","\u8bb0\u5f55\u67e5\u8be2":"14","\u8bbe\u5907\u72b6\u6001":"9","\u9996\u9875":"1","\u6743\u9650\u7ba1\u7406":"11"}';
        $authGroupFirstArr=json_decode($authGroupFirst,true);
        if(!$authGroupFirstArr['通讯录']){
            $authGroupFirstArr['通讯录']=$authGroupFirstArr['客户资料'];
        }
        $navList=array_column($this->first_nav,'name');
        $navListIds=[];
        foreach ($navList as $k=>$v){
            $v=strip_tags($v);
            $navListIds[]=$authGroupFirstArr[$v];
        }
        foreach ($auths as $v){
            if(!in_array($v['group_id'],$navListIds)){
                continue;
            }
            $v['group_name'] = $v['group_name'] ? $v['group_name']:'未分组';
            $auths1[$v['group_id']][]=$v;
        }
//        dump($auths1);die;
//        $this->assign("auths",$auths);
        $this->assign("auths1",$auths1);

        //查找设备
        $devices = D("Common/Devices")->getDevices();
        //查找已有权限的设备
        $dids = M("account_purview")->where("account_id = {$id}")->getField("device_id",true);
        //判断是否有权限
        if($dids){
            foreach($devices as $k=>$v){
                foreach($v['devices'] as $m=>$n){
                    if(in_array($n['id'],$dids)){
                        $devices[$k]['devices'][$m]['is'] = 1;
                    }else{
                        $devices[$k]['devices'][$m]['is'] = 0;
                    }
                }
            }
        }

        //手机设备
        $app_devices = D("Common/DevicesApp")->getDevices();
        $adids = M("account_app_purview")->where("account_id = {$id}")->getField("device_id",true);
        if($adids){
            foreach($app_devices as $k=>$v){
                foreach($v['devices'] as $m=>$n){
                    if(in_array($n['id'],$adids)){
                        $app_devices[$k]['devices'][$m]['is'] = 1;
                    }else{
                        $app_devices[$k]['devices'][$m]['is'] = 0;
                    }
                }
            }
        }
        $groups = D('Common/DeviceGroup')->getOption(); //设备组
        $purviews = D('Common/AccountPurview')->getData0($this->account['id']);
        $this->assign("purviews",$purviews);
        $this->assign("groups",$groups);
        $this->assign("devices",$devices);
        $this->assign("app_devices",$app_devices);
        $this->assign('app_type',C('APP_TYPE'));
        $this->assign('data',$account);
        $this->display();
    }

}