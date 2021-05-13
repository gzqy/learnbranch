<?php

namespace Api\Controller;

use Think\Controller;

class RegularStatisticsController extends Controller
{
    function _initialize()
    {
        
    }
    
    /**
     *统计每天该设备上传的录音数量
     *统计每天所有设备上传的录音数量
     *统计每天该设备上传录音文件的的占用空间
     *统计每天所有设备上传录音文件的占用空间
     *统计总共的录音空间
     *统计录音空间已使用空间，统计百分比
     */
    public function statistics()
    {
        
        $data              = [];
        $perStartTimeStamp = strtotime(date('Y-m-d 00:00:00'));
        $perEndTimeStamp   = strtotime(date('Y-m-d 23:59:59'));
        $perStartTimeStamp = strtotime(date('2018-04-24 00:00:00'));
        $perEndTimeStamp   = strtotime(date('2018-04-24 23:59:59'));
        $perEndTimeStamp = strtotime(date('Y-m-d'));
        $perStartTimeStamp = $perEndTimeStamp-3600*24;
        // $year 			   = date('Y') = 2018;
        // $month             = date('m') = 10;
        // $day      		   = date('d') = 13;
        $year  = 2020;
        $month = '07';
        $day   = 27;
        
        //统计每天每个设备上传的录音数量
        $data['perDayPerDeviceUploadNum'] = M("device_call")->field('count(device_id) as sum,device_id')->
        where("files != '0' and files != 'null' and add_time >= $perStartTimeStamp and add_time <= $perEndTimeStamp")->
        group('device_id')->select();
        // echo M()->_sql();die;
        
        //统计每天所有设备上传的录音数量
        $data['perDayAllDeviceUploadNum'] = M("device_call")->field('count(device_id) as sum')->
        where("files != '0' and files != 'null' and add_time >= $perStartTimeStamp and add_time <= $perEndTimeStamp")->select();
        
        //服务器当天接受每个设备所有空间
        $dir                          = [];
        $dirArr                       = $this->listFile(UPLOAD_PATH, $dir);
        $data['perDayPerDeviceSpace'] = [];
        if ($dirArr) {
            foreach ($dirArr as $key => $val) {
                $tempDir    = $val . '/' . $year . '/' . $month . '/' . $day . '/';
                $deviceCode = substr($val, strrpos($val, '/') + 1);
                if (is_dir($tempDir)) {
                    $data['perDayPerDeviceSpace'][$deviceCode] = ((exec("du -ks {$tempDir}")) / 1024) . 'MB';
                } else {
                    $data['perDayPerDeviceSpace'][$deviceCode] = '0MB';
                }
                $tempDir = '';
            }
        }
        
        //服务器当天接受录音文件所用容量
        $nowDayRecordPath               = UPLOAD_PATH . '';
        $data['perDayRecoadTotalSpace'] = (exec("du -ks {$dir}") / 1024);
        
        //服务器硬盘总空间
        $data['serverHardDiskSpace'] = round((@disk_total_space('/') / (1024 * 1024 * 1024)), 2) . 'GB';
        
        //总录音占用服务器硬盘总空间比
        $dir                                = UPLOAD_PATH;
        $data['totalRecoadAndSpacingRatio'] = (((exec("du -ks {$dir}") / 1024/1024) / $data['serverHardDiskSpace']) * 100) . '%';
        
        //服务器当天接受录音文件所用容量
        $nowDayRecordPath               = UPLOAD_PATH . '';
        $data['perDayRecoadTotalSpace'] = (exec("du -ks {$dir}") / 1024/1024);
        
        echo '<pre>';
        print_r($data);
        //   	die;
        
    }
    
