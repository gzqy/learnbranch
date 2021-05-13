<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use Common\Utils\DirUtil;
/**
 * 设备管理控制器
 */
class DeviceCTController extends ShopbaseController {
	protected $nav_id = 7;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/DeviceCT/index'),'name'=>'设备管理'), 
            array('id'=>2,'a'=>U('/DeviceCT/GroupList'),'name'=>'设备组管理'), 
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
    	$start_date = I('start_date');
    	$end_date = I('end_date');
    	$p = I('p') ? I('p') : 1;
    	$limit = I('limit') ? I('limit') :20;

    	//where条件处理
    	$where['a.registered'] = 1;
    	$where['a.closed'] = 0;
    	$name && $where['a.name'] = array('like',"%{$name}%");
    	$code && $where['a.code'] = array("like","%{$code}%");
    	$keywords && $where['a.keywords'] = array('like',"%{keywords}%");
    	$group_id && $where['a.group_id'] = $group_id;
        if($start_date && $end_date){
            $where['a.add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['a.add_time'] = array('gt',$start_date);
            $end_date && $where['a.add_time'] = array('lt',$end_date);
        }
    	//分页处理
        $where['d.account_id']=$this->account_id;
		$count = M("devices")->alias("a")->join("left join device_group b on a.group_id = b.id")->
  join("left join device_stat c on a.id = c.device_id")->
  join('inner join account_purview as d on d.device_id=a.id')->
  where($where)->count();
		$page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
		//查询列表
		$data = M("devices")->alias("a")->join("left join device_group b on a.group_id = b.id")->
  join("left join device_stat c on a.id = c.device_id")->
  join('inner join account_purview as d on d.device_id=a.id')->
  where($where)->order("a.group_id asc,a.name desc")->field("a.*,b.name as gname,c.comeing,c.outgoing,c.case,c.missed,c.version,c.ip")->limit($page->firstRow.','.$page->listRows)->select();
		//查询设备组
		$groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
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
                $title = "设备管理列表";
                $header = array(
                            array('name','设备名称'),
                            array('code','设备编码'),
                            array('line','设备线路'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','注册时间'),
                            array('version','软件版本'),
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
    	$where['a.registered'] = 0;
    	$where['a.closed'] = 0;
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
		$count = M("devices")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_stat c on a.id = c.device_id")->where($where)->count();
		$page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
		//查询列表 	 
		$data = M("devices")->alias("a")->join("left join device_group b on a.group_id = b.id")->join("left join device_stat c on a.id = c.device_id")->where($where)->order("a.id desc")->field("a.*,b.name as gname,c.comeing,c.outgoing,c.case,c.missed,c.version")->limit($page->firstRow.','.$page->listRows)->select();
		//查询设备组
		$groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询未注册设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
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
                $title = "未注册设备列表";
                $header = array(
                            array('name','设备名称'),
                            array('code','设备编码'),
                            array('line','设备线路'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','注册时间'),
                            array('version','软件版本'),
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
     * 已删除设备列表
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
        $keywords = I('keywords');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;

        //where条件处理
        $where['a.closed'] = 1;
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
        $count = M("devices")->alias("a")->join("left join device_stat c on a.id = c.device_id")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,"start_date"=>$start_date,"end_date"=>$end_date));
        //查询列表   
        $data = M("devices")->alias("a")->join("left join device_stat c on a.id = c.device_id")->where($where)->order("a.id desc")->field("a.*,c.comeing,c.outgoing,c.device_time,c.missed,c.last_time")->limit($page->firstRow.','.$page->listRows)->select();
        //查询设备组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询已删除设备列表，查询条件：设备名称：{$name},设备编码：{$code},设备分组id：{$group_id},关键字：{$keywords},注册时间最小值：{$start_date},注册时间最大值：{$end_date}");
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
            $device = M("devices")->find($id);
            $device['IP']=M('device_stat')->where(['device_id'=>$id])->getField('IP');
            if(!$device){
                $this->error('设备不存在');
            }
            $this->assign('isAdmin',$this->isAdmin);
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
            if(!$data['name'] || !$data['code'] || !$data['group_id'] || !$data['line']){
                echo json_encode(array('code'=>300,'msg'=>'请填写必要参数'));exit;
            }
            //判断是编辑还是新增
            if($data['id']){ //编辑设备
                //检测设备是否存在
                if(!M("devices")->find($data['id'])){
                    echo json_encode(array('code'=>300,'msg'=>'设备不存在'));exit;
                }
                //修改设备相关信息
                $data['keywords'] = $data['name'].','.$data['code'];//关键字处理
                $data['upd_time'] = date("Y-m-d H:i:s",time());
                $data['registered'] = 1;//编辑的设备注册
                $data['closed'] = 0;
                if(M("devices")->save($data)){
                    if($this->isAdmin && $data['IP']){
                        M('device_stat')->where(['device_id'=>$data['id']])->save(['IP'=>$data['IP']]);
                    }
                    //编辑成功 
                    //处理权限设备分组
                    M("account_purview")->where("device_id = {$data['id']}")->save(array("group_id"=>$data['group_id']));
                    //端口处理 端口可能是追加 也可能是删减 需要判断是什么情况
                    $count = M("device_line")->where("device_id = {$data['id']}")->count();
                    if ( $count > $data['line'] ) { //删减端口
                        //减去多的端口 从后向前减去 并且需要删除端口权限相关数据
                        M("device_line")->where("device_id = {$data['id']} and code > {$data['line']}")->delete();
                    }
                    $line = array();
                    if ( $count < $data['line'] ) { //追加端口
                        $last_i = $line_count+1;
                        for( $i = $last_i;$i<=$data['line'];$i++){
                            $line[$i]['device_id'] = $data['id'];
                            $line[$i]['code'] = $i;
                        }
                        M("device_line")->addAll($line);
                    }
                    $this->accountLogs->addLog("编辑设备,设备id：{$data['id']}");
                    echo json_encode(array('code'=>200,'msg'=>'设备编辑成功'));exit;
                }else{
                     echo json_encode(array('code'=>300,'msg'=>'设备编辑失败'));exit;
                }

            }else{ //添加设备
                //检测设备是否已经存在
                if(M("devices")->where("code= {$data['code']}")->find()){
                    echo json_encode(array('code'=>300,'msg'=>'设备已存在'));exit;
                }
                //进行设备添加
                unset($data['id']);
                //进行数据处理
                $data['registered'] = 0;//新增设备都是未注册的
                $data['closed'] = 0;
                $data['add_time'] = date("Y-m-d H:i:s",time());
                $data['upd_time'] = date("Y-m-d H:i:s",time());
                $data['keywords'] = $data['name'].','.$data['code'];//关键字处理
                $id = M("devices")->add($data);
                if($id){
                    //添加成功后 
                    //清除设备权限表 防止于遗留数据
                    M("account_purview")->where("device_id = {$id}")->delete();
                    M("account_purview_line")->where("device_id = {$id}")->delete();
                    //device_stat处理
                    if(M('device_stat',null)->where(['device_id'=>$id])->find()){
                        M('device_stat',null)->where(['device_id'=>$id])->delete();
                        M("device_stat")->add(array('device_id'=>$id));
                    }else{
                        M("device_stat")->add(array('device_id'=>$id));
                    }
                    //端口处理
                    M("device_line")->where("device_id = {$id}")->delete();
                    $line = array();
                    for($i=1;$i<=$data['line'];$i++){
                        $line[$i]['device_id'] = $id;
                        $line[$i]['code'] = $i;
                    }
                    if($line){
                        M("device_line")->addAll($line);
                    }
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
            $device = M("devices")->find($id);
            if(!$device){
                echo json_encode(array('code'=>300,'msg'=>'设备不存在'));exit;
            }
            //删除设备 不能从数据库删除 因为其他很多表 存在依赖关系 全都删除 会出现数据断档
            $data = array();
            $data['group_id'] = 0;
            $data['closed'] = 1;
            if(M("devices")->where("id = {$id}")->save($data)){
                //成功后 处理部分数据
                M("account_purview")->where("device_id = {$id}")->delete();
                M("account_purview_line")->where("device_id = {$id}")->delete();
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
     * 彻底删除设备
     * @param id 设备id
     */
    public function DeviceDelte(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'没有选择要删除的设备'));exit;
            }
            //检测设备是否存在
            $device = M("devices")->find($id);
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
            
            if(M("devices")->where("id = {$id}")->delete()){
                M("account_purview")->where("device_id = {$id}")->delete();
                M("account_purview_line")->where("device_id = {$id}")->delete();
                M("device_call")->where("device_id = {$id}")->delete();
                M("device_call_data")->where("device_id = {$id}")->delete();
                M("device_call_flag")->where("device_id = {$id}")->delete();
                M("device_case")->where("device_id = {$id}")->delete();
                M("device_line")->where("device_id = {$id}")->delete();
                M("device_stat")->where("device_id = {$id}")->delete();
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
                $device = M("devices")->find($id);
                if(!$device){
                    continue;
                }
                //删除设备 不能从数据库删除 因为其他很多表 存在依赖关系 全都删除 会出现数据断档
                $data = array();
                $data['group_id'] = 0;
                $data['closed'] = 1;
                if(M("devices")->where("id = {$id}")->save($data)){
                    //成功后 处理部分数据
                    M("account_purview")->where("device_id = {$id}")->delete();
                    M("account_purview_line")->where("device_id = {$id}")->delete();
                }else{
                    continue;
                }
            }
            $dids = implode($ids,',');
            $this->accountLogs->addLog("批量删除设备,设备id集合：{$dids}");
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
     * 升级设备程序
     */
    function Updatefile(){
//        $upload_class = new \Helper\LoadFile();
        $upfile_id = I('upfile_id');
//        $upload_class = new \Think\Upload();// 实例化上传类
//         $upload_class->exts      =     array();// 设置附件上传类型
//        $upload_path = SITE_PATH .'public' . DIRECTORY_SEPARATOR.'Record'. DIRECTORY_SEPARATOR.$upfile_id;
//        if(!file_exists($upload_path)){
//           mkdir($upload_path,0755);
//        }
//        $upload_class->rootPath=   SITE_PATH;
//        $upload_class->savePath= 'public/Record/' . $upfile_id;
//        $upload_class->saveName='file_name';
//        $file_res = $upload_class->upload('upfile');
//
        $dst = 'upfile';
        if (!is_array($_FILES[$dst]) || !is_uploaded_file($_FILES[$dst]['tmp_name'])) {
            echo '未接收到文件！';
            exit;
        }
        $filename = 'file_name';
        $full_dirs = SITE_PATH.  'Update' . '/' . $upfile_id;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs, 0777, true);
        }
        $upload_file = $full_dirs . '/'. $_FILES[$dst]['name'];
        echo $upload_file;
        $a=move_uploaded_file($_FILES[$dst]['tmp_name'], $upload_file);
        chmod($upload_file,'0755');
        
        if(!file_exists($upload_file)){
           echo '上传的文件有误';die;
        }
//        if ( empty($file_res) || strtolower($file_res['type'])  != 'exe') {
//            echo $upload_class->getError();exit();
////            echo "<script>parent.dialog.tip({msg:'上传的文件有误'});dialog.hide();</script>";exit();
//        }
//        $upload_file = $file_res['name'];
//        $_model = new \Models\DeviceStat();
        $_model = D('Common/DeviceStat');
//        $_data = $_model->where(['device_id'=>$upfile_id])->find();
        $_model->where(['device_id'=>$upfile_id])->save(['update_file'=>str_replace(SPSTATIC,'', $upload_file),'update_time'=>time()]);
        echo '软件升级文件已上传,请等待设备升级';exit();
//        echo "<script>parent.UpdateOk();</script>";exit();
    }

    /**
     * 重置计数器
     */
    function doResetStat(){
        $device_ids = \Esy\Requests::post('ids');

        $_model = new \Models\DeviceStat();
        $_model_line = new \Models\DeviceLine();
        while(list($key,$device_id)=@each($device_ids)) {
            $_data = $_model->where('device_id',$device_id)->first();
            if ( $_data->id ) {
                $_data->comeing = \Models\DeviceCall::getTypeCount($device_id,0,10);;
                $_data->outgoing = \Models\DeviceCall::getTypeCount($device_id,0,9);;
                $_data->case = \Models\DeviceCase::getCount($device_id);;
                $_data->missed = \Models\DeviceCall::getTypeCount($device_id,0,11);;
                $_data->save();
                //端口的重置
                $_device_line = $_model_line->where("device_id",$device_id)->get();
                if ( $_device_line ) {
                    foreach( $_device_line as $line_key => $lines ) {
                        $lines->comeing = \Models\DeviceCall::getTypeCount($device_id,$lines->code,10);
                        $lines->outgoing = \Models\DeviceCall::getTypeCount($device_id,$lines->code,9);
                        $lines->case = \Models\DeviceCase::getCount($device_id,$lines->code);
                        $lines->missed = \Models\DeviceCall::getTypeCount($device_id,$lines->code,11);
                        $lines->save();
                    }
                }
            }
        }
        
        \Esy\View::json(array(
            'status'=>200,
            'msg' =>'重置计数器成功!',
        ));
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
                M("devices")->where("id = {$v}")->save(array('group_id'=>$group_id));
            }
            $dids = implode($ids,',');
            $this->accountLogs->addLog("设备批量分组,设备组id：{$group_id},设备id集合：{$dids}");
            echo json_encode(array('code'=>200,'msg'=>'批量分组成功'));exit;
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }

    }
    public function pingDevice(){
        ini_set('max_execution_time',300);
        $ip=I('ip');
        $ip = $ip ? $ip : $_GET['ip'];
        $ip=base64_decode($ip);
        if(!filter_var($ip, FILTER_VALIDATE_IP)){
            echo "{$ip} 不是合法ip";die;
//            exit(json_encode(['code'=>200,'msg'=>"{$ip} 不是合法ip"]));
        }
        header('Content-Type: text/html; charset=gb2312');
        exec("ping {$ip}",$o,$r);
        $o=implode("<br/>",$o);
        echo $o;
    }
}