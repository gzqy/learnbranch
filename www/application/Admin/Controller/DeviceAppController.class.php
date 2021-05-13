<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use Common\Utils\DirUtil;
/**
 * 手机设备控制器
 */
class DeviceAppController extends ShopbaseController {
    public $_last_time = 600;//账号是否在线时间判断
    protected $nav_id = 12;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/DeviceApp/index'),'name'=>'设备管理'), 
            array('id'=>2,'a'=>U('/DeviceApp/GroupList'),'name'=>'设备组管理'), 
        ); 
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }

    /**
     * 注册设备列表
     * @param name 设备名称
     * @param code 设备编码
     * @param group_id 设备组
     * @param keywords 关键字
     * @param start_date 注册时间最小值
     * @param end_date 注册时间最大值
     * @param p 分页当前页 默认为1
     * @param limit 分页页数 默认20
     */
    public function index(){
        $name = I('name');
        $code = I('code');
        $group_id = I('group_id');
        $keywords = I('keywords');
        $start_date = strtotime(I('start_date'));
        $end_date = strtotime(I('end_date'));
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;
        //where条件处理
        $where['a.registered'] = '1';
        $where['a.closed'] = '0';
        $name && $where['a.name'] = array('like',"%{$name}%");
        $code && $where['a.code'] = array('like',"%{$code}%");
        $keywords && $where['a.keywords'] = array('like',"%{keywords}%");
        $group_id && $where['a.group_id'] = $group_id;
        if($start_date && $end_date){
            $where['a.add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['a.add_time'] = array('gt',$start_date);
            $end_date && $where['a.add_time'] = array('lt',$end_date);
        }
        
        //分页处理
        $count = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
		//print_r(M()->_sql());
        //查询列表   
        $data = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->order("a.id desc")->field("a.*,b.name as gname,c.comeing,c.outgoing,c.missed,c.last_time")->limit($page->firstRow.','.$page->listRows)->select();

        $last_time = time() - $this->_last_time;//是否在线时间标准
        foreach ($data as $k=>$v){
            if($v['last_time'] >= $last_time){
                $data[$k]['status'] = 1;
            }else{
                $data[$k]['status'] = 0;
            }
        }

        //查询设备组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询app设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('group_id',$group_id);
        $this->assign('keywords',$keywords);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',1);
    
        $this->display();
    }

    public function index_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备管理列表";
                $header = array(
                            array('name','设备名称'),
                            array('code','电话号码'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','注册时间'),
                        );
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
     * 未注册设备列表
     * @param name 设备名称
     * @param code 设备编码
     * @param group_id 设备组
     * @param keywords 关键字
     * @param start_date 注册时间最小值
     * @param end_date 注册时间最大值
     * @param p 分页当前页 默认为1
     * @param limit 分页页数 默认20
     */
    public function NregIndex(){
        $name = I('name');
        $code = I('code');
        $group_id = I('group_id');
        $keywords = I('keywords');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;

        //where条件处理
        $where['a.registered'] = '0';
        $where['a.closed'] = '0';
        $name && $where['a.name'] = array('like',"%{$name}%");
        $code && $where['a.code'] = array('like',"%{$code}%");
        $keywords && $where['a.keywords'] = array('like',"%{keywords}%");
        $group_id && $where['a.group_id'] = $group_id;
        if($start_date && $end_date){
            $where['a.add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['a.add_time'] = array('gt',$start_date);
            $end_date && $where['a.add_time'] = array('lt',$end_date);
        }
        //分页处理
        $count = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
        //查询列表   
        $data = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->order("a.id desc")->field("a.*,b.name as gname,c.comeing,c.outgoing,c.missed")->limit($page->firstRow.','.$page->listRows)->select();
        //查询设备组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询未注册手机设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('group_id',$group_id);
        $this->assign('keywords',$keywords);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',1);
        $this->display();
    }

    public function NregIndex_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "未注册app设备列表";
                $header = array(
                            array('name','设备名称'),
                            array('code','电话号码'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','注册时间'),
                        );
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
     * 删除设备列表
     * @param name 设备名称
     * @param code 设备编码
     * @param group_id 设备组
     * @param keywords 关键字
     * @param start_date 注册时间最小值
     * @param end_date 注册时间最大值
     * @param p 分页当前页 默认为1
     * @param limit 分页页数 默认20
     */
    public function closeList(){
        $name = I('name');
        $code = I('code');
        $group_id = I('group_id');
        $keywords = I('keywords');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;
        //where条件处理
        $where['a.closed'] = '1';
        $name && $where['a.name'] = array('like',"%{$name}%");
        $code && $where['a.code'] = array('like',"%{$code}%");
        $keywords && $where['a.keywords'] = array('like',"%{keywords}%");
        $group_id && $where['a.group_id'] = $group_id;
        if($start_date && $end_date){
            $where['a.add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['a.add_time'] = array('gt',$start_date);
            $end_date && $where['a.add_time'] = array('lt',$end_date);
        }
        
        //分页处理
        $count = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
        //查询列表   
        $data = M("devices_app")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_app_stat c on a.id = c.device_id")->where($where)->order("a.id desc")->field("a.*,b.name as gname,c.comeing,c.outgoing,c.missed")->limit($page->firstRow.','.$page->listRows)->select();
        //查询设备组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询app设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('group_id',$group_id);
        $this->assign('keywords',$keywords);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',1);
        $this->display();
    }

    /**
     * 设备添加或者设备编辑注册
     * @param id 设备id 
     */
    public function add(){
        $id = I("id");
        //查询设备组
        $groups = D("Common/DeviceGroup")->getOption();
        if($id){ //编辑或者注册设备
            //获取设备的相关信息
            $device = M("devices_app")->find($id);
            if(!$device){
                $this->error('设备不存在');
            }
            $this->assign('device',$device);
            $this->assign('title',2);
        }else{ //添加设备
            $this->assign('title',1);
        }
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',1);
        $this->display();
    }

    /**
     * 编辑、添加设备保存
     * @param id 设备id
     * @param name 设备名称
     * @param line 设备端口
     * @param group_id 设备分组
     */
    public function save(){
        if(IS_AJAX){
            $data = I('data');
            //参数检测 
            if(!$data['name'] || !$data['code'] || !$data['group_id']){
                echo json_encode(array('code'=>300,'msg'=>'请填写必要参数'));exit;
            }
            //判断是编辑还是新增
            if($data['id']){ //编辑设备
                //检测设备是否存在
                if(!M("devices_app")->find($data['id'])){
                    echo json_encode(array('code'=>300,'msg'=>'设备不存在'));exit;
                }
                //修改设备相关信息
                $data['keywords'] = $data['name'].','.$data['code'];//关键字处理
                $data['upd_time'] = time();
                $data['registered'] = 1;//编辑的设备注册
                $data['closed'] = 0;
                if(M("devices_app")->save($data)){
                    //编辑成功 
                    //处理权限设备分组
                    M("account_app_purview")->where("device_id = {$data['id']}")->save(array("group_id"=>$data['group_id']));
                    $this->accountLogs->addLog("编辑手机设备,设备id：{$data['id']}");
                    echo json_encode(array('code'=>200,'msg'=>'设备编辑成功'));exit;
                }else{
                     echo json_encode(array('code'=>300,'msg'=>'设备编辑失败'));exit;
                }

            }else{ //添加设备
                //检测设备是否已经存在
                if(M("devices_app")->where("code= {$data['code']}")->find()){
                    echo json_encode(array('code'=>300,'msg'=>'设备已存在'));exit;
                }
                //进行设备添加
                unset($data['id']);
                //进行数据处理
                $data['registered'] = 0;//新增设备都是未注册的
                $data['closed'] = 0;
                $data['add_time'] = time();
                $data['upd_time'] = time();
                $data['keywords'] = $data['name'].','.$data['code'];//关键字处理
                $id = M("devices_app")->add($data);
                if($id){
                    //添加成功后 
                    //清除设备权限表 防止于遗留数据
                    M("account_app_purview")->where("device_id = {$id}")->delete();
                    //device_stat处理
                    M("device_app_stat")->add(array('device_id'=>$id));
                    $this->accountLogs->addLog("添加设备,设备id：{$id}");
                    echo json_encode(array('code'=>200,'msg'=>'添加设备成功'));exit;
                }else{ //添加设备失败
                    echo json_encode(array('code'=>300,'msg'=>'添加设备失败'));exit;
                }
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 删除设备
     * @param id 设备id
     */
    public function Remove(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'没有选择要删除的设备'));exit;
            }
            //检测设备是否存在
            $device = M("devices_app")->find($id);
            if(!$device){
                echo json_encode(array('code'=>300,'msg'=>'设备不存在'));exit;
            }
            //删除设备 不能从数据库删除 因为其他很多表 存在依赖关系 全都删除 会出现数据断档
            $data = array();
            $data['group_id'] = 0;
            $data['closed'] = 1;
            if(M("devices_app")->where("id = {$id}")->save($data)){
                //成功后 处理部分数据
                M("account_app_purview")->where("device_id = {$id}")->delete();
                $this->accountLogs->addLog("删除手机设备,设备id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'删除设备成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除设备失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 彻底删除手机设备
     * @param id 手机设备id
     */
    public function DeviceAppDelte($id){
        if(IS_AJAX){
            $id = I('id','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'没有选择要删除的设备'));exit;
            }
            //检测设备是否存在
            $device = M("devices_app")->find($id);
            if(!$device){
                echo json_encode(array('code'=>300,'msg'=>'设备不存在'));exit;
            }
            if(1 != $device['closed']){
                echo json_encode(array('code'=>300,'msg'=>'该设备不是删除设备，无需恢复'));exit;
            }
            /**
             * 删除设备需要删除：设备详情表 设备端口表 设备事件表 管理员设备权限表 管理员设备端口权限表
             *  设备记录表 设备、记录关注表 设备对应音频记录
             */
            
            if(M("devices_app")->where("id = {$id}")->delete()){
                M("account_app_purview")->where("device_id = {$id}")->delete();
                M("device_app_call")->where("device_id = {$id}")->delete();
                M("contacts")->where("app_code = '{$device['code']}'")->delete();
                M("device_app_call_data")->where("device_id = {$id}")->delete();
                M("device_app_call_flag")->where("device_id = {$id}")->delete();
                M("device_app_stat")->where("device_id = {$id}")->delete();
                $path = UPLOAD_PATH.'/'.$device['code'];
                DirUtil::deldir($path);
                $this->accountLogs->addLog("删除设备,设备id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'删除设备成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除设备失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 批量删除设备
     * @param ids 设备id集合
     */
    public function deleteAllDevice(){
        if(IS_POST){
            $ids = I('ids');
            if(!$ids){
                echo json_encode(array('code'=>300,'msg'=>'请选择要删除的设备'));exit;
            }
            foreach($ids as $k=>$id){
                if(!$id){
                    continue;
                }
                //检测设备是否存在
                $device = M("devices_app")->find($id);
                if(!$device){
                    continue;
                }
                //删除设备 不能从数据库删除 因为其他很多表 存在依赖关系 全都删除 会出现数据断档
                $data = array();
                $data['group_id'] = 0;
                $data['closed'] = 1;
                if(M("devices_app")->where("id = {$id}")->save($data)){
                    //成功后 处理部分数据
                    M("account_app_purview")->where("device_id = {$id}")->delete();
                }else{
                    continue;
                }
            }
            $dids = implode($ids,',');
            $this->accountLogs->addLog("批量删除手机设备,设备id集合：{$dids}");
            echo json_encode(array('code'=>200,'msg'=>'批量删除设备成功'));exit;
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
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
        $count = M("device_group")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"add_time_min"=>$add_time_min,"add_time_max"=>$add_time_max,"upd_time_min"=>$upd_time_min,"upd_time_max"=>$upd_time_max));
        //查询列表
        $data = M("device_group")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        //统计查询每个分组的设备数量
        foreach($data as $k=>$v){
            $data[$k]['num'] = M("devices")->where("group_id = {$v['id']} and closed = 0 and registered = 1")->count();
        }
        $this->accountLogs->addLog("查询设备组列表，查询条件：分组名称{$name},最小添加时间：{$add_time_min},最大添加时间：{$add_time_max},最近修改时间：{$upd_time_max},最远修改时间：{$upd_time_min}");
        $this->assign('name',$name);
        $this->assign('add_time_min',$add_time_min);
        $this->assign('add_time_max',$add_time_max);
        $this->assign('upd_time_min',$upd_time_min);
        $this->assign('upd_time_max',$upd_time_max);
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('Page',$page->show());
        $this->assign('second_nav_id',2);
        $this->display();
    }

    public function GroupList_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "设备组列表";
                $header = array(
                            array('name','分组名称'),
                            array('num','设备数量'),
                            array('add_time','创建时间'),
                            array('upd_time','更新时间'),
                        );
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
     * 添加、编辑分组
     * @param id 分组id 
     */
    public function addGroup(){
        $id = I('id');
        if($id){ //编辑分组
            $group = M("device_group")->find($id);
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

    /**
     * 保存编辑添加分组
     * @param id 分组id
     * @param name 分组名称
     */
    public function saveGroup(){
        if(IS_AJAX){
            $data = I('data');
            if($data['id']){ //编辑
                if(!M('device_group')->find($data['id'])){
                     echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
                }
                $data['upd_time'] = date("Y-m-d H:i:s",time());
                if(M("device_group")->save($data)){
                    $this->accountLogs->addLog("修改分组,分组id：{$data['id']}");
                     echo json_encode(array('code'=>200,'msg'=>'编辑分组成功'));exit;
                }else{
                     echo json_encode(array('code'=>200,'msg'=>'编辑分组失败'));exit;
                }
            }else{ //添加
                if(M("device_group")->where(array("name"=>$data['name']))->find()){
                     echo json_encode(array('code'=>200,'msg'=>'分组已经存在'));exit;
                }else{
                    //添加分组
                    $data['add_time'] = date("Y-m-d H:i:s",time());
                    $data['upd_time'] = date("Y-m-d H:i:s",time());
                    if($id = M("device_group")->add($data)){
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
            if(!M("device_group")->find($id)){
                echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
            }
            //进行删除
            //首先将本分组下的设备分组更换为0
            M("devices")->where("group_id = {$id}")->save(array("group_id"=>0));
            //修改设备权限表中的分组为0
            M("account_purview")->where("group_id = {$id}")->save(array("group_id"=>0));
            //删除分组
            if(M("device_group")->delete($id)){
                $this->accountLogs->addLog("删除设备分组,分组id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'删除分组成功'));exit; 
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除分组失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 批量删除设备组
     * @param ids 设备组id集合
     */
    function doGroupRemoves(){
        if(IS_POST){
            $ids = I('ids');
            if(!$ids){
                echo json_encode(array('code'=>300,'msg'=>'请选择要删除的分组'));exit;
            }
            foreach($ids as $k=>$id){
                if(!$id){
                    continue;
                }
                if(!M('device_group')->find($id)){
                    continue;
                }
                //首先将本分组下的设备分组更换为0
                M("devices")->where("group_id = {$id}")->save(array("group_id"=>0));
                //修改设备权限表中的分组为0
                M("account_purview")->where("group_id = {$id}")->save(array("group_id"=>0));
                if(!M("device_group")->delete($id)){
                    continue;
                }
            }
            $dids = implode($ids,',');
            $this->accountLogs->addLog("批量删除设备分组,分组id集合：{$dids}");
            echo json_encode(array('code'=>200,'msg'=>'批量删除分组成功'));exit; 
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 批量设备分组
     * @param group_id 分组id
     * @param ids 设备id集合   
     */
    function dosetGroup(){
        if(IS_POST){
            $group_id = I('group_id');
            $ids = I('ids');
            if(!$group_id){
                echo json_encode(array('code'=>300,'msg'=>'请选择设备组'));exit;
            }
            if(!$ids){
                echo json_encode(array('code'=>300,'msg'=>'请选择设备'));exit;
            }
            //检测设备组是否存在
            if(!M('device_group')->find($group_id)){
                 echo json_encode(array('code'=>300,'msg'=>'选择的分组不存在'));exit;
            }
            foreach($ids as $k=>$v){
                M("devices_app")->where("id = {$v}")->save(array('group_id'=>$group_id));
            }
            $dids = implode($ids,',');
            $this->accountLogs->addLog("设备批量分组,设备组id：{$group_id},设备id集合：{$dids}");
            echo json_encode(array('code'=>200,'msg'=>'批量分组成功'));exit;
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }

    }


    //-------------------------------设备状态--------------------------------------------------------------//

    /**
     * 设备权限检测
     * @param id 设备id
     */
    protected function _loadDevice($id){
        $auth = D("Common/DevicesApp")->checkDeviceAuth($id,$this->account_id);
        if(!$auth){
            $this->error('您没有该设备查看权限!');
        }
        $this->assign('this_device',$id);
    }

    /**
     * 获取设备组和权限设备
     */
    private function GetDevicesGroups() {
        //获取所有设备组
        $groups = $this->deviceGroupModel->getOption();
        //获取当前账号有权限的设备并按组分好
        $purviews = D("Common/DevicesApp")->getData($this->account_id);
        $this->assign('groups',$groups);
        $this->assign('all_purviews',$purviews); //将数据输送模板
    }
    
    /**
     * 设备状态前置方法
     */
    private function __beforeStatus($id=null){
        $this->assign('nav_id',13);
    }


    /**
     * 设备状态列表
     */
    public function status(){
        self::__beforeStatus();
        self::GetDevicesGroups();
        $group_id = I('group_id');
        $keywords = I('keywords');
        $name = I('name');
        $code = I('code');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $status = I('status');

        $data = D("Common/DevicesApp")->getDevicesList();
        $this->assign('count',$data['status']);
        $this->assign('data',$data['data']);
        $this->assign('gdata',json_encode($data));
        $this->assign('keywords',$keywords);
        $this->assign('group_id',$group_id);
        $this->assign('status',$status ? $status : 0);
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('second_nav_id',1);
        $this->assign('Page',$data['page']);
        $this->accountLogs->addLog("查看设备状态-{$con} 查询条件:设备名称 {$name},分组id {$group_id},关键字 {$keywords},设备编码 {$code},设备时间最小值 {$start_date},设备时间最大值 {$end_date}");
        $this->display();
    }

    /**
     * excel 导出设备列表
     */
    public function status_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                $data = $data['data'];
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备状态";
                $header = array(
                            array('name','设备名称'),
                            array('code','手机号码'),
                            array('attention','是否关注'),
                            array('comeing','来电数量'),
                            array('outgoing','去电数量'),
                            array('missed','未接数量'),
                            array('add_time','注册时间'),
                            array('upd_time','更新时间'),
                        );
                foreach($data as $k=>$v){
                    $data[$k]['attention'] = $v['attention'] ? '已关注' : '未关注' ;
                    $data[$k]['status'] = $v['status'] ? '在线' : '离线' ;
                    $data[$k]['comeing'] = $v['comeing'] ? $v['comeing'] : 0 ;
                    $data[$k]['outgoing'] = $v['outgoing'] ? $v['outgoing'] : 0 ;
                    $data[$k]['missed'] = $v['missed'] ? $v['missed'] : 0 ;
                    $data[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                    $data[$k]['upd_time'] = date('Y-m-d H:i:s',$v['upd_time']);
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
     * 设备记录---日志
     * @param id 设备id
     * @param start_date 查询开始时间戳
     * @param end_date 查询结束时间戳
     * @param start_call_time 最小通话时长
     * @param end_call_time 最大通话时长
     * @param max_recording 最大录音时长
     * @param min_recording 最小录音时长
     * @param search_line 设备端口号
     * @param tel 拨打/来电号码
     * @param serch_type 类型  9 去电 10 来电 11 未接 28音频 29 来电留言 50 现场视频
     * @param keywords 关键字 电话号码 分机  备注内容
     * @param sort 排序方式
     */
    public function showLogs($id=null) {
        self::__beforeStatus($id);
        self::_loadDevice($id); //设备信息检测
        $start_date = I('start_date');
        $end_date = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time = I('end_call_time');
        $max_recording = I('max_recording','','int');
        $min_recording = I('min_recording','','int');
        $tel = I('tel');
        $search_type = I('search_type');
        $keywords = I('keywords');
        $sort = I('sort');
        $id = I('id','','int');

        $data = D("Common/DevicesApp")->getCallList($id);//通话记录列表
		
        $data1 = $data['data'];
		
        //检测是否已经关注记录
        foreach($data1 as $k=>$v){
            if(M("device_app_call_flag")->where("call_id = {$v['id']} and account_id = {$this->account_id}")->find()){
                $data1[$k]['is_flag'] = 1;
            }else{
                $data1[$k]['is_flag'] = 0;
            }

            //检测是否有文件
            if(!$v['files']){
                $data1[$k]['files'] = 0;
            }else{
                $files = D("Common/DeviceCall")->getAppFileDir($v['files']).D("Common/DeviceCall")->replaceForFilename($v['files']);
                if(!is_file($files)){
                    $data1[$k]['files'] = 0;
                }else{
                    $data1[$k]['files'] = 1;
                }
            }
			 $name=M('contacts',null)->where(['app_code'=>$v['tel']])->getField('name');
			 
			 if($name){
				 $data[$k]['name']=$name;
			 }else{
				 $data[$k]['name']='';
			 }

        }
        
        $data2 = $data1;
        foreach($data2 as $k=>$v){
            foreach($v as $x=>$y){
                if('type' == $x){
                    unset($data2[$k][$x]);
                }
            }
        }

        //查询设备的端口和录音类型判断
        $content = "查询手机设备记录，设备id {$id}";
        $this->accountLogs->addLog($content);
        $start_date && $this->assign('start_date',$start_date);        
        $end_date && $this->assign('end_date',$end_date);        
        $start_call_time && $this->assign('start_call_time',$start_call_time);        
        $end_call_time && $this->assign('end_call_time',$end_call_time);        
        $search_type && $this->assign('search_type',$search_type);
        $limit=min(5000,I('limit')?(int)I('limit'):20);
        $this->assign('limit',$limit);
        $this->assign('id',$id);
        $this->assign('keywords',$keywords);
        $this->assign('tel',$tel);
        $this->assign('max_recording',$max_recording);
        $this->assign('min_recording',$min_recording);
        $this->assign('data',$data1);
        $this->assign('Page',$data['page']);
        $this->assign('gdata',json_encode($data2));  
        $this->assign('lines',$lines);      
        $this->display();
    }

     /**
     * 记录csv导出
     */
    public function logs_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备记录";
                $header = array(
                            array('type','录音类型'),
                            array('is_flag','是否关注'),
                            array('add_time','记录日期'),
                            array('call_time','录音时长'),
                            array('recording_time','通话时长'),
                            array('tel','通话号码'),
                            array('tel_name','备注名称'),
                            array('location','地址'),
                        );
                foreach($data as $k=>$v){
                    $data[$k]['is_flag'] = $v['is_flag'] ? '已关注' : '未关注' ;
                    $data[$k]['type'] = D("Common/DeviceCall")->type($v['type1']);
                    $data[$k]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
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
     * 关注设备
     * @param id 设备id
     * @param st 1为关注 0 取消关注
     */
    function setAttention(){
        if(IS_AJAX){
            $id = I('id');
            $max_attention = C('max_attention');//最大关注设备数量
            if ($max_attention ) { //检测是否关注数超出上限
                $count = M("account_app_purview")->where("account_id = {$this->account_id} and attention = 1")->count();
                if ( $count >= $max_attention ) {
                    echo json_encode(array('status'=>300,'msg'=>'最多可关注'.$max_attention.'个设备'));exit;
                }
            }
            //查找当前设备权限表
            $info = M("account_app_purview")->where(array('account_id'=>$this->account_id,'device_id'=>$id))->find();
            if ($info) { //更新数据 关注或者取消关注
                $model = M("account_app_purview");
                $model->id = $info['id'];
                $model->upd_time=time();
                $model->attention = array('exp',"1 - attention");
                $model->save();
                if(1 == $data['attention']){
                    $this->accountLogs->addLog("取消关注设备，设备id：{$id}");
                }else{
                    $this->accountLogs->addLog("关注设备，设备id：{$id}");
                }
                
                echo json_encode(array('status'=>200,'msg'=>'操作成功'));exit;
            }else{
                echo json_encode(array('status'=>300,'msg'=>'不是权限设备，无法关注'));exit;
            }
        }else{
            echo json_encode(array('status'=>300,'msg'=>'请求方式错误'));exit;
        }
    }
    
    /**
     * 关注记录（服务端每接收一次客户端请求视为一次事件，作为一条记录）
     * @param id 记录id 
     */
    function setLogAttention(){
        if(IS_AJAX){
            $id = I('id','','int');
            if(!$id || empty($id)){
                echo json_encode(array('code'=>300,'msg'=>'请求参数错误'));exit;
            }
            $maxNum = C('max_log_attention');//最大关注数量 0不限
            if($maxNum){
                $count = M("device_app_call_flag")->where("account_id = {$this->account_id}")->count();
                if($count > $maxNum){
                    echo json_encode(array('code'=>300,'msg'=>'最多可标注'.$max_attention.'个记录'));exit;
                }
            }
            //处理关注或者取消关注
            $is = M("device_app_call_flag")->where("call_id = {$id} and account_id = {$this->account_id}")->find();
            //检测记录是否存在
            $log = M("device_app_call")->where("id = {$id}")->getField('id');
            if(!$log){
                echo json_encode(array('code'=>300,'msg'=>'记录不存在'));exit;
            }
            if($is){ //取消关注
                if(M('device_app_call_flag')->where("call_id = {$id} and account_id = {$this->account_id}")->delete()){
                    $this->accountLogs->addLog("取消关注手机设备记录 记录id：{$id}");
                    echo json_encode(array('code'=>200,'msg'=>'记录取消关注成功'));exit;
                }else{  
                    echo json_encode(array('code'=>300,'msg'=>'记录取消关注失败'));exit;
                }
            }else{ //关注
                $time = time();
                //处理
                $data = array(
                    'device_id' =>$log['device_id'],
                    'account_id'=>$this->account_id,
                    'call_id'=>$id,
                    'add_time'=>$time,
                );
                if(M("device_app_call_flag")->add($data)){
                    $this->accountLogs->addLog("关注设手机备记录，记录id：{$id}");
                    echo json_encode(array('code'=>200,'msg'=>'记录关注成功'));exit;
                }else{
                    echo json_encode(array('code'=>300,'msg'=>'记录关注失败'));exit;
                }
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
        
    }

    /**
     * 录音文件播放
     * @param id 记录id
     */
    function PlayLog(){
        $id = I('id');
        $data = M("device_app_call")->field("files,device_id,call_date")->where("id = {$id}")->find();
        $data = M("device_app_call")->alias("a")
            ->join("left join devices_app b on b.id = a.device_id")
            ->where("a.id = {$id}")
            ->field("a.files,a.recording_time,a.call_time,a.call_date,a.type,a.call_date,b.name")
            ->find();
        if(!$data['files']){
            $this->error("录音文件不存在");
        }
        $data['type'] = D("Common/DeviceCall")->type($data['type']);
        $data['call_time'] = D("Common/DeviceCall")->getTime($data['call_time']);
        $data['recording_time'] = D("Common/DeviceCall")->getTime($data['recording_time']);
        //获取文件路径
        $file = D("Common/DeviceCall")->getAppFilePlayDir($data['files']);
        $file = $file.$data['files'];
        $file_type = D("Common/DeviceCall")->getFileType($data['files']);
        $fileMp3 = str_replace(['.wav','.WAV','.amr'],['.mp3','.mp3','.mp3'],$file);
        if(!file_exists($fileMp3) && (strripos($file,'.wav')!==false||strripos($file,'.amr')!==false)){
            if (strtoupper(substr(PHP_OS,0,3))==='WIN'){
                $str = "D:\sox\sox-14-4-2\sox {$file} {$fileMp3}";
            }elseif(strripos($file,'.amr')!==false){
                $str = "ffmpeg -i {$file} {$fileMp3}";
            }else{
                $str = "sox {$file} {$fileMp3}";
            }
            exec($str);
        }
        $file=$fileMp3;
        $msg = '  [设备名称: '.$data['name'].'. 记录时间:'.$data['call_date'].'. 记录文件: '.$data['files'].']';

        $this->accountLogs->addLog('播放 '.' '.$msg);

        $this->assign('file',$file);
        $this->assign('data',$data);
        $this->assign('nav_id',13);
        $this->display();
    }

    /**
     * 单个录音文件下载
     */
    function DownloadLog(){
        $id = I('id','','int');
        if ($id) {
            $data = M("device_app_call")->where("id = {$id}")->find();
            if(!$data){
                $this->error("记录不存在");
            }
            
            $_device = M("devices_app")->where("id = {$data['device_id']}")->find();

            $msg = '  [设备名称: '.$_device['name'].'. 设备端口:'.$data['line_id'].'. 记录时间:'.$data['call_date'].'. 记录文件: '.$data['files'].']';
            $this->accountLogs->addLog('下载 '.' '.$msg);
            D("Common/DeviceCall")->AppFileDownload($data['files']);
        }else{
            $this->error("记录不存在");
        }
    }

    /**
     * 批量下载录音文件
     * @param call_ids 通话记录id集合
     */
     function DownloadLogs() {
        $call_ids = I('call_ids');
        if(!$call_ids){
            $this->error("请选择要下载的");
        }
        $ids = implode($call_ids,',');
        $files = M("device_app_call")->where("id in ({$ids})")->getField('files',true);
        if(!$files){
            $this->error("没有可以下载的文件");
        }
        //实例化批量打包类
        $zip = new \ZipArchive;
        //创建临时目录
        $tmppath = DOWNLOAD_PATH;
        if(!is_dir($tmppath)){
            if(!mkdir($tmppath)){
                $this->error("创建临时目录失败");
            }
        }
        $filename  = $tmppath.'/'.date("ymdHi").'.zip';//生成文件
        $zip->open($filename, \ZipArchive::CREATE);//打开压缩包
        foreach($files as $k=>$v){
            $file1 = D("Common/DeviceCall")->getAppFileDir($v).$v;
            $zip->addFile($file1,$v);
        }
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$filename);
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        $this->accountLogs->addLog("批量下载录音文件");
    }
    public function fixDeviceAppStat(){
        if(M('device_app_stat')->where(['device_id'=>['gt',1000000]])->find()){
            echo 'fixed already,do not try again!';die;
        }
        $sql = "SELECT count(device_id) as num,device_id FROM `device_app_stat` GROUP BY device_id HAVING num>1";
        $res = (array)M('',null)->query($sql);
        if(!$res){
            return true;
        }
        $deviceIds = array_column($res,'device_id');
        $deviceIdStr = implode(",",$deviceIds);
        $query="select sum(comeing) as comeing,sum(outgoing) as outgoing,sum(missed) as missed,
        device_id,id from `device_app_stat` where device_id in ({$deviceIdStr}) GROUP BY device_id";
        $data = M('',null)->query($query);
        foreach ($data as $val){
            $id = $val['id'];
            M('device_app_stat')->where(['id'=>$id])->save($val);
            unset($val['id']);
            $deviceId=  $val['device_id'];
            $val['device_id']=1000000+$val['device_id'];
            M('device_app_stat')->where(['id'=>['NEQ',$id],'device_id'=>$deviceId])->save($val);
        }
        echo "ok";
    }
}