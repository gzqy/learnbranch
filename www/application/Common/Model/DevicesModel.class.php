<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DevicesModel extends CommonModel {
    protected $table = 'devices';
    public $timestamps = false;
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '设备名称不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('code', 'require', '设备编码不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
        array('line', 'require', '设备端口不能为空', 1, 'regex', CommonModel:: MODEL_BOTH ),
    );

    /**
     * 获取设备数量统计
     */
    public function getDevicesCount(){
        $where = array();
        $where['a.registered'] = 1;
        $where['a.closed'] = 0;
        $data = array();
        $data['total'] = $this->alias("a")->join("inner join device_stat b on a.id = b.device_id ")->where($where)->count();//总数
        //统计在线
        $time = time() - $this->_last_time;
        $time && $where['b.last_time'] = array('egt',$time);
        $data['inline'] = $this->alias("a")->join("inner join device_stat b on a.id = b.device_id ")->where($where)->count();//总在线数
        $data['out'] = $data['total'] - $data['inline'];
        return $data;
    }

   /**
     * 获取手机设备数量统计
     */
    public function getAppDevicesCount(){
        $where = array();
        $where['a.registered'] = '1';
        $where['a.closed'] = '0';
        $data = array();
        $data['total'] = M("devices_app")->alias("a")->join("inner join device_stat b on a.id = b.device_id ")->where($where)->count();//总数
        //统计在线
        $time = time() - $this->_last_time;
        $time && $where['b.last_time'] = array('egt',$time);
        $data['inline'] = M("devices_app")->alias("a")->join("inner join device_stat b on a.id = b.device_id ")->where($where)->count();//总在线数
        $data['out'] = $data['total'] - $data['inline'];
        return $data;
    } 

    /**
     * 获取设备列表
     * @param status 状态 0 全部 1在线 2 离线
     * @param name 设备名称
     * @param code 设备编码
     * @param group_id 设备组id
     * @param start_date 注册最小时间
     * @param end_date 注册最大时间
     * @param keywords 关键字
     */
    public function getDevicesList(){
        $p = I('p') ? I('p') : 1;
        $group_id = I('group_id');
        $keywords = I('keywords');
        $name = I('name');
        $code = I('code');
        $start_date = I('start_date');
        $end_date = I('end_date');
        
        $status = I('status');
        $account_id = $_SESSION['ACCOUNTS']['id'];
        //处理where条件
        $where = array();
        $where['a.account_id'] = $account_id;
        $where['b.registered'] = 1;
        $where['b.closed'] = 0;
        $group_id && $where['a.group_id'] = $group_id;
        $name && $where['b.name'] = array('like',"%{$name}%");
        $code && $where['b.code'] = array('like',"%{$code}%");
        $keywords && $where['b.keywords'] = array('like',"%{$keywords}%");
        $start_date &&  $where['b.add_time'] = array('gt',$start_date);
        $end_date &&  $where['b.add_time'] = array('lt',$end_date);
    
        //获取总的设备数  在线设备数  离线设备数
        $data = M('account_purview')->alias('a')
            ->join('inner join devices b on a.device_id = b.id')
            ->join('inner join device_stat c on a.device_id = c.device_id')
            ->where($where)
            ->order('b.id asc')
            ->field('a.attention,b.*,c.comeing,c.outgoing,c.case,c.missed,c.last_time,b.code')
            ->select();
        $dcounts = array();
        $dcounts['total']=count($data);
        $inlineData = [];
        $outlineData=[];
        $last_time = time() - $this->_last_time;//是否在线时间标准
        foreach ($data as $v){
            if($v['last_time']>=$last_time){
                $inlineData[]=$v;
            }else{
                $outlineData[]=$v;
            }
        }
        $dcounts['inline']=count($inlineData);
        $dcounts['out']=count($outlineData);
        //        $dcounts['total'] = M('account_purview')->alias('a')
        //              ->join('inner join devices b on a.device_id = b.id')
        //              ->join('inner join device_stat c on a.device_id = c.device_id')
        //              ->where($where)
        //              ->count();
        //
        //        $last_time = time() - $this->_last_time;//是否在线时间标准
        //        $dcounts['inline'] = M('account_purview')->alias('a')
        //              ->join('inner join devices b on a.device_id = b.id')
        //              ->join('inner join device_stat c on a.device_id = c.device_id')
        //              ->where($where)
        //              ->where("c.last_time >= {$last_time}")
        //              ->count();
        //        $dcounts['out'] = $dcounts['total'] - $dcounts['inline'];
    
        //查询设备列表
        //        if(1 == $status){
        //            $where['c.last_time'] = array('egt',$last_time);
        //        }else if(2==$status){
        //            $where['c.last_time'] = array('lt',$last_time);
        //        }
        //        $count = M('account_purview')->alias('a')
        //              ->join('inner join devices b on a.device_id = b.id')
        //              ->join('inner join device_stat c on a.device_id = c.device_id')
        //              ->where($where)
        //              ->count();
        if($status==1){
            $count=$dcounts['inline'];
            $data=$inlineData;
        } elseif($status==2){
            $count=$dcounts['out'];
            $data=$outlineData;
        }else{
            $count=$dcounts['total'];
        }
        //分页,同时 uploadStatus也在使用
        $page = $this->page($count,20,array('p'=>$p,"group_id"=>$group_id,"keywords"=>$keywords,"name"=>$name,"code"=>$code,"start_date"=>$start_date,"end_date"=>$end_date,'status'=>$status));
        $data=array_slice($data,$page->firstRow,$page->listRows);
        //        $data = M('account_purview')->alias('a')
        //            ->join('inner join devices b on a.device_id = b.id')
        //            ->join('inner join device_stat c on a.device_id = c.device_id')
        //            ->where($where)
        //            ->limit($page->firstRow.','.$page->listRows)
        //            ->order('b.id asc')
        //            ->field('a.attention,b.*,c.comeing,c.outgoing,c.case,c.missed,c.last_time,b.code')
        //            ->select();
        //判断是否在线
        foreach($data as $k=>$v){
            if($v['last_time'] >= $last_time){
                $data[$k]['status'] = 1;
            }else{
                $data[$k]['status'] = 0;
            }
            $data[$k]['last_call_date']=M('device_call',null)->where(['device_id'=>$v['id']])->
            order('id desc')->getField('call_date');
            // $data[$k]['Store'] = round(($v['totalstore'] - $v['totalfreestore']) / $v['totalstore'],2)*100;
            // $data[$k]['Mem'] = round(($v['totalmem'] - $v['totalfreemem']) / $v['totalmem'],2)*100;
            unset($data[$k]['last_time']);
            unset($data[$k]['totalstore']);
            unset($data[$k]['totalfreestore']);
            unset($data[$k]['totalmem']);
            unset($data[$k]['totalfreemem']);
        }
        $data1 = array();
        $data1['data'] = $data;
        $data1['page'] = $page->show();
        $data1['status'] = $dcounts;
        return $data1;
    }
    
    /**
     * 获取设备上传列表
     * @return array
     */
    public function getDevicesUploadList(){
        $p = I('p') ? I('p') : 1;
        $group_id = I('group_id');
        $keywords = I('keywords');
        $name = I('name');
        $code = I('code');
        
        $status = I('status');
        $account_id = $_SESSION['ACCOUNTS']['id'];
        //处理where条件
        $where = array();
        $where['a.account_id'] = $account_id;
        $where['b.registered'] = 1;
        $where['b.closed'] = 0;
        $group_id && $where['a.group_id'] = $group_id;
        $name && $where['b.name'] = array('like',"%{$name}%");
        $code && $where['b.code'] = array('like',"%{$code}%");
        $keywords && $where['b.keywords'] = array('like',"%{$keywords}%");
        $isUploadOption = I('isUploadOption');
//        //获取总的设备数  在线设备数  离线设备数
//        $dcounts = array();
//        $dcounts['total'] = M('account_purview')->alias('a')
//            ->join('inner join devices b on a.device_id = b.id')
//            ->join('inner join device_stat c on a.device_id = c.device_id')
//            ->where($where)
//            ->count();
//        $last_time = time() - $this->_last_time;//是否在线时间标准
//        $dcounts['inline'] = M('account_purview')->alias('a')
//            ->join('inner join devices b on a.device_id = b.id')
//            ->join('inner join device_stat c on a.device_id = c.device_id')
//            ->where($where)
//            ->where("c.last_time >= {$last_time}")
//            ->count();
//        $dcounts['out'] = $dcounts['total'] - $dcounts['inline'];
        $up_start_date = I('upload_start_date') ? date('Y-m-d',strtotime(I('upload_start_date'))) : date('Y-m-d',strtotime("-1 days"));
        $up_end_date = I('upload_end_date') ?  date('Y-m-d',strtotime(I('upload_end_date'))) : date('Y-m-d');
        $up_start_date = $up_start_date . ' 00:00:00';
        $up_end_date = $up_end_date . ' 00:00:00';
        $up_start_time = strtotime($up_start_date);
        $up_end_time = strtotime($up_end_date);
        $sql = "SELECT device_id FROM `device_call` where add_time>={$up_start_time} and add_time<={$up_end_time} and type in (9,10,11)  GROUP BY device_id";
        //获取所有上传的设备
        $uploadIds = (array)M('device_call')->query($sql);
        if(empty($uploadIds)){
            $uploadIds[]=0;
        }
        $orderBy = 'b.id asc';
        $uploadIds = array_filter($uploadIds);
        if(!empty($uploadIds)){
            $uploadIds = array_column($uploadIds,'device_id');
            $uploadIds = implode(',',$uploadIds);
            $orderBy = " FIELD(b.id,$uploadIds) ";
        }else{
            $uploadIds = '0';
        }
        //全部
        if(!$isUploadOption){
        
        }elseif($isUploadOption==1){    //上传
            $where['b.id']=['in', $uploadIds];
        }elseif($isUploadOption==2){    //未上传
            $where['b.id']=['not in',$uploadIds];
        }
        $count = M('account_purview')->alias('a')
            ->join('inner join devices b on a.device_id = b.id')
            ->join('inner join device_stat c on a.device_id = c.device_id')
            ->where($where)
            ->count();
        
        //分页,同时 uploadStatus也在使用
        $page = $this->page($count,20,array('p'=>$p,"group_id"=>$group_id,"keywords"=>$keywords,"name"=>$name,"code"=>$code,'status'=>$status,'upload_start_date'=>$up_start_date,'upload_end_date'=>$up_end_date,'isUploadOption'=>(int)$isUploadOption));
        $data = M('account_purview')->alias('a')
            ->join('inner join devices b on a.device_id = b.id')
            ->join('inner join device_stat c on a.device_id = c.device_id')
            ->where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order($orderBy)
            ->field('a.attention,b.*,c.comeing,c.outgoing,c.case,c.missed,c.last_time,b.code')
            ->select();
        
//        //判断是否在线
//        foreach($data as $k=>$v){
//            if($v['last_time'] >= $last_time){
//                $data[$k]['status'] = 1;
//            }else{
//                $data[$k]['status'] = 0;
//            }
//            // $data[$k]['Store'] = round(($v['totalstore'] - $v['totalfreestore']) / $v['totalstore'],2)*100;
//            // $data[$k]['Mem'] = round(($v['totalmem'] - $v['totalfreemem']) / $v['totalmem'],2)*100;
//            unset($data[$k]['last_time']);
//            unset($data[$k]['totalstore']);
//            unset($data[$k]['totalfreestore']);
//            unset($data[$k]['totalmem']);
//            unset($data[$k]['totalfreemem']);
//        }
        $data1 = array();
        $data1['data'] = $data;
        $data1['page'] = $page->show();
//        $data1['status'] = $dcounts;
        return $data1;
    }
    /**
     * 检测管理员是否有设备查看权限
     * @param $id 设备id
     * @param $account_id 管理员id
     */
    public function checkDeviceAuth($id,$account_id){
        if(!$id || !$account_id){
            return false;
        }
        $attention = M('account_purview')->where(array('account_id'=>$account_id,'device_id'=>$id))->find();
        if($attention){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取所有设备并按组分好
     */
    function getDevices(){
        $where = array();
        $where['a.registered'] = 1; //已经注册的
        $where['a.closed'] = 0; //没有删除的
        $data = $this->alias('a')->join("inner join device_group b on a.group_id = b.id")->field('a.name ,a.code,a.id,a.group_id,b.name as gname,b.id as gid')->where($where)->select();
        //对结果进行分组
        foreach($data as $k=>$v){
            $_data[$v['group_id']]['id'] = $v['gid'];
            $_data[$v['group_id']]['name'] = $v['gname'];
            $_data[$v['group_id']]['devices'][$k] = $v;   
        }
        unset($data);
        return $_data;
    }

    function added($data){
        if ( empty($data) ) {
            return false;
        }
        $device_id = self::insertGetId($data);

        self::added_partition($device_id);
        return $device_id;
    }
    
    function added_partition($device_id){
        if ( empty($device_id) ) {
            return false;
        }
        $is_partition = \Esy\Config::get('is_partition');
        if ( !$is_partition ) {
            return false;
        }

        $list = 10; //每10个设备放置到一个分区中
        $key = floor(($device_id)/$list); //获取当前设备应该是那个分区
        //取id key
        $partition_name = 'p'.$key;
        $partition_less = ($key+1)*$list;
        //判断分区是否纯在,
        $show_partition_name = DB::select("SELECT partition_name FROM INFORMATION_SCHEMA.partitions  WHERE TABLE_SCHEMA = schema()  AND TABLE_NAME=? and partition_name=?", array('device_call',$partition_name));
        if ( !$show_partition_name  ) { //新增分区
            DB::select("ALTER TABLE device_call ADD PARTITION (PARTITION $partition_name VALUES LESS THAN ($partition_less));");
        }
    }
    
    function getOption(){
        $_data = self::orderBy('sort', 'asc')->get();
        if ( $_data ) {
            $data = $_data->toArray();
            while (list($key, $val) = @each($data)) {
                $result[$val['id']] = $val;
            }
        }
        return $result;
    }
    
    function getGroups($group_id){
        if ( empty($group_id) ) {
            return false;
        }

        $_data = M('devices',null)->where(['group_id'=>$group_id,'registered'=>1,'closed'=>0])->select();
        if ($_data) {
//            $data = $_data->toArray();
            $result = self::getData($_data);
            
        }
        return $result;
    }
    
    function getGroupCount($group_id){
        if ( empty($group_id) ) {
            return false;
        }
        $_data = self::where("group_id",$group_id)->count();

        return $_data;
    }
    
    function getData($data){
        
        while (list($key, $val) = @each($data)) {
            $result[$key] = self::forData($val);            
        }

        return $result;
    }
    
    function forData($val){
        $val['device_id'] = empty($val['device_id']) ? $val['id'] : $val['device_id'];
        $stats = D('Common/DeviceStat')->getData($val['device_id']);
        if ( $stats ) {

            $val = array_merge($val,$stats);
        }
        return $val;
    }
    
    function getName($id){
        if ( empty($id) ) {
            return false;
        }
        $_data = self::where("id",$id)->first();
        if ( $_data ) {
            $data = $_data->name;  
        }
        
        
        return $data;
    }
}