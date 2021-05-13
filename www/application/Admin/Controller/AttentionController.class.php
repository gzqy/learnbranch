<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
/**
 * 我的关注控制器
 */
class AttentionController extends ShopbaseController {
	protected $nav_id = 2;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/Attention/index'),'name'=>'设备关注'),
            array('id'=>2,'a'=>U('/Attention/app_index'),'name'=>'手机关注'), 
        );
        if(C('APP_TYPE')==1){
            unset($second_nav[1]);
        }
        if(C('APP_TYPE')==2){
            unset($second_nav[0]);
        }
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
        $this->assign('second_nav_id',1);
    }

    /**
     * 关注的设备
     * 账号只能关注自己权限内的账号 字段attention控制 1 关注 0 不关注
     * @param name 设备名称
     * @param code 设备码
     * @param group_id 设备组id
     * @param start_date 加入开始时间
     * @param end_date 加入结束时间
     * @param p 分页当前页 默认1
     * @param limit 分页页数 默认20
     */
    public function index(){
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') : 20;
        $name = I('name');
        $code = I('code');
        $group_id = I('group_id');
        $start_date = I('start_date');
        $end_date = I('end_date');

        //where条件处理
        $where = array();
        $where['a.account_id'] = $this->account_id;
        $where['a.attention'] = 1;
        $name && $where['b.name'] = array('like',"%{$name}%");
        $code && $where['b.code'] = array('like',"%{$code}%");
        $group_id && $where['b.group_id'] = $group_id;
        $start_date && $where['a.add_time'] = array('gt',$start_date);
        $end_date && $where['a.add_time'] = array('lt',$end_date);

        //分页处理
        $count = M("account_purview")->alias('a')
                ->join("left join devices b on a.device_id = b.id")
                ->join("left join device_group d on a.group_id = d.id")
                ->join("left join device_stat c on a.device_id = c.device_id")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M("account_purview")->alias('a')
                ->join("left join devices b on a.device_id = b.id")
                ->join("left join device_group d on a.group_id = d.id")
                ->join("left join device_stat c on a.device_id = c.device_id")
                ->where($where)
                ->order("a.id desc")
                ->field("a.*,b.name,b.code,b.line,c.comeing,c.outgoing,c.missed,c.last_time,c.ip,d.name as gname")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        //判断每个设备是否在线
        if($data){
            $last_time = time() - 600;
            foreach($data as $k=>$v){
                if($v['last_time'] >= $last_time){
                    $data[$k]['status'] = 1;
                }else{
                    $data[$k]['status'] = 0;
                }
            }
        }

        //获取设备分组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询关注设备，查询条件：设备名称 {$name},设备编码：{$code},设备分组id：{$group_id},关注时间最小值:{$start_date},关注时间最大值：{$end_date}");
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('group_id',$group_id);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('groups',$groups);
        $this->display();
    }

    /**
     * 关注设备导出记录
     */
    public function index_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "设备关注";
                $header = array(
                            array('name','设备名称'),
                            array('last_time','设备状态'),
                            array('code','设备编码'),
                            array('line','设备端口'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','关注时间'),
                            array('last_time','最后在线'),
                        );
                $last_time = time() - 600;
                foreach($data as $k=>$v){
                    if($v['last_time'] >= $last_time){
                        $data[$k]['last_time'] =  iconv('utf-8','gbk','在线');
                    }else{
                        $data[$k]['last_time'] =  iconv('utf-8','gbk','离线');
                    }
                    $data[$k]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
                    $data[$k]['last_time'] = date("Y-m-d H:i:s",$v['last_time']);
                    $data[$k]['comeing'] = $v['comeing'] ? $v['comeing'] : 0 ;
                    $data[$k]['outgoing'] = $v['outgoing'] ? $v['outgoing'] : 0 ;
                    $data[$k]['missed'] = $v['missed'] ? $v['missed'] : 0 ;
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
     * 取消关注设备
     * @param id 取关的设备id
     */
    public function unattention(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'请选择要取消关注的设备'));exit;
            }
            $account_id = $this->account_id;
            $attention = M("account_purview")->where("id = {$id} and account_id = {$account_id}")->find();
            if(!$attention){//没有关注记录 
                echo json_encode(array('code'=>300,'msg'=>'关注设备不存在'));exit;
            }
            //删除关注的设备
            $is = M("account_purview")->where("id = {$id} and account_id = $account_id")->save(array('attention'=>0));
            if($is){
                 $this->accountLogs->addLog("取消关注设备，设备id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'设备取消关注成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'设备取消关注失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 关注记录列表
     * @param start_date 查询开始时间戳
     * @param end_date 查询结束时间戳
     * @param start_call_time 最小通话时长
     * @param end_call_time 最大通话时长
     * @param search_line 设备端口号
     * @param serch_type 类型  9 去电 10 来电 11 未接 28音频 29 来电留言 50 现场视频
     */
    public function logList(){
        $start_date = I('start_date');
        $end_date = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time = I('end_call_time');
        $search_line = I('search_line');
        $search_type = I('search_type');
        $tel= I('tel');
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') : 20;
        //where 条件处理
        $where = array();
        $where['a.account_id'] = $this->account_id;
        $search_line && $where['b.line'] = $search_line;
        $search_type && $where['b.type'] = $search_type;
        $tel && $where['b.tel'] = ['like',"%{$tel}%"];
        $start_date && $where['a.add_time'] = array('gt',$start_date);
        $end_date && $where['a.add_time'] = array('lt',$end_date);
        $start_call_time && $where['b.call_time'] = array('gt',$start_call_time);
        $end_call_time && $where['b.call_time'] = array('lt',$end_call_time);
        //分页处理
        $count = M("device_call_flag")->alias('a')
                ->join("left join device_call b on a.call_id = b.id")
                ->join("left join device_line c on a.device_id = c.device_id and a.line_id = c.code ")
                ->field("a.id,a.add_time,b.type,b.call_time,b.tel,b.recording_time,c.name")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('tel'=>$tel,'p'=>$p,"search_type"=>$search_type,"search_line"=>$search_line,"start_call_time"=>$start_call_time,'end_call_time'=>$end_call_time,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M("device_call_flag")->alias('a')
                ->join("left join device_call b on a.call_id = b.id")
                ->join("left join device_line c on a.device_id = c.device_id and b.line_id = c.code ")
                ->field("a.id,a.add_time,a.device_id,b.line_id,b.type,b.call_time,b.tel,b.recording_time,c.PortName,a.call_id,b.files")
                ->where($where)
                ->order("a.id desc")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        //处理通话类型
        if($data){
            $call = D("Common/DeviceCall");
            foreach($data as $k=>$v){
                $data[$k]['type1'] = $v['type'];
                $img = $call->icon($v['type']);
                $type = $call->type($v['type']);
                //检测是否有文件
                if(!$v['files']){
                    $data[$k]['files'] = 0;
                }else{
                    $files = D("Common/DeviceCall")->getFileDir($v['files']).D("Common/DeviceCall")->replaceForFilename($v['files']);
                    if(!is_file($files)){
                        $data[$k]['files'] = 0;
                    }else{
                        $data[$k]['files'] = 1;
                    }
                }
                $data[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $data[$k]['call_time'] = $call->getTime($v['call_time']);
                $data[$k]['recording_time'] = $call->getTime($v['recording_time']);
            }
        }
        $data2 = $data;
        foreach($data2 as $k=>$v){
            foreach($v as $x=>$y){
                if('type' == $x){
                    unset($data2[$k][$x]);
                }
            }
        }
        $this->accountLogs->addLog("查询关注记录，查询条件：端口id {$search_line},记录类型：{$serch_type},通话时间最小值：{$start_call_time},通话时间最大值：{$end_call_time},关注时间最小值：{$start_date},关注时间最大值：{$end_date}");
        //获取通话记录类型集合
        $types = D("Common/DeviceCall")->callTypes();
        $this->assign('search_type',$search_type);
        $this->assign('search_line',$search_line);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('start_call_time',$start_call_time);
        $this->assign('end_call_time',$end_call_time);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('tel',$tel);
        $this->assign('gdata',json_encode($data2));
        $this->assign('types',$types);
        $this->display();        
    }

    /**
     * 关注记录导出列表
     */
    public function logList_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备记录";
                $header = array(
                            array('portname','端口名称'),
                            array('type','录音类型'),
                            array('add_time','关注日期'),
                            array('call_time','录音时长'),
                            array('recording_time','通话时长'),
                            array('tel','通话号码'),
                        );
                foreach($data as $k=>$v){
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
     * 取消关注记录
     * @param id 取消关注的id
     */
    public function unattentionlog(){
         if(IS_AJAX){
            $id = I('id','','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'请选择要取消关注的记录'));exit;
            }
            $account_id = $this->account_id;
            $attention = M("device_call_flag")->where("id = {$id} and account_id = {$account_id}")->find();
            if(!$attention){//没有关注记录 
                echo json_encode(array('code'=>300,'msg'=>'关注记录不存在'));exit;
            }
            //删除关注的记录
            $is = M("device_call_flag")->where("id = {$id} and account_id = $account_id")->delete();
            if($is){
                 $this->accountLogs->addLog("取消关注记录，记录id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'记录取消关注成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'记录取消关注失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 手机设备关注
     * @param name 设备名称
     * @param code 设备码
     * @param group_id 设备组id
     * @param start_date 加入开始时间
     * @param end_date 加入结束时间
     * @param p 分页当前页 默认1
     * @param limit 分页页数 默认20
     */
    public function app_index(){
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') : 20;
        $name = I('name');
        $code = I('code');
        $group_id = I('group_id');
        $start_date = I('start_date');
        $end_date = I('end_date');

        //where条件处理
        $where = array();
        $where['a.account_id'] = $this->account_id;
        $where['a.attention'] = 1;
        $name && $where['b.name'] = array('like',"%{$name}%");
        $code && $where['b.code'] = array('like',"%{$code}%");
        $group_id && $where['b.group_id'] = $group_id;
        $start_date && $where['a.add_time'] = array('gt',$start_date);
        $end_date && $where['a.add_time'] = array('lt',$end_date);

        //分页处理
        $count = M("account_app_purview")->alias('a')
                ->join("left join devices_app b on a.device_id = b.id")
                ->join("left join device_group d on a.group_id = d.id")
                ->join("left join device_app_stat c on a.device_id = c.device_id")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"code"=>$code,"group_id"=>$group_id,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M("account_app_purview")->alias('a')
                ->join("left join devices_app b on a.device_id = b.id")
                ->join("left join device_group d on a.group_id = d.id")
                ->join("left join device_app_stat c on a.device_id = c.device_id")
                ->where($where)
                ->order("a.id desc")
                ->field("a.*,b.name,b.code,c.comeing,c.outgoing,c.missed,c.last_time,d.name as gname")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();

        //获取设备分组
        $groups = D("Common/DeviceGroup")->getOption();
        $this->accountLogs->addLog("查询关注手机设备，查询条件：设备名称 {$name},设备编码：{$code},设备分组id：{$group_id},关注时间最小值:{$start_date},关注时间最大值：{$end_date}");
        $this->assign('name',$name);
        $this->assign('code',$code);
        $this->assign('group_id',$group_id);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',2);
        $this->display();
    }

     /**
     * 关注设备导出记录
     */
    public function app_index_excel(){
       if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备关注";
                $header = array(
                            array('name','设备名称'),
                            array('code','手机号码'),
                            array('gname','设备分组'),
                            array('comeing','来电统计'),
                            array('outgoing','去电统计'),
                            array('missed','未接统计'),
                            array('add_time','关注时间'),
                            array('last_time','最后在线'),
                        );
                $last_time = time() - 600;
                foreach($data as $k=>$v){
                    $data[$k]['add_time'] = date("Y-m-d H:i:s",$v['add_time']);
                    $data[$k]['last_time'] = date("Y-m-d H:i:s",$v['last_time']);
                    $data[$k]['comeing'] = $v['comeing'] ? $v['comeing'] : 0 ;
                    $data[$k]['outgoing'] = $v['outgoing'] ? $v['outgoing'] : 0 ;
                    $data[$k]['missed'] = $v['missed'] ? $v['missed'] : 0 ;
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
     * 取消关注手机设备
     * @param id 取关的设备id
     */
    public function unattention1(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'请选择要取消关注的设备'));exit;
            }
            $account_id = $this->account_id;
            $attention = M("account_app_purview")->where("id = {$id} and account_id = {$account_id}")->find();
            if(!$attention){//没有关注记录 
                echo json_encode(array('code'=>300,'msg'=>'关注设备不存在'));exit;
            }
            //删除关注的设备
            $is = M("account_app_purview")->where("id = {$id} and account_id = $account_id")->save(array('attention'=>0));
            if($is){
                 $this->accountLogs->addLog("取消关注手机设备，设备id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'设备取消关注成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'设备取消关注失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /*
     * 关注记录列表
     * @param start_date 查询开始时间戳
     * @param end_date 查询结束时间戳
     * @param start_call_time 最小通话时长
     * @param end_call_time 最大通话时长
     * @param search_line 设备端口号
     * @param serch_type 类型  9 去电 10 来电 11 未接 28音频 29 来电留言 50 现场视频
     */
    public function app_logList(){
        $start_date = I('start_date');
        $end_date = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time = I('end_call_time');
        $search_line = I('search_line');
        $search_type = I('search_type');
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') : 20;

        //where 条件处理
        $where = array();
        $where['a.account_id'] = $this->account_id;
        $search_type && $where['b.type'] = $search_type;
        $start_date && $where['a.add_time'] = array('gt',$start_date);
        $end_date && $where['a.add_time'] = array('lt',$end_date);
        $start_call_time && $where['b.call_time'] = array('gt',$start_call_time);
        $end_call_time && $where['b.call_time'] = array('lt',$end_call_time);

        //分页处理
        $count = M("device_app_call_flag")->alias('a')
                ->join("left join device_app_call b on a.call_id = b.id")
                ->field("a.id,a.add_time,b.type,b.call_time,b.tel,b.recording_time")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"search_type"=>$search_type,"start_call_time"=>$start_call_time,'end_call_time'=>$end_call_time,'start_date'=>$start_date,'end_date'=>$end_date));
        //查询列表
        $data = M("device_app_call_flag")->alias('a')
                ->join("left join device_app_call b on a.call_id = b.id")
                ->field("a.id,a.add_time,b.type,b.call_time,a.call_id,b.tel,b.recording_time")     
                ->where($where)
                ->order("a.id desc")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        //处理通话类型
        if($data){
            $call = D("Common/DeviceCall");
            foreach($data as $k=>$v){
                $data[$k]['type1'] = $v['type'];
                $img = $call->icon($v['type']);
                $type = $call->type($v['type']);
                $data[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $data[$k]['call_time'] = $call->getTime($v['call_time']);
                $data[$k]['recording_time'] = $call->getTime($v['recording_time']);
            }
        }
        $data2 = $data;
        foreach($data2 as $k=>$v){
            foreach($v as $x=>$y){
                if('type' == $x){
                    unset($data2[$k][$x]);
                }
            }
        }
        $this->accountLogs->addLog("查询关注记录，查询条件：端口id {$search_line},记录类型：{$serch_type},通话时间最小值：{$start_call_time},通话时间最大值：{$end_call_time},关注时间最小值：{$start_date},关注时间最大值：{$end_date}");
        //获取通话记录类型集合
        $types = D("Common/DeviceCall")->callTypes();
        $this->assign('search_type',$search_type);
        $this->assign('search_line',$search_line);
        $this->assign('start_date',$start_date);
        $this->assign('end_date',$end_date);
        $this->assign('start_call_time',$start_call_time);
        $this->assign('end_call_time',$end_call_time);
        $this->assign('Page',$page->show());
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data2));
        $this->assign('types',$types);
        $this->assign('second_nav_id',2);
        $this->display();        
    }

    /**
     * 关注记录导出列表
     */
    public function app_logList_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "app设备记录";
                $header = array(
                            array('type','录音类型'),
                            array('add_time','关注日期'),
                            array('call_time','录音时长'),
                            array('recording_time','通话时长'),
                            array('tel','通话号码'),
                        );
                foreach($data as $k=>$v){
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
     * 取消关注记录
     * @param id 取消关注的id
     */
    public function app_unattentionlog(){
         if(IS_AJAX){
            $id = I('id','','int');
            if(!$id){
                echo json_encode(array('code'=>300,'msg'=>'请选择要取消关注的记录'));exit;
            }
            $account_id = $this->account_id;
            $attention = M("device_app_call_flag")->where("id = {$id} and account_id = {$account_id}")->find();
            if(!$attention){//没有关注记录 
                echo json_encode(array('code'=>300,'msg'=>'关注记录不存在'));exit;
            }
            //删除关注的记录
            $is = M("device_app_call_flag")->where("id = {$id} and account_id = $account_id")->delete();
            if($is){
                 $this->accountLogs->addLog("取消关注手机记录，记录id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'记录取消关注成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'msg'=>'记录取消关注失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

}