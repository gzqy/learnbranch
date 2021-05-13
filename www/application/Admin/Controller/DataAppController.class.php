<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use Common\Utils\DataUtil;
/**
 * 数据统计控制器
 */
class DataAppController extends ShopbaseController {
    protected $nav_id = 4;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/DataApp/index_devices'),'name'=>'数据总览'), 
            array('id'=>2,'a'=>U('/DataApp/Statistics'),'name'=>'数据统计'), 
            array('id'=>3,'a'=>U('/DataApp/forms'),'name'=>'报表生成'), 
        ); 
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }

    private function menu_index(){
        $third_nav = array(
            array('id'=>1,'a'=>U('DataApp/index_devices'),'name'=>'设备总览'),
            array('id'=>2,'a'=>U('DataApp/index_accounts'),'name'=>'账号总览'),
            array('id'=>3,'a'=>U('DataApp/index_calls'),'name'=>'记录总览'),
        );
        $this->assign('third_nav',$third_nav);
    }
    /**
     * 设备数据总览
     * 1、设备数量、在线数量、离线数量、在线率
     * 2、总添加数量、已注册数量、未注册数量、删除数量
     * 3、端口总数、平均端口数量
     * 4、cpu 内存 存储统计
     */
    public function index_devices(){
        $this->menu_index();
        //权限设备总数 在线 离线 在线率
        $account_devices = $this->accounts->getAccountAppDevicesCount($this->account_id);//权限设备总数 在线 离线
        $account_devices['round'] = 100 * round($account_devices['inline']/$account_devices['total'],2);
        //设备添加数 注册数量 未注册数量 删除数量
        $devices = DataUtil::getAppDeviceCount();
        //设备端口总数 平均端口数量统计
        $lines = DataUtil::getAppDeviceLines($devices['registered']);
        //设备的cpu最高使用率 最低使用率 平均使用率 内存、存储的
        $system  = DataUtil::getDeviceSystem();
        //数据输送前端模板
        $this->accountLogs->addLog("查看数据统计-数据总览-设备总览");
        $this->assign('second_nav_id',1);
        $this->assign('third_nav_id',1);
        $this->assign('account_devices',$account_devices);
        $this->assign('devices',$devices);
        $this->assign('lines',$lines);
        $this->assign('system',$system);
        $this->display();
    }

    /**
     * 账号统计
     * 账号自身信息
     * 账号日志信息
     * 账号关注信息
     */
    public function index_accounts(){
        $this->menu_index();
        //账号统计
        $accounts = $this->accounts->getAccountsCount();
        $accounts['round'] = 100 * round(($accounts['inline'] / $accounts['total']),2)."%";
        
        //日志信息统计
        $account_logs = DataUtil::getAccountLogs($accounts['total']);
        //账号关注信息统计
        $account_attentios = DataUtil::getAccountAppAttention($accounts['total']);
        $this->accountLogs->addLog("查看数据统计-数据总览-账号总览");
        $this->assign('accounts',$accounts);
        $this->assign('account_logs',$account_logs);
        $this->assign('account_attentios',$account_attentios);
        $this->assign('second_nav_id',1);
        $this->assign('third_nav_id',2);
        $this->display();
    }

    /**
     * 通话记录统计
     * 数量占比 统计
     * 总录音时长、总通话时长、平均录音时长、平均通话时长、录音效率
     * 按月份 统计各项数据
     * 平均每天、每周、每月、每年各项通话数量
     */
    public function index_calls(){
        $this->menu_index();
        //通话记录总览统计
        $logs = DataUtil::getaAppCallCount();
        //计算总的录音时长、通话时长、平均录音时长。平均通话时长、录音效率
        if($logs){
            $res['record_time'] = $logs['recording_time'];
            $res['call_time'] = $logs['call_time'];
            $res['round'] = 100*round($res['record_time']/$res['call_time'],2).'%';
            $res['avge_record'] = round($res['record_time']/($logs['comeing'] + $logs['outgoing']),0);
            $res['avge_call'] = round($res['call_time']/($logs['comeing'] + $logs['outgoing']),0);
            $res['record_time1'] = $this->secToTime($res['record_time']);
            $res['call_time1'] = $this->secToTime($res['call_time']);
            $res['avge_record1'] = $this->secToTime($res['avge_record']);
            $res['avge_call1'] = $this->secToTime($res['avge_call']);
        }
        //按时间统计通话量
        $callByTime = DataUtil::getAppCallCountByTime();
        $callByTime['recording_time1'] = $this->secToTime($callByTime['recording_time']);
        $callByTime['call_time1'] = $this->secToTime($callByTime['call_time']);
        $callByTime['recording_time'] = round($callByTime['recording_time']/3600,2);
        $callByTime['call_time'] = round($callByTime['call_time']/3600,2);
        //统计每个月份的数据
        $monthLogs = DataUtil::getAppLogsByMonth();
        //处理
        $x = '[';
        $y = array();
        foreach($monthLogs as $k => $v){
            $x .= "'".$v['date']."',";
            $y['coming'] .= $v['comeing'].',';
            $y['outing'] .= $v['outgoing'].',';
            $y['miss'] .= $v['missed'].',';
        }
        $x .= "]";
        $arr = "{
            title : {text: '记录变化统计',subtext: ''},
            tooltip : {trigger: 'axis'},
            legend: {data:['来电总数','去电总数','未接来电']},
            toolbox: {
                show : true,
                feature : {
                    mark : {show: true},
                    dataView : {show: true, readOnly: false},
                    magicType : {show: true, type: ['line', 'bar']},
                    restore : {show: true},
                    saveAsImage : {show: true}
                }
            },
            calculable : true,
            xAxis : [
                {
                    type : 'category',
                    boundaryGap : false,
                    data : {$x}
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    axisLabel : {
                        formatter: '{value}'
                    }
                }
            ],
            series : [
                {
                    name:'来电总数',
                    type:'line',
                    smooth:true,
                    data:[{$y['coming']}],
                    markPoint : {
                        data : [
                            {type : 'max', name: '最大值'},
                            {type : 'min', name: '最小值'}
                        ]
                    },
                    markLine : {
                        data : [
                            {type : 'average', name: '平均值'}
                        ]
                    }
                },
                {
                    name:'去电总数',
                    type:'line',
                    smooth:true,
                    data:[{$y['outing']}],
                    markPoint : {
                        data : [
                            {type : 'max', name: '最大值'},
                            {type : 'min', name: '最小值'}
                        ]
                    },
                    markLine : {
                        data : [
                            {type : 'average', name: '平均值'}
                        ]
                    }
                },
                {
                    name:'未接来电',
                    type:'line',
                    smooth:true,
                    data:[{$y['miss']}],
                    markPoint : {
                        data : [
                            {type : 'max', name: '最大值'},
                            {type : 'min', name: '最小值'}
                        ]
                    },
                    markLine : {
                        data : [
                            {type : 'average', name: '平均值'}
                        ]
                    }
                },
            ]
        }";
        $this->accountLogs->addLog("查看数据统计-数据总览-记录总览");
        $this->assign('logs',$logs);
        $this->assign('res',$res);
        $this->assign('callByTime',$callByTime);
        $this->assign('option',$arr);
        $this->assign('third_nav_id',3);
        $this->assign('second_nav_id',1);
        $this->display();
    }

    /**
     * 数据统计
     */
    public function Statistics(){
        $purviews = $this->accountPurview->getAppData($this->account_id);//权限设备
        $this->assign('purviews',$purviews);
        $this->assign('second_nav_id',2);
        $this->display();
    }

    /**
     * 记录统计 
     * @param start_date 统计开始时间
     * @param end_date 统计结束时间
     * @param ids 要统计的设备集合
     */
    public function doStatistics(){
        if(IS_POST){
            $start_date = I('start_date');
            $end_date = I('end_date');
            $ids = I('ids');
            if(!$ids){
                $this->error("请选择要统计的设备");
            }
            $device_ids = implode($ids, ',');
            //处理时间
            $start_date && $start_date =strtotime($start_date);
            $end_date && $end_date = strtotime($end_date);
			//$start_date && $start_date = date("Y-m-d 00:00:00",strtotime($start_date));
            //$end_date && $end_date = date("Y-m-d 00:00:00",strtotime($end_date));
            //where条件处理
            if($start_date && $end_date){
                $where['c.add_time'] = array("between",array($start_date,$end_date));
            }else{
               $start_date && $where['c.add_time'] = array('egt',$start_date);
                $end_date && $where['c.add_time'] = array('elt',$end_date); 
            }
            

            $where['b.id'] = array("IN","$device_ids");
      
			 $data = M("devices_app")->alias("b")
                ->join("left join device_app_call c on b.id = c.device_id")
                ->where($where)
                ->field(" sum(case when c.type='10' then 1 else 0 end) as comeing,sum(case when c.type='9' then 1 else 0 end) as outgoing,sum(case when c.type='11' then 1 else 0 end) as missed,COALESCE(0) as audio,COALESCE(0) as message,COALESCE(0) as vedio,COALESCE(sum(c.call_time),0) as call_time,COALESCE(sum(c.recording_time),0) as recording_time,b.code,b.name,b.id")->order("b.id desc")->group("b.id")->select();
			
			             /*
			$data = M("device_app_call_data")->alias("a")->join("right join devices_app b on a.device_id = b.id")->where($where)->field("COALESCE(sum(a.comeing),0) as comeing,COALESCE(sum(a.outgoing),0) as outgoing,COALESCE(sum(a.missed),0) as missed,COALESCE(sum(a.audio),0) as audio,COALESCE(sum(a.message),0) as message,COALESCE(sum(a.vedio),0) as vedio,COALESCE(sum(a.call_time),0) as call_time,COALESCE(sum(a.recording_time),0) as recording_time,b.code,b.name,b.id")->order("b.id desc")->group("a.device_id")->select();
			*/
            $result = array();
            if($data){ //对结果进行处理
                foreach($data as $k=>$v){
                    $result[$v['id']] = $v;
                }   
            }
            unset($data);
            $data = array();
            //查询不到结果的就是 o
            foreach($ids as $k=>$v){
                if($result[$v]){ //设备查询到数据
                    $data[$v] = $result[$v];
                    $data[$v]['call_time'] = $this->secToTime($result[$v]['call_time']);
                    $data[$v]['recording_time'] = $this->secToTime($result[$v]['recording_time']);
                }else{//设备没有查询到数据 说明都是0
                    $result[$v]['id'] = $v;
                    //查询设备信息
                    $device = M("devices_app")->where("id = {$v}")->field("code,name")->find();
                    $data[$v]['code'] = $device['code'];
                    $data[$v]['name'] = $device['name'];
                    $data[$v]['comeing'] = 0;
                    $data[$v]['outgoing'] = 0;
                    $data[$v]['missed'] = 0;
                    $data[$v]['audio'] = 0;
                    $data[$v]['message'] = 0;
                    $data[$v]['vedio'] = 0;
                    $data[$v]['call_time'] = "00:00:00";
                    $data[$v]['recording_time'] = "00:00:00";
                }
            }

            $this->accountLogs->addLog("查询数据统计，查询条件：设备id{$device_ids},起始时间：{$start_date},结束时间：{$end_date}");
            $this->assign('data',$data);
            $this->assign('second_nav_id',2);
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
            $this->assign('ids',implode($ids,','));
            $this->display();
        }else{
            $this->error("请求方式错误");
        }
    }

    public function doStatistics_excel(){
        if(1 == I('excel')){
            $start_date = I('start_date');
            $end_date = I('end_date');
            $device_ids = I('ids');
            if(!$device_ids){
                $this->error("请选择要统计的设备");
            }
            $ids = explode(',', $device_ids);
            //处理时间
            $start_date && $start_date = date("Y-m-d 00:00:00",strtotime($start_date));
            $end_date && $end_date = date("Y-m-d 00:00:00",strtotime($end_date));
            //where条件处理
            if($start_date && $end_date){
                $where['a.time'] = array("between",array($start_date,$end_date));
            }else{
               $start_date && $where['a.time'] = array('egt',$start_date);
                $end_date && $where['a.time'] = array('elt',$end_date); 
            }
            
            $where['a.type'] = '0';
            $where['a.device_id'] = array("IN","$device_ids");
            $data = M("device_app_call_data")->alias("a")->join("right join devices_app b on a.device_id = b.id")->where($where)->field("COALESCE(sum(a.comeing),0) as comeing,COALESCE(sum(a.outgoing),0) as outgoing,COALESCE(sum(a.missed),0) as missed,COALESCE(sum(a.audio),0) as audio,COALESCE(sum(a.message),0) as message,COALESCE(sum(a.vedio),0) as vedio,COALESCE(sum(a.call_time),0) as call_time,COALESCE(sum(a.recording_time),0) as recording_time,b.code,b.name,b.id")->order("b.id desc")->group("a.device_id")->select();

            $result = array();
            if($data){ //对结果进行处理
                foreach($data as $k=>$v){
                    $result[$v['id']] = $v;
                }   
            }
            unset($data);
            $data = array();
            //查询不到结果的就是 o
            foreach($ids as $k=>$v){
                if($result[$v]){ //设备查询到数据
                    $data[$v] = $result[$v];
                    $data[$v]['call_time'] = $this->secToTime($result[$v]['call_time']);
                    $data[$v]['recording_time'] = $this->secToTime($result[$v]['recording_time']);
                }else{//设备没有查询到数据 说明都是0
                    $result[$v]['id'] = $v;
                    //查询设备信息
                    $device = M("devices_app")->where("id = {$v}")->field("code,name")->find();
                    $data[$v]['code'] = $device['code'];
                    $data[$v]['name'] = $device['name'];
                    $data[$v]['comeing'] = 0;
                    $data[$v]['outgoing'] = 0;
                    $data[$v]['missed'] = 0;
                    $data[$v]['audio'] = 0;
                    $data[$v]['message'] = 0;
                    $data[$v]['vedio'] = 0;
                    $data[$v]['call_time'] = "00:00:00";
                    $data[$v]['recording_time'] = "00:00:00";
                }
            }
            header ( "Content-type:application/vnd.ms-excel" );  
            header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
            $head =array('设备名称','来电数量','去电数量','未接数量','留言数量','音频总数','视频总数','通话时长','录音时长');
            foreach($head as $k=>$v){
                $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
            }
            $i = 0;
            $fp = fopen('php://output', 'a');
            fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
            
            foreach($data as $k => $v){
                $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['comeing']);
                $excels[$i][] = iconv('utf-8','gbk',$v['outgoing']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['missed']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['audio']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['message']);
                $excels[$i][] =  iconv('utf-8','gbk',$v['vedio']);
                $excels[$i][] =  $v['call_time'];
                $excels[$i][] =  $v['recording_time'];
                
                fputcsv($fp,$excels[$i]);
                $i++;
            }
            $this->accountLogs->addLog("导出数据统计表格");
            unset($excels);  
            ob_flush();  
            flush();    
        }else{
            $this->error("请求方式错误");
        }
    }

    /**
     * 报表生成展示页面
     */
    public function forms(){
        $purviews = $this->accountPurview->getAppData($this->account_id);//权限设备
        $this->assign('purviews',$purviews);
        $this->assign('second_nav_id',3);
        $this->display();
    }

    /**
     * 报表生成
     * 统计 一段时间内的所有记录数据
     * 统计 一段时间内的通话记录 录音时间等数据
     * 统计 一段时间内的平均记录数据
     * 统计 一段时间内的折线变化图线
     * 统计 一段时间内的平均设备记录数据
     * 统计 一段时间内的每台设备平均时间记录数据
     * @param start_date 开始时间
     * @param end_date 结束时间
     * @param ids 设备集合
     * @param type 选择查询类型 0 按天 1 按小时 2 按月 3 按年
     */
    public function doForms(){
        if(IS_POST){
            $start_date = I('start_date');
            $end_date = I('end_date');
            $ids = I('ids');
            $ids_log = implode($ids,',');
            $type = I('type','','int');
            if(!$type){
                $type = 0;
            }
            if(!$ids){
                $this->error("请选择要统计的设备");
            }

            if(!$start_date || !$end_date){
                $this->error("请选择统计时间段");
            }
            if(0 == $type){ //按天查询
                 $data = DataUtil::getAppCallCountByday($ids,$type,$start_date,$end_date);
                 $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，按天查询");
                $type = "每天";
            }else if(1==$type){
                //按小时查询 和其他查询方式不同 是直接查询记录表的
                //检测这两个时间段是不是一天
                $a = date("Y-m-d",strtotime($end_date));
                $b = date("Y-m-d",strtotime($start_date));
                if($a != $b){
                    $this->error("按小时统计只能统计同一天");
                }
                $data = DataUtil::getAppCallCountByhour($ids,$start_date,$end_date);
                $type = "每小时";
                $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，小时查询");
                $this->assign("data",$data);
                $this->assign('second_nav_id',3);
                $this->display("hour");exit;
            }else if(2 == $type){
                 $data = DataUtil::getAppCallCountByday($ids,$type,$start_date,$end_date);
                 $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，按月查询");
                $type = "每月";
            }else if(3== $type){
                 $data = DataUtil::getAppCallCountByday($ids,$type,$start_date,$end_date);
                 $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，按年查询");
                $type = "每年";
            }else{
                $this->error("查询类型错误");
            }
            
            $this->assign('data',$data);
            $this->assign('logs',$data['all']);
            $this->assign('times',$data['bytime']);
            $this->assign('device',$data['bydevice']);
            $this->assign('devicetime',$data['bydevicetime']);
            $this->assign('second_nav_id',3);
            $this->assign('type',$type);
            $this->display();
        }else{
            $this->error("请求方式错误");
        }
    }

    /**
     * 吧秒数 装换为标准格式
     */
    private  function secToTime($times){  
        $result = '00:00:00';  
        if ($times>0) {  
                $hour = floor($times/3600);  
                $minute = floor(($times-3600 * $hour)/60);  
                $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);  
                $result = $hour.':'.$minute.':'.$second;  
        }  
        return $result;  
    }
}