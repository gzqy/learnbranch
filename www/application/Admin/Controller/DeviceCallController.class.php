<?php

namespace Admin\Controller;

use Common\Controller\ShopbaseController;
use Common\Utils\Excel_XMLUtil;

/**
 * 设备控制器
 */
class DeviceCallController extends ShopbaseController
{
    protected $nav_id = 14;
    protected $second_nav = array();
    
    function _initialize()
    {
        parent::_initialize();
        $second_nav = array(
            array('id' => 1, 'a' => '/Device/index', 'name' => '设备列表'),
            array('id' => 2, 'a' => '/Device/Unregistered', 'name' => '未注册设备'),
            array('id' => 3, 'a' => '/Device/add', 'name' => '添加设备'),
            array('id' => 4, 'a' => '/Device/GroupList', 'name' => '设备组列表'),
            array('id' => 5, 'a' => '/Device/GroupAdd', 'name' => '添加设备组'),
        );
        $this->assign('nav_id', $this->nav_id);
        $this->assign('second_nav', $second_nav);
    }
    
    /**
     * 设备权限检测
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
        //        dump($deviceName);
        //        dump($groupName);
        //        dump($deviceLines);die;
        //        //查询设备信息
        //        $device = $this->device->alias('a')->join("left join device_stat b on a.id = b.device_id")->field("a.name,a.add_time,a.line,b.*")->where("a.id = {$id}")->select();
        //        //cpu 内存 存储百分比
        //        $device = $device[0];
        //        $device['id'] = $id;
        //        if(!$device['cpu']){
        //            $device['cpu'] = '未知';
        //        }else{
        //            $device['cpu'] = $device['cpu'].'%';
        //        }
        //        if(!$device['totalstore'] || 0==$device['totalstore']){
        //            $device['store'] = '未知';
        //        }else{
        //            if($device['totalstore'] < $device['totalfreestore']){
        //                $device['store'] = '未知';
        //            }else{
        //                $device['store'] = round(($device['totalstore'] - $device['totalfreestore']) / $device['totalstore'],2)*100;
        //                $device['store'] .= "%";
        //            }
        //        }
        //        if(!$device['totalmem'] || 0==$device['totalmem']){
        //            $device['mem'] = '未知';
        //        }else{
        //            if($device['totalmem'] < $device['totalfreemem']){
        //                $device['mem'] = '未知';
        //            }else{
        //                $device['mem'] = round(($device['totalmem'] - $device['totalfreemem']) / $device['totalmem'],2)*100;
        //                $device['mem'] .= "%";
        //            }
        //        }
        //
        //        $r = M('account_purview_line',null)->
        //        field('sum(comeing) as comeing,sum(outgoing) as outgoing,sum(missed) as missed,sum(`case`) as case1')->
        //        join('device_line on account_purview_line.line_id=device_line.id')->
        //        where(['account_purview_line.account_id'=>$this->account_id,'account_purview_line.device_id'=>$id])->find();
        //        $device['comeing'] = $r['comeing'];
        //        $device['outgoing'] =  $r['outgoing'];
        //        $device['missed'] =  $r['missed'];
        //        $device['case'] =  $r['case1'];
        //
        //        $this->assign('device',$device);
        //        $this->assign('this_device',$id);
    }
    
    /**
     * 获取设备组和权限设备
     */
    private function GetDevicesGroups()
    {
        //获取所有设备组
        $groups = $this->deviceGroupModel->getOption();
        //获取当前账号有权限的设备并按组分好
        $purviews = $this->accountPurview->getData($this->account_id);
        $this->assign('groups', $groups);
        $this->assign('all_purviews', $purviews); //将数据输送模板
    }
    
    /**
     * 设备状态前置方法
     */
    private function __beforeStatus($id = null)
    {
        $this->assign('nav_id', 14);
    }
    
    /**
     * 设备状态
     * @param status 状态 0 全部 1在线 2 离线
     * @param name 设备名称
     * @param code 设备编码
     * @param group_id 设备组id
     * @param start_date 注册最小时间
     * @param end_date 注册最大时间
     * @param keywords 关键字
     */
    function status()
    {
        self::__beforeStatus();
        self::GetDevicesGroups();
        $group_id   = I('group_id');
        $keywords   = I('keywords');
        $name       = I('name');
        $code       = I('code');
        $start_date = I('start_date');
        $end_date   = I('end_date');
        $status     = I('status');
        
        $data = $this->device->getDevicesList();
        foreach ($data['data'] as $k => $v) {
            if (!$v) {
                continue;
            }
            $r                            = M('account_purview_line', null)->
            field('sum(comeing) as comeing,sum(outgoing) as outgoing,sum(missed) as missed')->
            join('device_line on account_purview_line.line_id=device_line.id')->
            where(['account_purview_line.account_id' => $this->account_id, 'account_purview_line.device_id' => $v['id']])->find();
            $data['data'][$k]['comeing']  = $r['comeing'];
            $data['data'][$k]['outgoing'] = $r['outgoing'];
            $data['data'][$k]['missed']   = $r['missed'];
        }
        $this->assign('count', $data['status']);
        $this->assign('data', $data['data']);
        $this->assign('keywords', $keywords);
        $this->assign('group_id', $group_id);
        $this->assign('status', $status ? $status : 0);
        $this->assign('name', $name);
        $this->assign('code', $code);
        $this->assign('start_date', $start_date);
        $this->assign('end_date', $end_date);
        $this->assign('second_nav_id', 1);
        $this->assign('Page', $data['page']);
        $this->assign('gdata', urlencode(json_encode($data['data'])));
        if (0 == $status) {
            $con = "全部设备";
        } else if (1 == $status) {
            $con = "在线设备";
        } else if (3 == $status) {
            $con = "离线设备";
        }
        $this->accountLogs->addLog("查看设备状态-{$con} 查询条件:设备名称 {$name},分组id {$group_id},关键字 {$keywords},设备编码 {$code},设备时间最小值 {$start_date},设备时间最大值 {$end_date}");
        $this->display();
    }
    