    public function listFile($date, $dir)
    {
        //1、首先先读取文件夹
        $temp = scandir($date);
        //遍历文件夹
        foreach ($temp as $v) {
            $a = $date . '/' . $v;
            if (is_dir($a)) {//如果是文件夹则执行
                
                if ($v == '.' || $v == '..') {//判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
                    continue;
                }
                // echo "<font color='red'>$a</font>","<br/>"; //把文件夹红名输出
                $dir[] = $a;
                
                // $this->listFile($a);//因为是文件夹所以再次调用自己这个函数，把这个文件夹下的文件遍历出来
            } else {
                continue;
            }
            
            
        }
        return $dir;
    }
    
    
    /**
     * 每天开始时统计 之前的通话记录数据
     */
    public function changeByDay()
    {
        //获取所有设备
        $devices = M("devices")->getField("id", true);
        //清除以前的数据
        M("device_call_data")->where("id != 0")->delete();
        foreach ($devices as $key => $id) {
            //获取每个设备的每天的各种统计数据
            $data  = array();
            $data1 = array();
            $data  = M("device_call")->where("device_id = {$id}")->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as comeing,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outgoing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as missed,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as audio,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as message,COALESCE(sum(case when type = 50 then 1 else 0 end ),0) as vedio,FROM_UNIXTIME(add_time,'%Y-%m-%d') as time,sum(recording_time) as recording_time,sum(call_time) as call_time ")->group("time")->select();
            if (!$data) {
                continue;
            } else {
                //这里获取了相关数据 进行处理
                foreach ($data as $k => $v) {
                    $data[$k]['device_id'] = $id;
                    $data[$k]['all']       = $v['comeing'] + $v['outgoing'] + $v['missed'] + $v['audio'] + $v['message'] + $v['vedio'];
                    $data[$k]['type']      = 0;
                    //这里要查询某一天来电时长和去电时长
                    $time = $v['time'];//当天的日志字符串
                    //获取这一天的开始和结束时间戳
                    $start = strtotime($time);
                    $end   = $start + 24 * 60 * 60;
                    $data1 = M("device_call")->where("device_id = {$id} and type in (9,10) and add_time between {$start} and {$end}")->field("type,sum(call_time) as call_time")->group("type")->select();
                    if ($data1) { //有查出数据
                        if ($data1[0]) {
                            $data[$k]['outgoing_time'] = $data1[0]['call_time'];
                        } else {
                            $data[$k]['outgoing_time'] = 0;
                        }
                        if ($data1[1]) {
                            $data[$k]['comeing_time'] = $data1[1]['call_time'];
                        } else {
                            $data[$k]['comeing_time'] = 0;
                        }
                    } else { //没有数据 就是0
                        $data[$k]['comeing_time']  = 0;
                        $data[$k]['outgoing_time'] = 0;
                    }
                }
                M("device_call_data")->addall($data);
                unset($data);
                unset($data1);
            }
        }
        //获取所有的数据
        $all         = M("device_call")->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as comeing,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outgoing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as missed,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as audio,COALESCE(sum(case when type = 29 then 1 else 0 end ),0) as message,COALESCE(sum(case when type = 50 then 1 else 0 end ),0) as vedio,sum(recording_time) as recording_time,sum(call_time) as call_time ")->find();
        $all['all']  = $all['comeing'] + $all['outgoing'] + $all['missed'] + $all['audio'] + $all['message'] + $all['vedio'];
        $all['type'] = 1;
        //获取所有数据的总来电时长 去电时长
        $all1 = M("device_call")->where("type in (9,10)")->field("type,sum(call_time) as call_time")->group("type")->select();
        if ($all1[0]['call_time']) {
            $all && $all['outgoing_time'] = $all1[0]['call_time'];
        } else {
            $all && $all['outgoing_time'] = 0;
        }
        if ($all1[1]['call_time']) {
            $all && $all['comeing_time'] = $all1[1]['call_time'];
        } else {
            $all && $all['comeing_time'] = 0;
        }
        $all && M("device_call_data")->add($all);
    }
    
