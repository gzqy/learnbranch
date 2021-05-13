<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use Common\Utils\DataUtil;
/**
 * 数据统计控制器
 */
class DataController extends ShopbaseController {
    protected $nav_id = 3;
    protected $second_nav = array();

    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/Data/index_devices'),'name'=>'数据总览'),
            array('id'=>2,'a'=>U('/Data/Statistics'),'name'=>'数据统计'), 
//            array('id'=>4,'a'=>U('/Data/getCallList'),'name'=>'记录查询'),
            array('id'=>3,'a'=>U('/Data/forms'),'name'=>'报表生成'), 
//            array('id'=>5,'a'=>U('/Data/xuewei'),'name'=>'学威统计'),
        ); 
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }

    private function menu_index(){
        $third_nav = array(
            array('id'=>1,'a'=>U('Data/index_devices'),'name'=>'设备总览'),
            array('id'=>2,'a'=>U('Data/index_accounts'),'name'=>'账号总览'),
            array('id'=>3,'a'=>U('Data/index_calls'),'name'=>'记录总览'),
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
        $account_devices = $this->accounts->getAccountDevicesCount($this->account_id);//权限设备总数 在线 离线
        $account_devices['round'] = 100 * round($account_devices['inline']/$account_devices['total'],2);
        //设备添加数 注册数量 未注册数量 删除数量
        $devices = DataUtil::getDeviceCount();
        //设备端口总数 平均端口数量统计
        $lines = DataUtil::getDeviceLines($devices['registered']);
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
        $account_attentios = DataUtil::getAccountAttention($accounts['total']);
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
        $logs = DataUtil::getaCallCount();
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
        $callByTime = DataUtil::getCallCountByTime();
        $callByTime['recording_time'] = round($callByTime['recording_time']/3600,2);
        $callByTime['call_time'] = round($callByTime['call_time']/3600,2);
        $callByTime['recording_time1'] = $this->secToTime($callByTime['recording_time']);
        $callByTime['call_time1'] = $this->secToTime($callByTime['call_time']);
        //统计每个月份的数据
        $monthLogs = DataUtil::getLogsByMonth();
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
 * 设备权限检测,根据DeviceCallController 中的 方法而来
     * @param id 设备id
     */
    protected function _loadDevice($id)
    {
        $auth = $this->device->checkDeviceAuth($id, $this->account_id);
        if ($id && !$auth) {
            $this->error('您没有该设备查看权限!');
        }
        $sql         = "SELECT pl.line_id,pl.device_id,d.group_id,d.code,dl.code as line_code,
                g.name as group_name,d.name as device_name,dl.portname
                FROM `account_purview_line` as pl
                inner JOIN devices as d  on pl.device_id=d.id
                inner join device_group as g on g.id=d.group_id
                inner join device_line as dl on dl.id=pl.line_id
                where account_id={$this->account_id} order by dl.device_id,dl.code asc";
        $rst         = M("", null)->query($sql);
        $deviceLines = [];
        foreach ($rst as $v) {
            $deviceLines[$v['group_id']][$v['device_id']][] = $v['line_id'];
        }
        $deviceName = array_column($rst, 'device_name', 'device_id');
        $groupName  = array_column($rst, 'group_name', 'group_id');
        $codeList   = array_column($rst, 'code', 'device_id');
        $lineName   = array_column($rst, 'portname', 'line_id');
        $lineCode   = array_column($rst, 'line_code', 'line_id');
        $this->assign('lineName', $lineName);
        $this->assign('deviceName', $deviceName);
        $this->assign('codeList', $codeList);
        $this->assign('groupName', $groupName);
        $this->assign('lineCode', $lineCode);
        $this->assign('deviceLines', $deviceLines);
    }
    /**
     * 数据统计
     */
    public function Statistics(){
        $this->_loadDevice(0);
        $purviews = $this->accountPurview->getData($this->account_id);//权限设备
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
            $type = I("type",1);
            if(1 == $type){ //原始查询方式 通过选择设备已经开始和结束时间
                $start_date = I('start_date');
                $end_date = I('end_date');
                $ids = I('ids');
                if(!$ids){
                    $this->error("请选择要统计的设备");
                }
                $data = DataUtil::getStatisByDevices($ids,$start_date,$end_date);
                $this->assign('data',$data);
                $this->assign('jdata',json_encode($data));
            }else{ //条件查询方式 通过多种条件 采用实时查询方式
                $data =  DataUtil::getStatisByConditions($this->account_id);
                $this->assign("data",$data);
                $this->assign('jdata',json_encode($data));
                $this->display('doStatistics1');exit;
            }
            $this->accountLogs->addLog("查询数据统计");
            $this->assign('second_nav_id',2);
            $this->display();
        }else{
            $this->error("请求方式错误");
        }
    }

    /**
     * 数据统计导出excel表格 
     * @param excel 1代表导出
     * @param type 1旧的查询导出 2 条件查询导出
     * @param data 要导出的数据 json处理
     */
    public function doStatistics_excel(){
        if(1 == I('excel')){
            $type = I('type',1,'int');
            $data = json_decode(htmlspecialchars_decode(I('data')),1);
            if(!$data || !is_array($data)){
                $this->error("请求错误");
            }
            if(1 == $type){
                header ( "Content-type:application/vnd.ms-excel" );  
                header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
//                $head =array('设备名称','设备分组','来电数量','去电数量','未接数量','留言数量','音频总数','视频总数','通话时长','录音时长','来电时长','去电时长');
                $head =array('设备名称','设备分组','来电数量','去电数量','未接数量','留言数量','音频总数','视频总数','录音时长','来电时长','去电时长');
                foreach($head as $k=>$v){
                    $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
                }
                $i = 0;
                $fp = fopen('php://output', 'a');
                fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
                
                foreach($data as $k => $v){
                    $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['gname']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['comeing']);
                    $excels[$i][] = iconv('utf-8','gbk',$v['outgoing']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['missed']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['audio']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['message']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['vedio']);
//                    $excels[$i][] = $v['call_time'];
                    $excels[$i][] = $v['recording_time'];
                    $excels[$i][] = $v['comeing_time'];
                    $excels[$i][] = $v['outgoing_time'];
                    
                    fputcsv($fp,$excels[$i]);
                    $i++;
                }
            }else{
                header ( "Content-type:application/vnd.ms-excel" );  
                header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
//                $head =array('设备名称','端口名称','来电数量','去电数量','未接数量','留言数量','音频总数','视频总数','通话时长','录音时长');
                $head =array('设备名称','端口名称','来电数量','去电数量','未接数量','留言数量','音频总数','视频总数','录音时长');
                foreach($head as $k=>$v){
                    $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
                }
                $i = 0;
                $fp = fopen('php://output', 'a');
                fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
                
                foreach($data['device'] as $k => $v){
                    $excels[$i][] =  iconv('utf-8','gbk',$v['device_name']);
                    $excels[$i][] =  '';
                    $excels[$i][] =  iconv('utf-8','gbk',$v['total']['coming']);
                    $excels[$i][] = iconv('utf-8','gbk',$v['total']['outing']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['total']['miss']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['total']['audio']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['total']['message']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['total']['vedio']);
//                    $excels[$i][] = $v['total']['call_time'];
                    $excels[$i][] = $v['total']['recording_time'];
                    fputcsv($fp,$excels[$i]);
                    $i++;
                    foreach($v['line'] as $line){
                        $excels[$i][] =  '';
                        $excels[$i][] =  iconv('utf-8','gbk',$line['portname']);
                        $excels[$i][] =  iconv('utf-8','gbk',$line['coming']);
                        $excels[$i][] = iconv('utf-8','gbk',$line['outing']);
                        $excels[$i][] =  iconv('utf-8','gbk',$line['miss']);
                        $excels[$i][] =  iconv('utf-8','gbk',$line['audio']);
                        $excels[$i][] =  iconv('utf-8','gbk',$line['message']);
                        $excels[$i][] =  iconv('utf-8','gbk',$line['vedio']);
//                        $excels[$i][] = $line['call_time'];
                        $excels[$i][] = $line['recording_time'];
                        fputcsv($fp,$excels[$i]);
                        $i++;
                    }
                    
                }
                $excels[$i][] =  iconv('utf-8','gbk','总计');
                $excels[$i][] =  '';
                $excels[$i][] =  iconv('utf-8','gbk',$data['total']['coming']);
                $excels[$i][] = iconv('utf-8','gbk',$data['total']['outing']);
                $excels[$i][] =  iconv('utf-8','gbk',$data['total']['miss']);
                $excels[$i][] =  iconv('utf-8','gbk',$data['total']['audio']);
                $excels[$i][] =  iconv('utf-8','gbk',$data['total']['message']);
                $excels[$i][] =  iconv('utf-8','gbk',$data['total']['vedio']);
//                $excels[$i][] = $line['call_time'];
                $excels[$i][] = $line['recording_time'];
                fputcsv($fp,$excels[$i]);
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
     * 记录查询 生成列表
     */
    public function getCallList(){
        $purviews = $this->accountPurview->getData($this->account_id);//权限设备
        $this->assign('purviews',$purviews);
        $this->assign('second_nav_id',4);
        $this->display();
    }

    /**
     * 记录查询结果列表
     */
    public function dogetCallList(){
        $type = I("type",1);
        if(1 == $type){
            $ids = I('ids');
            if(!$ids){
                $this->error("请选择要统计的设备");
            }
            if(!is_array($ids)){
                $ids = json_decode(htmlspecialchars_decode($ids),1);
            }
            $data = DataUtil::getCallList($this->account_id,$ids);
        }else{
            $data = DataUtil::getCallList($this->account_id);
        }
        $this->assign('second_nav_id',4);
        $this->assign('Page',$data['page']);
        $this->assign('data',$data['callList']);
        $this->display();
    }

    /**
     * 查询结果导出
     * @param excel_type 1 搜索全部 0 搜索本页
     */
    public function dogetCallList_excel(){
        $ids = I('ids');
        $excel_type = I('excel_type',0,'int');
        if(!is_array($ids) && $ids){
            $ids = json_decode(htmlspecialchars_decode($ids),1);
        }
        if($ids){
            $data = DataUtil::getCallList($this->account_id,$ids,$excel_type);
        }else{
            $data = DataUtil::getCallList($this->account_id,null,$excel_type);
        }
        $data = $data['callList'];
        if(!$data){
            $this->error('没有查询到数据');
        }
        ini_set('memory_limit', '256M');
        ini_set("max_execution_time", "3600");
        header ( "Content-type:application/vnd.ms-excel" );  
        header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
        $head =array('设备名称','端口名称','录音类型','是否关注','记录日期','录音时长','通话时长','通话号码');
        foreach($head as $k=>$v){
            $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
        }
        $i = 0;
        $fp = fopen('php://output', 'a');
        fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
        foreach($data as $k => $v){
            $excels[$i][] =  iconv('utf-8','gbk',$v['device_name']);
            $excels[$i][] =  iconv('utf-8','gbk',$v['portname']);
            $excels[$i][] = iconv('utf-8','gbk',D("Common/DeviceCall")->type($v['type1']));
            if(1==$v['is_flag']){
                $excels[$i][] = iconv('utf-8','gbk','关注');
            }else{
                $excels[$i][] = iconv('utf-8','gbk','未关注');
            }
            $excels[$i][] = $v['call_date'];
            $excels[$i][] = D("Common/DeviceCall")->getTime($v['call_time']);
            $excels[$i][] = D("Common/DeviceCall")->getTime($v['recording_time']);
            $excels[$i][] = $v['tel'];
            fputcsv($fp,$excels[$i]);
            $i++;
        }
        $this->accountLogs->addLog("导出设备状态表格");
        unset($excels);  
        ob_flush();  
        flush();
    }

    /**
     * 报表生成展示页面
     */
    public function forms(){
        $this->_loadDevice(0);
        $purviews = $this->accountPurview->getData($this->account_id);//权限设备
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
//            $ids = I('lineIds');
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
                 $data = DataUtil::getCallCountByday($ids,$type,$start_date,$end_date);
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
                $data = DataUtil::getCallCountByhour($ids,$start_date,$end_date);
                $type = "每小时";
                $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，小时查询");
                $this->assign("data",$data);
                $this->assign('second_nav_id',3);
                $this->display("hour");exit;
            }else if(2 == $type){
                 $data = DataUtil::getCallCountByday($ids,$type,$start_date,$end_date);
                 $this->accountLogs->addLog("生成报表，查询条件：设备id{$ids_log},起始时间：{$start_date},结束时间：{$end_date}，按月查询");
                $type = "每月";
            }else if(3== $type){
                 $data = DataUtil::getCallCountByday($ids,$type,$start_date,$end_date);
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
     * 学威统计
     */
    public function xuewei(){
        $purviews = $this->accountPurview->getData($this->account_id);//权限设备
        $this->assign('purviews',$purviews);
        $this->assign('second_nav_id',5);
        $this->display();
    }

    /**
     * 学威统计处理
     */
    public function doXuewei(){
        if(IS_POST){
            $start_date = I('start_date');
            $end_date = I('end_date');
            $score = I('pingfen');
            $ids = I('ids');
            $ids_log = implode($ids,',');
            if(!$ids){
                $this->error("请选择要统计的设备");
            }

            if(!$start_date || !$end_date){
                $this->error("请选择统计时间段");
            }
            $scores = [0,1,2,3,4,5,6];
            if(!in_array($score, $scores)){
                $this->error('星级选择错误');
            }
            $data = DataUtil::xuewei($ids,$start_date,$end_date,$score);
            if($data){
                $this->assign('data',$data);
                $this->assign('gdata',json_encode($data));
            }
            $this->assign('start_date',$start_date);
            $this->assign('end_date',$end_date);
            $this->assign('pingfen',$score);
            $this->assign('second_nav_id',5);
            $this->display();
        }else{
            $this->error('请求方式错误');
        }
    }

    /**
     * 某个端口的通话记录
     */
    public function xuewei_detail(){
        $start_date = I('start_date');
        $end_date = I('end_date');
        $score = I('pingfen');
        $device_id = I('device_id');
        $line_id = I('line_id');

        if(!$device_id || !$line_id || !$start_date || !$end_date){
            $this->error('条件错误');
        }
        $scores = [0,1,2,3,4,5,6];
        if(!in_array($score, $scores)){
            $this->error('星级选择错误');
        }
        $data = DataUtil::xuewei_detail($device_id,$line_id,$start_date,$end_date,$score);
        $this->assign('second_nav_id',5);
        $this->assign('Page',$data['page']);
        $this->assign('data',$data['callList']);
        $this->display();
    }

    /**
     * 学威统计导出
     */
    public function doXuewei_excel(){
        if(IS_POST){
            $data = json_decode(htmlspecialchars_decode(I('data')),1);
            if(!$data || !is_array($data)){
                $this->error("请求错误");
            }
                header ( "Content-type:application/vnd.ms-excel" );  
                header ( "Content-Disposition:filename=" . iconv ( "UTF-8", "GB18030", "query_user_info" ) . ".csv" );  
                $head =array('分公司','销售姓名','来电数量','来电时长(s)','去电数量','去电时长(s)','有效电话个数','有效电话时长(s)','无效电话个数','无效电话时长(s)','首个有效电话时间');
                foreach($head as $k=>$v){
                    $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
                }
                $i = 0;
                $fp = fopen('php://output', 'a');
                fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
                
                foreach($data as $k => $v){
                    $excels[$i][] =  iconv('utf-8','gbk',$v['name']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['portname']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['come_count']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['come_time']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['out_count']);
                    $excels[$i][] = iconv('utf-8','gbk',$v['out_time']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['youxiao_count']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['youxiao_time']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['wuxiao_count']);
                    $excels[$i][] =  iconv('utf-8','gbk',$v['wuxiao_time']);
                    $excels[$i][] =  iconv('utf-8','gbk',date('Y-m-d H:i:s',$v['first']));
                    fputcsv($fp,$excels[$i]);
                    $i++;
                }
            $this->accountLogs->addLog("导出数据统计表格");
            unset($excels);  
            ob_flush();  
            flush();
        }else{
            $this->error('请求方式错误');
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