    /**
     * excel 导出设备列表
     */
    public function index_excel()
    {
        if (IS_POST) {
            if (1 == I('excel')) {
                $data = json_decode(htmlspecialchars_decode(urldecode(I('data'))), 1);
                if (!$data) {
                    $this->ajaxReturn(array('status' => 100, 'msg' => '没有数据可导出'));
                }
                $title  = "设备状态";
                $header = array(
                    array('name', '设备名称'),
                    array('status', '设备状态'),
                    array('attention', '是否关注'),
                    array('comeing', '来电数量'),
                    array('outgoing', '去电数量'),
                    array('missed', '未接数量'),
                );
                foreach ($data as $k => $v) {
                    $data[$k]['attention'] = $v['attention'] ? '已关注' : '未关注';
                    $data[$k]['status']    = $v['status'] ? '在线' : '离线';
                    $data[$k]['comeing']   = $v['comeing'] ? $v['comeing'] : 0;
                    $data[$k]['outgoing']  = $v['outgoing'] ? $v['outgoing'] : 0;
                    $data[$k]['missed']    = $v['missed'] ? $v['missed'] : 0;
                }
                $path = $this->exportExcel($title, $header, $data);
                $this->ajaxReturn(array('status' => 200, 'url' => $path));
            } else {
                exit('非法请求');
            }
        } else {
            exit('非法请求');
        }
    }
    