    /**
     * 每天0时统计数据 并更新device_app_data数据表
     */
    public function changeAppByDay()
    {
        //获取所有设备
        $devices = M("devices_app")->getField("id", true);
        //清除以前的数据
        M("device_app_call_data")->where("id != 0")->delete();
        foreach ($devices as $key => $id) {
            //获取每个设备的每天的各种统计数据
            $data = array();
            $data = M("device_app_call")->where("device_id = {$id}")->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as comeing,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outgoing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as missed,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as audio,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as message,COALESCE(sum(case when type = 50 then 1 else 0 end ),0) as vedio,FROM_UNIXTIME(add_time,'%Y-%m-%d') as time,sum(recording_time) as recording_time,sum(call_time) as call_time ")->group("time")->select();
            if (!$data) {
                continue;
            } else {
                //这里获取了相关数据 进行处理
                foreach ($data as $k => $v) {
                    $data[$k]['device_id'] = $id;
                    $data[$k]['all']       = $v['comeing'] + $v['outgoing'] + $v['missed'] + $v['audio'] + $v['message'] + $v['vedio'];
                    $data[$k]['type']      = 0;
                }
                M("device_app_call_data")->addall($data);
                unset($data);
            }
        }
        //获取所有的数据
        $all         = M("device_app_call")->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as comeing,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outgoing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as missed,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as audio,COALESCE(sum(case when type = 29 then 1 else 0 end ),0) as message,COALESCE(sum(case when type = 50 then 1 else 0 end ),0) as vedio,sum(recording_time) as recording_time,sum(call_time) as call_time ")->find();
        $all['all']  = $all['comeing'] + $all['outgoing'] + $all['missed'] + $all['audio'] + $all['message'] + $all['vedio'];
        $all['type'] = 1;
        M("device_app_call_data")->add($all);
    }
    
    
    /**
     * 每隔10分钟 检测设备是否掉线
     */
    public function checkDevice()
    {
        $last_time = time() - D("Common/DeviceStat")->_last_time;//10分钟没有上传 认为不在线
        
        $info = M("devices")
            ->join("left join device_stat on devices.id = device_stat.device_id")
            ->field("devices.id,devices.name,devices.code,device_stat.last_time")
            ->where("devices.registered = 1 and devices.closed = 0 and device_stat.last_time <= {$last_time}")
            ->select();
        if (!$info) {
            exit;
        }
        $Pemail = new \Common\Utils\MailUtil();
        foreach ($info as $k => $v) { //分设备进行发送
            $data  = array();
            $msg   = '';
            $msg   .= '<h3>设备名称:  ' . $v['name'] . '</h3>';
            $msg   .= '<h4>设备编码： ' . $v['code'] . '</h4>';
            $msg   .= '<h4>最后在线： ' . date("Y-m-d H:i:s", $v['last_time']) . '</h4>';
            $msg   .= '<h4>发送时间： ' . date("Y-m-d H:i:s", time()) . '</h4>';
            $title = '';
            $title = '设备[' . $v['code'] . ']' . '] 设备掉线';
            //查找发送记录
            $mail = M("device_stmp")->where("device_id = {$v['id']}")->order("time desc")->limit(1)->getfield("time");
            if ($mail) { //发送过邮件
                $time = time() - $mail;
                if ($time >= 24 * 60 * 60) { //超过要发送并记录
                    $is = 1;
                } else {
                    $is = 0;
                }
            } else { //没有发送过邮件
                $is = 1;
            }
            if ($is) {
                //发送邮件并记录数据库
                $emails = M("AccountPurview")->alias('a')
                    ->join("left join accounts b on a.account_id = b.id")
                    ->where("a.device_id = {$v['id']}")
                    ->field("b.email")
                    ->select();
                $Pemail->setHeader($title);
                $Pemail->setBody($msg);
                $Pemail->setClients($emails);
                $status = $Pemail->send();
                
                if ($status) {
                    $data              = array();
                    $data['device_id'] = $v['id'];
                    $data['time']      = time();
                    M("device_stmp")->add($data);
                }
            }
        }
    }
}