<?php

namespace Api\Controller;

use Think\Controller;

class CronController extends Controller
{
    function _initialize()
    {
        
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
    
    
    /**
     * 中信证券 需求 每小时检测设备是否掉线或者异常 写入文本文件
     * 设备检测两种情况
     * 一 设备掉线（10分钟没有上线）
     * 二 设备异常（cpu、内存、存储 超过90%）
     */
    public function checkDeviceForZX()
    {
        $devices = M("devices")
            ->join("left join device_stat on devices.id = device_stat.device_id")
            ->field("devices.id,devices.name,devices.code,device_stat.last_time,device_stat.CPU,device_stat.TotalStore,device_stat.TotalFreeStore,device_stat.TotalMem,device_stat.TotalFreeMem")
            ->where("devices.registered = 1 and devices.closed = 0")
            ->select();
        
        $last_time = time() - D("Common/DeviceStat")->_last_time;//10分钟没有上传 认为不在线
        //按设备检测 如果设备掉线就不在检测是否异常 如果设备在线检测设备是否异常
        if (!$devices) {
            exit('系统当前没有设备');
        }
        foreach ($devices as $k => $device) {
            $data = array();
            //检测是否掉线
            if ($device['last_time'] < $last_time) { //掉线
                $data['level']     = 0; //级别
                $data['log_time']  = date('Y-m-d H:i:s', time());
                $data['chk_time']  = time();//记录时间
                $data['last_time'] = $device['last_time'];//最后在线
                $data['name']      = $device['name'];
                $data['code']      = $device['code'];
                $log               = "{";
                $log               .= "level={$data['level']}&log_time={$data['log_time']}&chk_time={$data['chk_time']}&name={$data['name']}&code={$data['code']}&last_time={$data['last_time']}";
                $log               .= '}';
            } else { //没有掉线
                //检测是否设备异常
                $cpu   = $device['cpu'];
                $mem   = round(($device['totalmem'] - $device['totalfreemem']) / $device['totalmem'], 2) * 100;
                $store = round(($device['totalstore'] - $device['totalfreestore']) / $device['totalstore'], 2) * 100;
                if ($cpu >= 90 || $mem >= 90 || $store >= 90) { //内存 凑
                    $data['level']     = 1; //级别
                    $data['log_time']  = date('Y-m-d H:i:s', time());
                    $data['chk_time']  = time();//记录时间
                    $data['last_time'] = $device['last_time'];//最后在线
                    $data['name']      = $device['name'];
                    $data['code']      = $device['code'];
                    $log               .= "level={$data['level']}&log_time={$data['log_time']}&chk_time={$data['chk_time']}&name={$data['name']}&code={$data['code']}&last_time={$data['last_time']}&cpu={$cpu}%&mem={$mem}%&store={$store}%";
                    $log               .= '}';
                }
                //没有掉线的设备检测端口
                $lines = M("device_line")->where("device_id = {$device['id']} and case_type=13")->field("code,last_time,PortName")->select();
                if ($lines) {
                    foreach ($lines as $x => $line) {
                        $log1     = "{";
                        $chk_time = time();
                        $log_time = date('Y-m-d H:i:s', $chk_time);
                        $log1     .= "level=2&log_time={$log_time}&chk_time={$chk_time}&device_name={$device['name']}&device_code={$device['code']}&line_code={$line['code']}&line_name={$line['portname']}&last_time={$line['last_time']}";
                        $log1     .= '}';
                        $filename = "./public/checkForZX.log";
                        $handle   = fopen($filename, "a+");
                        $str      = fwrite($handle, "{$log1}\n");
                        fclose($handle);
                    }
                }
            }
            if ($log) {
                $filename = "./public/checkForZX.log";
                $handle   = fopen($filename, "a+");
                $str      = fwrite($handle, "{$log}\n");
                fclose($handle);
            }
        }
    }
    
    public function contactRemind()
    {
        $Pemail = new \Common\Utils\MailUtil();
        $time = time();
        $limitTime = 60;
        $date   = date('Y-m-d H:i:s', time() + 3600);
        $saveDate      = date('Y-m-d H:i:s');
        $sql    = "select a.*,b.name,b.tel1,b.company from contact_remind as a INNER JOIN contacts as b
                    on a.contact_id=b.id
                    where is_send=0 and remind_time<='{$date}'
                    order by remind_time asc limit 1000";
        $r      = M('contact_remind', null)->query($sql);
        if ($r) {
            $accountEmails = M('accounts', null)->field('id,email')->select();
            $accountEmails = array_column($accountEmails, 'email', 'id');
            foreach ($r as $v) {
                if(time()-$time>$limitTime){
                    echo "time out";die;
                }
                //封装包体 邮件接收人
                $msg = "<h3>回访提醒<h3>";
                $msg .= "<h4>客户姓名：{$v['name']}；客户电话:{$v['tel1']}</h4>";
                if ($v['company']) {
                    $msg .= '<h4>公司名称：' . $v['company'] . '</h4>';
                }
                $msg   .= '<h4>设定回访时间：' . $v['remind_time'] . '</h4>';
                $title = "回访提醒";
//                var_dump($Pemail);die;
                $Pemail->setHeader($title);
                $Pemail->setBody($msg);
//                var_dump($accountEmails[$v['account_id']]);die;
//                $accountEmails[$v['account_id']] = '785754267@qq.com';
                $Pemail->setClients([ ['email'=>$accountEmails[$v['account_id']]] ]);
                $status = $Pemail->send();
                //如果发送成功 要记录数据
                if ($status) {
                    M('contact_remind', null)->where(['id' => $v['id']])->
                    save(["send_time" => $saveDate . ',' . $v['send_time'],'is_send'=>1]);
                }
            }
            
        }
    }
    
}