    /**
     * 具体某个设备端口情况
     * @param id 设备id
     */
    public function showLine($id = null)
    {
        self::__beforeStatus($id);
        self::_loadDevice($id); //进行设备权限检测 不在权限组内 不能查看
        //获取设备权限内的端口信息
        $lines = $this->accountPurviewLine->getDeviceAuthLines($id, $this->account_id);
        $con   = [
            'account_id' => $this->account_id,
            'device_id'  => $id
        ];
        //端口状态处理
        if ($lines) {
            $purviews = M('account_purview_line')->field('line_id')->where($con)->select();
            $purviews = array_column($purviews, 'line_id');
            foreach ($lines as $k => $v) {
                if (!in_array($v['id'], $purviews)) {
                    unset($lines[$k]);
                    continue;
                }
                $lines[$k] = D("Common/DeviceLine")->forData($v);
            }
        }
        $this->accountLogs->addLog("查看设备端口，设备id：{$id}");
        $lines && $this->assign('data', $lines);
        $this->assign('second_nav_id', 0);
        $this->display();
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
    public function showLogs($id = null)
    {
        self::__beforeStatus($id);
        self::_loadDevice($id); //设备信息检测
    
        $limit              = I('limit') > 0 ? I('limit') : 20;
        $limit              = min($limit, 5000);
        $start_date      = I('start_date');
        $end_date        = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time   = I('end_call_time');
        $max_recording   = I('max_recording', '', 'int');
        $min_recording   = I('min_recording', '', 'int');
        $tel             = I('tel');
        $search_line     = I('search_line');
        $search_type     = I('search_type');
        $keywords        = I('keywords');
        $sort            = I('sort');
        $id              = I('id', '', 'int');
        $lineIds         = I('lineIds');
        $recentDate      = I('recent_date');
        //        dump($lineIds);die;
        $callnotes = I('callnotes');
    
        $recentDateList = array_combine(range(1,4),['今天','最近七天','本月','今年']);
        $rDate          = array_combine(range(1, 4), [
            [date('Y-m-d') . " 00:00:00", date('Y-m-d', strtotime('1 day')) . " 00:00:00"],
            [date('Y-m-d', strtotime('-7 days')) . " 00:00:00", date('Y-m-d', strtotime('1 day')) . " 00:00:00"],
            [date('Y-m') . "-01 00:00:00", date('Y-m-d', strtotime('1 day')) . " 00:00:00"],
            [date('Y') . "-01-01 00:00:00", date('Y-m-d', strtotime('1 day')) . " 00:00:00"],
        ]);
        $checkedDeviceId    = [];
        $checkedDeviceGroup = [];
        if ($lineIds) {
            $lineIds            = is_array($lineIds) ? implode(",", $lineIds) : $lineIds;
            $sql                = "select * from device_line as l INNER JOIN devices as g
            on l.device_id=g.id where l.id in ({$lineIds})";
            $rst                = M('', null)->query($sql);
            $checkedDeviceId    = array_column($rst, 'device_id');
            $checkedDeviceGroup = array_column($rst, 'group_id');
        }
        $data = $this->deviceCall->getCallList($id, $lineIds);//通话记录列表
        //        var_dump($data);die;
        $data1 = $data['data'];
        //检测是否已经关注记录
        foreach ($data1 as $k => $v) {
            if (M("device_call_flag")->where("call_id = {$v['id']} and account_id = {$this->account_id}")->find()) {
                $data1[$k]['is_flag'] = 1;
            } else {
                $data1[$k]['is_flag'] = 0;
            }
            //检测是否有文件
            if (!$v['files']) {
                $data1[$k]['files'] = 0;
            } else {
                $files = D("Common/DeviceCall")->getFileDir($v['files']) . D("Common/DeviceCall")->replaceForFilename($v['files']);
                if (!is_file($files)) {
                    $data1[$k]['files'] = 0;
                } else {
                    $data1[$k]['files'] = 1;
                }
            }
        }
        //查询设备的端口和录音类型判断
        //        $lines = D("Common/DeviceLine")->getLines($id);
        $lines     = [];
        $lines_ids = M('account_purview_line', null)->field('line_id')->where(['account_id' => $this->account_id, 'device_id' => $id])->select();
        if ($lines_ids) {
            $lines_ids = array_column($lines_ids, 'line_id');
            $lines     = M('device_line', null)->where(['id' => ['in', $lines_ids]])->order("code asc")->select();
        }
        $linesOption     = '';
        $search_line_txt = '';
        //        {fruitID:1,fruitName:"苹果"},
        $search_line_arr = explode(',', $search_line);
        foreach ($lines as $v) {
            if (in_array($v['code'], $search_line_arr)) {
                $search_line_txt .= $v['portname'] . ',';
            }
            $linesOption .= '{fruitID:' . $v['code'] . ',';
            $linesOption .= 'fruitName:"' . $v['portname'] . '"},';
        }
        $linesOption     = rtrim($linesOption, ',');
        $search_line_txt = rtrim($search_line_txt, ',');
        $content         = "查询设备记录，设备id {$id}";
        $this->accountLogs->addLog($content);
        
        $start_date && $this->assign('start_date', $start_date);
        $end_date && $this->assign('end_date', $end_date);
        $start_call_time && $this->assign('start_call_time', $start_call_time);
        $end_call_time && $this->assign('end_call_time', $end_call_time);
        $search_line && $this->assign('search_line', $search_line);
        $search_type && $this->assign('search_type', $search_type);
    
        $importantLevelList=array_combine(range(1,5),['★','★★','★★★','★★★★','★★★★★']);
        $sourceList = M('contact_source',null)->select();
        $groups   = D("Common/ContactGroup")->getOption();//通讯组
        $this->assign('id',$id);
        $this->assign('groups',$groups);
        $this->assign('rDate', $rDate);
        $this->assign('recentDateList', $recentDateList);
        $this->assign('recentDate', $recentDate);
        $this->assign('keywords', $keywords);
        $this->assign('tel', $tel);
        $this->assign('callnotes', $callnotes);
        $this->assign('max_recording', $max_recording);
        $this->assign('min_recording', $min_recording);
        $this->assign('data', $data1);
        $this->assign('linesOption', $linesOption);
        $this->assign('search_line', $search_line);
        $this->assign('limit', $limit);
        $this->assign('lineIds', !is_array($lineIds) ? explode(',', $lineIds) : $lineIds);
        $this->assign('checkedDeviceId', $checkedDeviceId);
        $this->assign('checkedDeviceGroup', $checkedDeviceGroup);
        $this->assign('search_line_txt', $search_line_txt);
        include_once SITE_PATH . 'application/Admin/Controller/ConcatController.class.php';
        $this->assign('feedbackType',ConcatController::$feedbackTypeList );
        $data2 = $data1;
        foreach ($data2 as $k => $v) {
            foreach ($v as $x => $y) {
                if ('type' == $x) {
                    unset($data2[$k][$x]);
                }
            }
        }
        $this->assign('gdata', json_encode($data2));
        $this->assign('Page', $data['page']);
        $this->assign('importantLevelList',$importantLevelList);
        $this->assign('sourceList',$sourceList);
        $this->assign('lines', $lines);
        $this->display();
    }
    
    /**
     * 记录csv导出
     */
    public function logs_excel()
    {
        if (IS_POST) {
            if (1 == I('excel')) {
                $data = json_decode(htmlspecialchars_decode(I('data')), 1);
                if (!$data) {
                    $this->ajaxReturn(array('status' => 100, 'msg' => '没有数据可导出'));
                }
                $title  = "设备记录";
                $header = array(
                    array('portname', '端口名称'),
                    array('type', '录音类型'),
                    array('is_flag', '是否关注'),
                    array('call_date', '记录日期'),
                    array('call_time', '录音时长'),
                    array('recording_time', '通话时长'),
                    array('tel', '通话号码'),
                );
                foreach ($data as $k => $v) {
                    $data[$k]['is_flag'] = $v['is_flag'] ? '已关注' : '未关注';
                    $data[$k]['type']    = D("Common/DeviceCall")->type($v['type1']);
                }
                $path = $this->exportExcel($title, $header, $data);
                $this->ajaxReturn(array('status' => 200, 'url' => $path));
            } else {
                exit('非法请求');
            }
        } else {
            exit('非法请求');
        }
    }
    
    /**
     * 设备事件查询
     * @param id 设备id
     */
    public function showCase($id = null)
    {
        self::__beforeStatus($id);
        self::_loadDevice($id);
        $p     = I('p', '', 'int') ? I('p', '', 'int') : 1;
        $limit = I('limit', '', 'int') ? I('limit', '', 'int') : 20;
        //分页
        $count = M("device_call")->where("device_id = {$id}")->count();
        $this->accountLogs->addLog("查看设备事件 设备id：{$id}");
        if (0 == $count || !$count) {
            $page = $this->page($count, $limit, array('p' => $p, "id" => $id));
            $data = M("device_call")->where("device_id = {$id}")->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
            if ($data) {//获取事件类型
                foreach ($data as $k => $v) {
                    $data[$k]['type_name'] = D("Common/DeviceCase")->type($v['type']);
                }
            }
            
            $this->assign('id', $id);
            $this->assign('data', $data);
            $this->assign('Page', $page->show());
            $this->display();
        } else {
            $this->assign('id', $id);
            $this->display();
        }
    }
    
    /**
     * 记录评分
     */
    public function saveScore()
    {
        if (IS_AJAX) {
            $id    = I('id');
            $score = I('score');
            if (!$id || empty($score)) {
                echo json_encode(array('status' => 300, 'msg' => '缺少参数'));
                exit;
            }
            $array = [1, 2, 3, 4, 5];
            if (!in_array($score, $array)) {
                echo json_encode(array('status' => 300, 'msg' => '分数错误'));
                exit;
            }
            $call = M('DeviceCall')->where('id = ' . $id)->find();
            if (!$call) {
                echo json_encode(array('status' => 300, 'msg' => '记录错误'));
                exit;
            }
            $data['score'] = "{$score}";
            M('DeviceCall')->where('id=' . $id)->save($data);
            echo json_encode(array('status' => 200, 'msg' => '记录评分成功'));
            exit;
        } else {
            echo json_encode(array('status' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 关注设备
     * @param id 设备id
     * @param st 1为关注 0 取消关注
     */
    function setAttention()
    {
        if (IS_AJAX) {
            $id            = I('id');
            $st            = I('st');
            $max_attention = C('max_attention');//最大关注设备数量
            if ($st == 1 && $max_attention) { //检测是否关注数超出上限
                $count = $this->accountPurview->where("account_id = {$this->account_id} and attention = 1")->count();
                if ($count >= $max_attention) {
                    echo json_encode(array('status' => 300, 'msg' => '最多可关注' . $max_attention . '个设备'));
                    exit;
                }
            }
            
            //查找当前设备权限表
            $info = $this->accountPurview->where(array('account_id' => $this->account_id, 'device_id' => $id))->find();
            if ($info) { //更新数据 关注或者取消关注
                $data              = array();
                $time              = time();
                $data['attention'] = $st;
                $data['upd_time']  = date('Y-m-d H:i:s', $time);
                $data['add_time']  = date('Y-m-d H:i:s', $time);
                $this->accountPurview->where("id = {$info['id']}")->save($data);
                if (0 == $st) {
                    $this->accountLogs->addLog("取消关注设备，设备id：{$id}");
                } else {
                    $this->accountLogs->addLog("关注设备，设备id：{$id}");
                }
                
                echo json_encode(array('status' => 200, 'msg' => '操作成功'));
                exit;
            } else {
                echo json_encode(array('status' => 300, 'msg' => '不是权限设备，无法关注'));
                exit;
            }
        } else {
            echo json_encode(array('status' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 关注记录（服务端每接收一次客户端请求视为一次事件，作为一条记录）
     * @param id 记录id
     * @param st 1 关注 0 取消关注
     */
    function setLogAttention()
    {
        if (IS_AJAX) {
            $id = I('id', '', 'int');
            $st = I('st');
            if (!$id || empty($id)) {
                echo json_encode(array('code' => 300, 'msg' => '请求参数错误'));
                exit;
            }
            $maxNum = C('max_log_attention');//最大关注数量 0不限
            if ($maxNum) {
                $count = M("device_call_flag")->where("account_id = {$this->account_id}")->count();
                if ($count > $maxNum) {
                    echo json_encode(array('code' => 300, 'msg' => '最多可标注' . $max_attention . '个记录'));
                    exit;
                }
            }
            //处理关注或者取消关注
            $is = M("device_call_flag")->where("call_id = {$id} and account_id = {$this->account_id}")->find();
            //检测记录是否存在
            $log = M("device_call")->where("id = {$id}")->find();
            if (!$log) {
                echo json_encode(array('code' => 300, 'msg' => '记录不存在'));
                exit;
            }
            if (1 == $st) { //关注
                if ($is) {
                    echo json_encode(array('code' => 300, 'msg' => '记录已经关注'));
                    exit;
                } else {
                    $time = time();
                    //处理
                    $data = array(
                        'device_id'  => $log['device_id'],
                        'line_id'    => $log['line_id'],
                        'account_id' => $this->account_id,
                        'call_id'    => $id,
                        'add_time'   => $time,
                    );
                    if (M("device_call_flag")->add($data)) {
                        $this->accountLogs->addLog("关注设备记录，记录id：{$id}");
                        echo json_encode(array('code' => 200, 'msg' => '记录关注成功'));
                        exit;
                    } else {
                        echo json_encode(array('code' => 300, 'msg' => '记录关注失败'));
                        exit;
                    }
                }
            } else {//取消关注
                if (M('device_call_flag')->where("call_id = {$id} and account_id = {$this->account_id}")->delete()) {
                    $this->accountLogs->addLog("取消关注设备记录 记录id：{$id}");
                    echo json_encode(array('code' => 200, 'msg' => '记录取消关注成功'));
                    exit;
                } else {
                    echo json_encode(array('code' => 300, 'msg' => '记录取消关注失败'));
                    exit;
                }
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
        
    }
    
    /**
     * 修改端口备注名称
     * @param
     */
    function SavePortName()
    {
        if (IS_AJAX) {
            $data = I('data');
            if (!$data) {
                echo json_encode(array('code' => 300, 'msg' => '请填写参数'));
                exit;
            }
            if (!$data['device_id'] || !$data['code'] || !$data['PortName']) {
                echo json_encode(array('code' => 300, 'msg' => '请填写端口名称'));
                exit;
            }
            //修改名称
            if (M('device_line')->where(array('device_id' => $data['device_id'], 'code' => $data['code']))->save(array('PortName' => $data['PortName']))) {
                $this->accountLogs->addLog("修改设备端口名称，设备id:{$data['device_id']},端口：{$data['code']},端口名称：{$data['PortName']}");
                echo json_encode(array('code' => 200, 'msg' => '修改端口备注成功'));
                exit;
            } else {
                echo json_encode(array('code' => 300, 'msg' => '修改端口备注失败'));
                exit;
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 备注记录
     */
    function doSaveCallNote()
    {
        $_model = new \Models\DeviceCall();
        if (empty($_model)) {
            \Esy\View::json(array(
                'status' => 300,
                'msg'    => 'Err!',
            ));
        }
        $data     = \Esy\Requests::post('data');
        $required = \Esy\Requests::post('required');
        while (list($key, $val) = @each($required)) {
            if ($val == 1 && empty($data[$key])) {
                \Esy\View::json(array(
                    'status' => 300,
                    'msg'    => '请输入完整的资料!',
                ));
            }
        }
        $_data = $_model->where("id", $data['id'])->first();
        $time  = time();
        if ($_data) {
            $_data->note            = $data['note'];
            $_data->note_time       = $time;
            $_data->note_account_id = $this->account_id;
            
            $_data->keywords = $data['note'] . ',' . $_data->tel . ',' . $_data->extension . ',' . $_data->PortName . ',' . $_data->contact_name . ',' . $_data->files;
            $_data->save();
        }
        $_device = \Models\Device::where('id', $_data->device_id)->first();
        $msg     = \Models\DeviceCall::type($_data->type) . '  [设备名称: ' . $_device->name . '. 设备端口:' . $_data->line_id . '. 记录时间:' . $_data->call_date . '. 备注内容: ' . $data['note'] . ']';
        \Models\AccountLogs::addLog('备注 ' . ' ' . $msg);
        $msg_type = empty($data['id']) ? '新增' : '保存';
        \Esy\View::json(array(
            'status' => 200,
            'msg'    => $msg_type . ' 成功!',
            'id'     => $id,
        ));
    }
    
    /**
     * excel 导出监听记录
     * @param ids 要到出的记录id集合
     * @param to_all 全部导出
     * @param id 设备id
     */
    function dotoExcel($id = null)
    {
        $ids    = \Esy\Requests::post('ids');
        $to_all = \Esy\Requests::post('to_all');
        $device = \Models\Device::getAn($id);
        if (empty($ids) && empty($to_all)) {
            \Esy\View::json(array(
                'status' => 300,
                'msg'    => '请选择需要导出的记录!',
            ));
        }
        ini_set("max_execution_time", "3600");
        //要查询数据的where条件
        if ($to_all && $id) {
            $_models = self::getExp($id);
        } else {
            $_models = new \Models\DeviceCall();
            $_models = $_models->whereIn("id", $ids)->orderBy("add_time", 'DESC');
        }
        //计算总数
        $count = $_models->count('id');
        if (0 >= $count) {
            $this->redirect("logs");
        }
        $file_name = empty($file_name) ? $device['name'] . '-' . date("Ymd") : $file_name;
        $mark      = $filename;
        // 解决IE浏览器输出中文名乱码的bug
        if (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])) {
            $file_name = urlencode($file_name);
            $file_name = iconv('UTF-8', 'GBK//IGNORE', $file_name);
        }
        $file_name = $file_name . '.csv';
        // header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        // header('Content-Disposition: attachment;filename="' . $fileName . '"');
        // header('Cache-Control: max-age=0');
        $sqlLimit = 100000;//每次只从数据库取100000条以防变量缓存太大
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        // buffer计数器
        $cnt         = 0;
        $fileNameArr = array();
        //处理头部文件
        $head[] = "序号";
        $head[] = "端口";
        $head[] = "端口名称";
        $head[] = "录音类型";
        $head[] = "日期时间";
        $head[] = "录音时间";
        $head[] = "通话时间";
        $head[] = "铃声";
        $head[] = "号码";
        $head[] = "联系人";
        $head[] = "星标";
        $head[] = "备注";
        $head[] = "录音文件";
        foreach ($head as $k => $v) {
            $head[$k] = iconv('utf-8', 'gbk', $v);//CSV的Excel支持GBK编码，一定要转换，否则乱码
        }
        $purview_device_line = \Models\AccountPurviewLine::getData($this->account_id, $id);
        for ($i = 0; $i < ceil($count / $sqlLimit); $i++) {
            $fp = fopen($mark . '_' . $i . '.csv', 'w'); //生成临时文件
            //chmod('attack_ip_info_' . $i . '.csv',777);//修改可执行权限
            $fileNameArr[] = $mark . '_' . $i . '.csv';
            
            fputcsv($fp, $head);// 将数据通过fputcsv写到文件句柄
            $data = $_models->skip($i * $sqlLimit)->take($sqlLimit)->get()->toarray(); //每次查询10w数据
            foreach ($data as $k => $val) {
                $dataarr = array();
                $cnt++;
                if ($limit == $cnt) {
                    //刷新一下输出buffer，防止由于数据过多造成问题
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                //数据处理
                if (empty($purview_device_line[$val['line_id']]) && $id) {
                    continue;
                }
                if ($val['PortName']) {
                    $PortName = $val['PortName'];
                } else {
                    $PortName = \Models\DeviceLine::getPortName($val['device_id'], $val['line_id']);
                }
                if ($val['contact_name']) {
                    $contact_name = $val['contact_name'];
                } else {
                    $contacts     = \Models\Contact::getContact($val['tel'], $val['contact_id']);
                    $contact_name = is_array($contacts) ? $contacts['name'] : $contacts;
                }
                $audio_file = \Esy\Config::getUrl('home_url') . '/index/DownloadLog/?id=' . $val['id'];;
                $logAttention = \Models\DeviceCallFlag::isAttention($this->account_id, $val['id']);
                $dataarr[]    = $k;
                $dataarr[]    = sprintf("%04d", $val['line_id']);
                $dataarr[]    = iconv('utf-8', 'gbk', $PortName);
                $dataarr[]    = iconv('utf-8', 'gbk', \Models\DeviceCall::type($val['type']));
                $dataarr[]    = $val['call_date'];
                $dataarr[]    = \Models\DeviceCall::getTime($val['recording_time']);
                $dataarr[]    = \Models\DeviceCall::getTime($val['call_time']);
                $dataarr[]    = $val['rings_number'];
                $dataarr[]    = $val['tel'];
                $dataarr[]    = $contact_name;
                $dataarr[]    = iconv('utf-8', 'gbk', ($logAttention == true) ? '是' : '否');
                $dataarr[]    = $val['note'];
                $dataarr[]    = $audio_file;
                fputcsv($fp, $dataarr);
                unset($dataarr);
            }
            fclose($fp);  //每生成一个文件关闭
        }
        $zip      = new ZipArchive;
        $filename = $filename . ".zip";
        $zip->open($filename, ZipArchive::CREATE);   //打开压缩包
        
        foreach ($fileNameArr as $file) {
            $zip->addFile($file, basename($file));   //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
        foreach ($fileNameArr as $file) {
            unlink($file); //删除csv临时文件
        }
        //输出压缩文件提供下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
        header("Content-Type: application/zip"); // zip格式的
        header("Content-Transfer-Encoding: binary"); //
        header('Content-Length: ' . filesize($filename)); //
        @readfile($filename);//输出文件;
        unlink($filename); //删除压缩包临时文件
        \Models\AccountLogs::addLog('下载记录');
        exit();
        \Esy\View::json(array(
            'status' => 200,
            'msg'    => '记录发送成功!',
        ));
    }
    
    /**
     * php导出csv 方法
     * @param head 文件头
     * @param data 数据
     * @param filename 文件名
     */
    function putCsv(array $head, $data, $mark = 'attack_ip_info', $fileName = "test.csv")
    {
        set_time_limit(0);
        $sqlCount = $data->count();//总条数
        // 输出Excel文件头，可把user.csv换成你要的文件名
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $sqlLimit = 100000;//每次只从数据库取100000条以防变量缓存太大
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        // buffer计数器
        $cnt         = 0;
        $fileNameArr = array();
        // 逐行取出数据，不浪费内存
        for ($i = 0; $i < ceil($sqlCount / $sqlLimit); $i++) {
            $fp = fopen($mark . '_' . $i . '.csv', 'w'); //生成临时文件
            //     chmod('attack_ip_info_' . $i . '.csv',777);//修改可执行权限
            $fileNameArr[] = $mark . '_' . $i . '.csv';
            // 将数据通过fputcsv写到文件句柄
            fputcsv($fp, $head);
            $dataArr = $data->offset($i * $sqlLimit)->limit($sqlLimit)->get()->toArray();
            foreach ($dataArr as $a) {
                $cnt++;
                if ($limit == $cnt) {
                    //刷新一下输出buffer，防止由于数据过多造成问题
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                fputcsv($fp, $a);
            }
            fclose($fp);  //每生成一个文件关闭
        }
        //进行多个文件压缩
        $zip      = new ZipArchive();
        $filename = $mark . ".zip";
        $zip->open($filename, ZipArchive::CREATE);   //打开压缩包
        foreach ($fileNameArr as $file) {
            $zip->addFile($file, basename($file));   //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
        foreach ($fileNameArr as $file) {
            unlink($file); //删除csv临时文件
        }
        //输出压缩文件提供下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
        header("Content-Type: application/zip"); // zip格式的
        header("Content-Transfer-Encoding: binary"); //
        header('Content-Length: ' . filesize($filename)); //
        @readfile($filename);//输出文件;
        unlink($filename); //删除压缩包临时文件
    }
    
    /**
     * 批量下载录音文件
     * @param call_ids 通话记录id集合
     */
    function DownloadLogs()
    {
        $call_ids = I('call_ids');
        if (!$call_ids) {
            $this->error("请选择要下载的");
        }
        $ids   = implode($call_ids, ',');
        $files = M("device_call")->where("id in ({$ids})")->getField('files', true);
        if (!$files) {
            $this->error("没有可以下载的文件");
        }
        //实例化批量打包类
        $zip = new \ZipArchive;
        //创建临时目录
        $tmppath = DOWNLOAD_PATH;
        if (!is_dir($tmppath)) {
            if (!mkdir($tmppath)) {
                $this->error("创建临时目录失败");
            }
        }
        $filename = $tmppath . '/' . date("ymdHi") . '.zip';//生成文件
        $zip->open($filename, \ZipArchive::CREATE);//打开压缩包
        foreach ($files as $k => $v) {
            $file1 = "";
            $file1 = D("Common/DeviceCall")->getFileDir($v) . D("Common/DeviceCall")->replaceForFilename($v);
            if (!is_file($file1)) {
                continue;
            }
            $a = $zip->addFile($file1, $v);
        }
        $zip->close();
        
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        $this->accountLogs->addLog("批量下载录音文件");
    }
    
    /**
     * 获取设备端口状态
     * @param ids 端口id集合
     */
    function GetLineStatus()
    {
        if (IS_AJAX) {
            $ids = I('ids');
            if (!$ids) {
                echo json_encode(array('status' => 300, 'msg' => '没有请求内容'));
                exit;
            }
            foreach ($ids as $k => $v) {
                $stat = D("Common/DeviceLine")->getData($v);
                if ($stat) {
                    if ($v == 'case') {
                        $data['cased'] = $stat;
                    }
                    $data[$v] = $stat;
                }
            }
            echo json_encode(array('status' => 200, 'data' => $data));
            exit;
        } else {
            echo json_encode(array('status' => 300, 'msg' => '非法请求'));
            exit;
        }
    }
    
    /**
     * 设备监听获取模型
     */
    private function getExp($id)
    {
        if (empty($id)) {
            return false;
        }
        $sel_date        = I('sel_date');
        $start_date      = I('start_date');
        $end_date        = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time   = I('end_call_time');
        $search_line     = I('search_line');
        $search_type     = I('search_type');
        $keywords        = I('keywords');
        $sort            = I('sort');
        
        $_models = $_models->where("device_id", $id);
        
        if ($sel_date == 1) { //今天的
            $start_date = date("Y-m-d 00:00:00");
            $end_date   = date("Y-m-d 23:59:59");
        }
        if ($sel_date == 7) {//7天的
            $start_date = date("Y-m-d 00:00:00", time() - 86400 * 7);
            $end_date   = date("Y-m-d 23:59:59");
        }
        if ($sel_date == 30) { //30天的
            $start_date = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("m"), 1, date("Y")));
            $end_date   = date("Y-m-d 23:59:59", mktime(0, 0, 0, date("m") + 1, 1, date("Y")) - 1);
        }
        
        if ($search_line) { //端口号
            $_models = $_models->where("line_id", $search_line);
        }
        if ($search_type) { //类型
            $_models = $_models->where("type", $search_type);
        }
        
        if ($keywords) { //关键字
            $_models = $_models->where("keywords", 'like', '%' . $keywords . '%');
        }
        if ($start_date) { //开始时间
            $start_time = strtotime($start_date);
            $_models    = $_models->where("add_time", '>=', $start_time);
        }
        if ($end_date) { //结束时间
            $end_time = strtotime($end_date);
            $_models  = $_models->where("add_time", '<', $end_time);
        }
        if ($start_call_time) { //通话最短时间
            $_models = $_models->where("call_time", '>=', $start_call_time);
        }
        if ($end_call_time) { //通话最长时间
            $_models = $_models->where("call_time", '<', $end_call_time);
        }
        
        $sort = empty($sort) ? 'time' : $sort;
        if ($sort == 'type') {
            $_models = $_models->orderBy("type", 'ASC');
        }
        if ($sort == 'recording') {
            $_models = $_models->orderBy("recording_time", 'DESC');
        }
        if ($sort == 'call') {
            $_models = $_models->orderBy("call_time", 'DESC');
        }
        if ($sort == 'tel') {
            $_models = $_models->orderBy("tel", 'DESC');
        }
        if ($sort == 'time') {
            $_models = $_models->orderBy("add_time", 'DESC');
        }
        \Esy\View::newd()->with('start_date', $start_date)->with('end_date', $end_date);
        return $_models;
    }
    
    /**
     * 录音文件播放
     * @param id 记录id
     */
    function PlayLog()
    {
        $this->__beforeStatus();
        $id   = I('id');
        $data = M("device_call")->alias("a")
            ->join("left join devices b on b.id = a.device_id")
            ->join("left join device_line c on c.device_id =a.device_id and c.code = a.line_id")
            ->where("a.id = {$id}")
            ->field("a.files,a.recording_time,a.call_time,a.call_date,a.type,a.call_date,b.name,c.PortName")
            ->find();
        if (!$data['files']) {
            $this->error("录音文件不存在");
        }
        //处理数据 转换为标准格式getFileType
        $data['type']           = D("Common/DeviceCall")->type($data['type']);
        $data['call_time']      = D("Common/DeviceCall")->getTime($data['call_time']);
        $data['recording_time'] = D("Common/DeviceCall")->getTime($data['recording_time']);
        //获取文件路径
        $file = D("Common/DeviceCall")->getFilePlayDir($data['files']);
        $file = $file . D("Common/DeviceCall")->replaceForFilename($data['files']);
        $msg  = '  [设备名称: ' . $data['name'] . '. 设备端口:' . $data['portname'] . '. 记录时间:' . $data['call_date'] . '. 记录文件: ' . $data['files'] . ']';
        $this->accountLogs->addLog('播放 ' . ' ' . $msg);
        
        $this->assign("data", $data);
        $this->assign('file', $file);
        $this->display();
    }
    
    /**
     * 录音文件下载
     */
    function DownloadLog()
    {
        $id = I('id', '', 'int');
        if ($id) {
            $data = M("device_call")->where("id = {$id}")->find();
            if (!$data) {
                $this->error("记录不存在");
            }
            if ($data['throw_host']) {
                $re_url = $data['throw_host'] . '/Api/DownloadLog/?id=' . $id;
                hearder("location:{$re_url}");
            }
            $_device = M("devices")->where("id = {$data['device_id']}")->find();
            
            $msg = '  [设备名称: ' . $_device['name'] . '. 设备端口:' . $data['line_id'] . '. 记录时间:' . $data['call_date'] . '. 记录文件: ' . $data['files'] . ']';
            $this->accountLogs->addLog('下载 ' . ' ' . $msg);
            D("Common/DeviceCall")->FileDownload($data['files']);
        } else {
            $this->error("记录不存在");
        }
    }
    
    /**
     * 删除录音
     * @param id 记录id
     */
    public function deleteLog()
    {
        $id = I('id', '', 'int');
        if (!$id) {
            echo json_encode(array('status' => 300, 'msg' => '请选择要删除的记录'));
            exit;
        }
        $log = M("device_call")->where("id = {$id}")->field("id,files")->find();
        if (!$log) {
            echo json_encode(array('status' => 300, 'msg' => '记录不存在'));
            exit;
        }
        $is = D("Common/DeviceCall")->deleteLog($log);
        if ($is) {
            echo json_encode(array('status' => 200, 'msg' => '删除记录成功'));
            exit;
        } else {
            echo json_encode(array('status' => 300, 'msg' => '删除记录失败'));
            exit;
        }
    }
    
    /**
     * 选择时间段内设备来去电情况
     */
    public function uploadStatus()
    {
        self::GetDevicesGroups();
        $group_id   = I('group_id');
        $keywords   = I('keywords');
        $name       = I('name');
        $code       = I('code');
        $start_date = I('upload_start_date') ? date('Y-m-d', strtotime(I('upload_start_date'))) : date('Y-m-d', strtotime("-1 days"));
        $end_date   = I('upload_end_date') ? date('Y-m-d', strtotime(I('upload_end_date'))) : date('Y-m-d');
        $start_date = $start_date . ' 00:00:00';
        $end_date   = $end_date . ' 00:00:00';
        $data       = (array)$this->device->getDevicesUploadList();
        $deviceIds  = array_column($data['data'], 'id');
        if ($deviceIds) {
            $start_time = strtotime($start_date);
            $end_time   = strtotime($end_date);
            $deviceIds  = implode(',', $deviceIds);
            $sql        = "SELECT device_id FROM `device_call` where add_time>={$start_time} and add_time<={$end_time} and type in (9,10,11)  and device_id in ({$deviceIds})  GROUP BY device_id";
            $uploadIds  = M('device_call')->query($sql);
            $uploadIds  = array_column($uploadIds, 'device_id');
        }
        foreach ($data['data'] as $k => $v) {
            $data['data'][$k]['is_upload'] = in_array($v['id'], $uploadIds) ? 1 : 0;
        }
        $this->assign('isUploadOption', I('isUploadOption'));
        $this->assign('count', $data['status']);
        $this->assign('data', $data['data']);
        $this->assign('keywords', $keywords);
        $this->assign('group_id', $group_id);
        //        $this->assign('status',$status ? $status : 0);
        $this->assign('name', $name);
        $this->assign('code', $code);
        $this->assign('start_date', $start_date);
        $this->assign('end_date', $end_date);
        $this->assign('second_nav_id', 1);
        $this->assign('Page', $data['page']);
        $this->assign('gdata', json_encode($data['data']));
        
        $this->accountLogs->addLog("查看设备上传状态 查询条件:设备名称 {$name},分组id {$group_id},关键字 {$keywords},设备编码 {$code},设备时间最小值 {$start_date},设备时间最大值 {$end_date}");
        $this->display();
    }
}
