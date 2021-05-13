<?php
/**
 * 数据统计公共类
 */
namespace Common\Utils;
class DataUtil {
    /**
     * 获取所有设备数量 注册数量 未注册数量 删除数量
     */
    public function getDeviceCount(){
        $data = array();
        $data['total'] = M("devices")->count();
        $data['registered'] = M("devices")->where("registered = 1 and closed = 0")->count();
        $data['unregistered'] = M("devices")->where("registered = 0 and closed = 0")->count();
        $data['closed'] = M("devices")->where("closed = 1")->count();
        //设备有效率
        $data['round'] = round($data['registered']/$data['total'],2) * 100;
        return $data;
    }

    /**
     * 获取设备的所有端口数量 
     * 平均端口数量
     * 端口平均来电 去电 未接 留言 音频 视频 总数
     * @param count 设备数量
     */
    public function getDeviceLines($count){
        if(!$count){
            return false;
        }
        $data = array();
        //获取有效设备 --- 未删除的 注册的
        $ids = M("devices")->where("registered = 1 and closed = 0")->getField('id',true);
        $ids = '('.implode($ids,',').')';
        $data['total'] = M("device_line")->where("device_id in {$ids}")->count();
        if(!$data['total'] || 0 == $data['total']){
            return false;
        }
        $data['avge'] = round($data['total']/$count,0);

        //端口平均来电去电未接事件
        $tong = M("device_call_data")->where("type = 1")->field("comeing,outgoing,missed,audio,message,vedio,all")->find();
        $data['comeing'] = round($tong['comeing']/$data['total'],2);
        $data['outgoing'] = round($tong['outgoing']/$data['total'],2);
        $data['audio'] = round($tong['audio']/$data['total'],2);
        $data['message'] = round($tong['message']/$data['total'],2);
        $data['vedio'] = round($tong['vedio']/$data['total'],2);
        $data['all'] = round($tong['all']/$data['total'],2);
        $data['missed'] = round($tong['missed']/$data['total'],2);
        return $data;
    }


    /**
     * 获取所有手机设备数量 注册数量 未注册数量 删除数量
     */
    public function getAppDeviceCount(){
        $data = array();
        $data['total'] = M("devices_app")->count();
        $data['registered'] = M("devices_app")->where("registered = '1' and closed = '0' ")->count();
        $data['unregistered'] = M("devices_app")->where("registered = '0' and closed = '0' ")->count();
        $data['closed'] = M("devices_app")->where("closed = '1' ")->count();
        //设备有效率
        $data['round'] = round($data['registered']/$data['total'],2) * 100;
        return $data;
    }

    /**
     * 获取设备的所有端口数量 
     * 平均端口数量
     * 端口平均来电 去电 未接 留言 音频 视频 总数
     * @param count 设备数量
     */
    public function getAppDeviceLines($count){
        if(!$count){
            return false;
        }
        $data = array();
        //获取有效设备 --- 未删除的 注册的
        $ids = M("devices_app")->where("registered = '1' and closed = '0' ")->getField('id',true);
        $ids = '('.implode($ids,',').')';
        $data['total'] = $count;
        if(!$data['total'] || 0 == $data['total']){
            return false;
        }
        $data['avge'] = round($data['total']/$count,0);

        //端口平均来电去电未接事件
        $tong = M("device_app_call_data")->where("type = 1")->field("comeing,outgoing,missed,audio,message,vedio,all")->find();
        $data['comeing'] = round($tong['comeing']/$data['total'],2);
        $data['outgoing'] = round($tong['outgoing']/$data['total'],2);
        $data['audio'] = round($tong['audio']/$data['total'],2);
        $data['message'] = round($tong['message']/$data['total'],2);
        $data['vedio'] = round($tong['vedio']/$data['total'],2);
        $data['all'] = round($tong['all']/$data['total'],2);
        $data['missed'] = round($tong['missed']/$data['total'],2);
        return $data;
    }

    /**
     * 获取设备cpu 内存 存储信息
     */
    public function getDeviceSystem(){
        $ids = M("devices")->where("registered = 1 and closed = 0")->getField('id',true);
        if(!$ids){
            return false;
        }
        $ids = '('.implode($ids,',').')';
        $data = array();
        $cpu = M("device_stat")->where("device_id in {$ids}")->field("max(cpu) as ecpu,min(cpu) as acpu,max(TotalFreeStore/TotalStore) as astore,min(TotalFreeStore/TotalStore) as estore,max(TotalFreeMem/TotalMem) as amem,min(TotalFreeMem/TotalMem) as emem,avg(cpu) as vcpu,avg(TotalFreeStore) as vfreestore,avg(TotalStore) as vstore,avg(TotalFreeMem) as vfreemem,avg(TotalMem) as vmem")->find();//
        //数据处理
        $data['acpu'] = $cpu['acpu'];//最低cpu
        $data['ecpu'] = $cpu['ecpu'];//最高cpu
        $data['astore'] = 100*(1 - round($cpu['astore'],2));//最低存储使用
        $data['estore'] = 100*(1 - round($cpu['estore'],2));//最高存储使用
        $data['amem'] = 100*(1 - round($cpu['amem'],2));//最低内存使用
        $data['emem'] = 100*(1 - round($cpu['emem'],2));//最高内存使用
        $data['vcpu'] = round($cpu['vcpu'],2);
        $data['vstore'] = 100*(round(($cpu['vstore']-$cpu['vfreestore'])/$cpu['vstore'],2));
        $data['vmem'] = 100*(round(($cpu['vmem']-$cpu['vfreemem'])/$cpu['vmem'],2));
        return $data;
    }

    /**
     * 获取日志相关统计
     * 总日志 日均日志 人均日志 人均日均日志 最多操作日志天 操作数 最少操作日志天 操作数
     * 月最多操作数 最少操作数 月均操作数 月均人均操作数 
     * @pram total 总管理员数量
     */
    public function getAccountLogs($total){
        if(!$total || 0 == $total){
            return false;
        }
        $data = array();
        $count = M("account_logs")->count();
        if(!$count){
            return false;
        }
        $day_logs = M("account_logs")->field("count(id) as count,FROM_UNIXTIME(utime,'%y-%m-%d') as date")->group('date')->select();
        $month_logs = M("account_logs")->field("count(id) as count,FROM_UNIXTIME(utime,'%y-%m') as date")->group('date')->order("count desc")->select();
        //按天的方式统计
        //总天数
        $count_days = count($day_logs);
        foreach($day_logs as $k=>$v){
            $a[$k] = $v['count'];
        }
        //获取最大、最小的天操作数量
        asort($a);
        $data['day']['total'] = $count;
        $data['day']['days'] = $count_days;
        $data['day']['max'] = $day_logs[array_search(max($a),$a)];
        $data['day']['min'] = $day_logs[array_search(min($a),$a)];
        //每天日志 每天每人日志
        $data['day']['avgeday'] = round($data['day']['total']/$data['day']['days'],2);
        $data['day']['avgeaccountday'] = round($data['day']['total']/$data['day']['days']/$total,2);
        unset($a);

        //按月份处理
        $count_month = count($month_logs);
        foreach($month_logs as $k=>$v){
            $b[$k] = $v['count'];
        }
        asort($b);
        $data['month']['total'] = $count;
        $data['month']['months'] = $count_month;
        $data['month']['max'] = $month_logs[array_search(max($b),$b)];
        $data['month']['min'] = $month_logs[array_search(min($b),$b)];
        //每天日志 每天每人日志
        $data['month']['avgeday'] = round($data['month']['total']/$data['month']['months'],2);
        $data['month']['avgeaccountday'] = round($data['month']['total']/$data['month']['months']/$total,2);
        
        return $data;
    }

    /**
     * 统计账号 关注设备 关注记录 平均关注设备 平均关注记录
     * @param $total
     */
    public function getAccountAttention(int $total){
        if(!$total || 0 == $total){
            return false;
        }
        $data = array();
        //关注总数 平均关注数量
        $device_count = M("account_purview")->where("attention = 1")->count();
        $log_count =M("device_call_flag")->count();
        $data['total_device'] = $device_count;
        $data['total_log'] = $log_count;
        $data['avge_device'] = round($data['total_device']/$total,2);
        $data['avge_log'] = round($data['total_log']/$total,2);
        return $data;
    }

    /**
     * 统计账号 关注设备 关注记录 平均关注设备 平均关注记录
     * @param $total
     */
    public function getAccountAppAttention(int $total){
        if(!$total || 0 == $total){
            return false;
        }
        $data = array();
        //关注总数 平均关注数量
        $device_count = M("account_app_purview")->where("attention = 1")->count();
        $log_count =M("device_app_call_flag")->count();
        $data['total_device'] = $device_count;
        $data['total_log'] = $log_count;
        $data['avge_device'] = round($data['total_device']/$total,2);
        $data['avge_log'] = round($data['total_log']/$total,2);
        return $data;
    }

    /**
     * 通话记录 总体计算量统计
     */
    public function getaCallCount(){
        $data = M("device_call_data")->where("type = 1")->find();
        if(!$data){
            return false;
        }
        $data['round'] = 100 * round(( ($data['all'] - $data['missed']) / $data['all']),2)."%";
        $data['miss'] = 100*(1- round(( ($data['all'] - $data['missed']) / $data['all']),2)).'%';

        return $data;
    }

    /**
     * 通话记录 总体计算量统计
     */
    public function getaAppCallCount(){
        $data = M("device_app_call_data")->where("type = 1")->find();
        if(!$data){
            return false;
        }
        $data['round'] = 100 * round(( ($data['all'] - $data['missed']) / $data['all']),2)."%";
        $data['miss'] = 100*(1- round(( ($data['all'] - $data['missed']) / $data['all']),2)).'%';

        return $data;
    }

    /**
     * 按时间段统计通话记录
     */
    public function getCallCountByTime(){
        $data = M("device_call_data")->where("type = 1")->find(); //总的通话数据
        if(!$data){
            return false;
        }
        //获取最开始 和 昨天的时间段
        $minTime = M("device_call_data")->min('time');
        $maxTime = M("device_call_data")->max('time');

        //获取两个时间段内的月份
        $months = self::getMonthFromRange($minTime,$maxTime);
        $months = count($months);//总共的月份
        //计算每个月的 总的来电 去电 记录 等的数据
        $result = array();
        $result['all'] = round($data['all']/$months,2);
        $result['comeing'] = round($data['comeing']/$months,2);
        $result['outgoing'] = round($data['outgoing']/$months,2);
        $result['missed'] = round($data['missed']/$months,2);
        $result['audio'] = round($data['audio']/$months,2);
        $result['message'] = round($data['message']/$months,2);
        $result['vedio'] = round($data['vedio']/$months,2);
        $result['call_time'] = round($data['call_time']/$months,0);
        $result['recording_time'] = round($data['recording_time']/$months,0);
        return $result;
    }

    /**
     * 按时间段统计通话记录
     */
    public function getAppCallCountByTime(){
        $data = M("device_app_call_data")->where("type = 1")->find(); //总的通话数据
        if(!$data){
            return false;
        }
        //获取最开始 和 昨天的时间段
        $minTime = M("device_app_call_data")->min('time');
        $maxTime = M("device_app_call_data")->max('time');

        //获取两个时间段内的月份
        $months = self::getMonthFromRange($minTime,$maxTime);
        $months = count($months);//总共的月份
        //计算每个月的 总的来电 去电 记录 等的数据
        $result = array();
        $result['all'] = round($data['all']/$months,2);
        $result['comeing'] = round($data['comeing']/$months,2);
        $result['outgoing'] = round($data['outgoing']/$months,2);
        $result['missed'] = round($data['missed']/$months,2);
        $result['audio'] = round($data['audio']/$months,2);
        $result['message'] = round($data['message']/$months,2);
        $result['vedio'] = round($data['vedio']/$months,2);
        $result['call_time'] = round($data['call_time']/$months,0);
        $result['recording_time'] = round($data['recording_time']/$months,0);
        return $result;
    }

    /**
     * 按天统计一段时间内的记录数据
     */
    public function getCallCountByday($ids,$type,$start=null,$end=null){
        if(!$ids){
            return false;
        }
        if(0 == $type){ //按天查询
            $file = "DATE_FORMAT(time,'%Y-%m-%d') as date";
        }else if(1==$type){
            return false;
        }else if(2 == $type){
            $file = "DATE_FORMAT(time,'%Y-%m') as date";
        }else if(3== $type){
            $file = "DATE_FORMAT(time,'%Y') as date";
        }else{
            return false;
        }
        //处理时间
        $start && $start = date("Y-m-d 00:00:00",strtotime($start));//查询开始时间
        $end && $end = date("Y-m-d 00:00:00",strtotime($end));//查询结束时间
        $count = count($ids);//总设备数量
        $devices = implode($ids,',');
        //查询这段时间 这些设备的所有数据总量
        $where = array();
        $where['time'] =array('between',"{$start},{$end}");
        $where['device_id'] = array("IN","{$devices}");
        $where['type'] = '0';
        $all = M("device_call_data")->where($where)->field("sum(`all`) as alld,sum(comeing) as comeing, sum(`outgoing`) as outgoing,sum(`missed`) as missed,sum(`audio`) as audio,sum(`message`) as message,sum(`vedio`) as vedio,sum(`call_time`) as call_time,sum(`recording_time`) as recording_time,{$file}")->group("date")->where($where)->select();
        $days = count($all);
        //计算总数量
        $data = array();
        foreach($all as $k=>$v){
            $data['all']['all'] += $v['alld'];
            $data['all']['comeing'] += $v['comeing'];
            $data['all']['outgoing'] += $v['outgoing'];
            $data['all']['missed'] += $v['missed'];
            $data['all']['audio'] += $v['audio'];
            $data['all']['message'] += $v['message'];
            $data['all']['vedio'] += $v['vedio'];
            $data['all']['call_time'] += $v['call_time'];
            $data['all']['recording_time'] += $v['recording_time'];
            $all[$k]['call_time'] = self::trantSecondToHour($v['call_time']);
            $all[$k]['recording_time'] = self::trantSecondToHour($v['recording_time']);
        }
        $data['all']['call_time'] = self::trantSecondToHour($data['all']['call_time']);
        $data['all']['recording_time'] = self::trantSecondToHour($data['all']['recording_time']);
        //折现图
        $x = '[';
        $y = array();
        foreach($all as $k=>$v){
            $x .= "'".$v['date']."',";
            $y['coming'] .= $v['comeing'].',';
            $y['outing'] .= $v['outgoing'].',';
            $y['miss'] .= $v['missed'].',';
            $y['audio'] .= $v['audio'].',';
            $y['message'] .= $v['message'].',';
            $y['vedio'] .= $v['vedio'].',';
            $y['call_time'] .= $v['call_time'].',';
            $y['recording_time'] .= $v['recording_time'].',';
        }
        $x .= "]";
        $arr = "{
            title : {text: '按天统计记录',subtext: ''},
            tooltip : {trigger: 'axis'},
            legend: {data:['来电总数','去电总数','未接来电','音频记录','来电留言','视频记录','通话时长','录音时长']},
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
                    name:'音频记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['audio']}],
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
                    name:'留言记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['message']}],
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
                    name:'视频记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['vedio']}],
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
                    name:'通话时长',
                    type:'line',
                    smooth:true,
                    data:[{$y['call_time']}],
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
                    name:'录音时长',
                    type:'line',
                    smooth:true,
                    data:[{$y['recording_time']}],
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

        $data['option'] = $arr;
        $data['devices'] = $count;
        $data['times'] = $days;
        //计算每天的数据量
        $data['bytime']['all'] = round($data['all']['all']/$data['times'],2);
        $data['bytime']['comeing'] = round($data['all']['comeing']/$data['times'],2);
        $data['bytime']['outgoing'] = round($data['all']['outgoing']/$data['times'],2);
        $data['bytime']['missed'] = round($data['all']['missed']/$data['times'],2);
        $data['bytime']['audio'] = round($data['all']['audio']/$data['times'],2);
        $data['bytime']['message'] = round($data['all']['message']/$data['times'],2);
        $data['bytime']['vedio'] = round($data['all']['vedio']/$data['times'],2);
        $data['bytime']['call_time'] = round($data['all']['call_time']/$data['times'],2);
        $data['bytime']['recording_time'] = round($data['all']['recording_time']/$data['times'],2);
        //每台设备的
        $data['bydevice']['all'] = round($data['all']['all']/$data['devices'],2);
        $data['bydevice']['comeing'] = round($data['all']['comeing']/$data['devices'],2);
        $data['bydevice']['outgoing'] = round($data['all']['outgoing']/$data['devices'],2);
        $data['bydevice']['missed'] = round($data['all']['missed']/$data['devices'],2);
        $data['bydevice']['audio'] = round($data['all']['audio']/$data['devices'],2);
        $data['bydevice']['message'] = round($data['all']['message']/$data['devices'],2);
        $data['bydevice']['vedio'] = round($data['all']['vedio']/$data['devices'],2);
        $data['bydevice']['call_time'] = round($data['all']['call_time']/$data['devices'],2);
        $data['bydevice']['recording_time'] = round($data['all']['recording_time']/$data['devices'],2);
        //每天设备每天的
        $data['bydevicetime']['all'] = round($data['all']['all']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['comeing'] = round($data['all']['comeing']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['outgoing'] = round($data['all']['outgoing']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['missed'] = round($data['all']['missed']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['audio'] = round($data['all']['audio']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['message'] = round($data['all']['message']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['vedio'] = round($data['all']['vedio']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['call_time'] = round($data['all']['call_time']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['recording_time'] = round($data['all']['recording_time']/$data['devices']/$data['times'],2);
        return $data;
    }

    /**
     * 按天统计一段时间内的记录数据
     */
    public function getAppCallCountByday($ids,$type,$start=null,$end=null){
        if(!$ids){
            return false;
        }
        if(0 == $type){ //按天查询
            $file = "DATE_FORMAT(time,'%Y-%m-%d') as date";
        }else if(1==$type){
            return false;
        }else if(2 == $type){
            $file = "DATE_FORMAT(time,'%Y-%m') as date";
        }else if(3== $type){
            $file = "DATE_FORMAT(time,'%Y') as date";
        }else{
            return false;
        }
        //处理时间
        $start && $start = date("Y-m-d 00:00:00",strtotime($start));//查询开始时间
        $end && $end = date("Y-m-d 00:00:00",strtotime($end));//查询结束时间
        $count = count($ids);//总设备数量
        $devices = implode($ids,',');
        //查询这段时间 这些设备的所有数据总量
        $where = array();
        $where['time'] =array('between',"{$start},{$end}");
        $where['device_id'] = array("IN","{$devices}");
        $where['type'] = '0';
        $all = M("device_app_call_data")->where($where)->field("sum(`all`) as alld,sum(comeing) as comeing, sum(`outgoing`) as outgoing,sum(`missed`) as missed,sum(`audio`) as audio,sum(`message`) as message,sum(`vedio`) as vedio,sum(`call_time`) as call_time,sum(`recording_time`) as recording_time,{$file}")->group("date")->where($where)->select();
        $days = count($all);
        //计算总数量
        $data = array();
        foreach($all as $k=>$v){
            $data['all']['all'] += $v['alld'];
            $data['all']['comeing'] += $v['comeing'];
            $data['all']['outgoing'] += $v['outgoing'];
            $data['all']['missed'] += $v['missed'];
            $data['all']['audio'] += $v['audio'];
            $data['all']['message'] += $v['message'];
            $data['all']['vedio'] += $v['vedio'];
            $data['all']['call_time'] += $v['call_time'];
            $data['all']['recording_time'] += $v['recording_time'];
            $all[$k]['call_time'] = self::trantSecondToHour($v['call_time']);
            $all[$k]['recording_time'] = self::trantSecondToHour($v['recording_time']);
        }
        $data['all']['call_time'] = self::trantSecondToHour($data['all']['call_time']);
        $data['all']['recording_time'] = self::trantSecondToHour($data['all']['recording_time']);
        //折现图
        $x = '[';
        $y = array();
        foreach($all as $k=>$v){
            $x .= "'".$v['date']."',";
            $y['coming'] .= $v['comeing'].',';
            $y['outing'] .= $v['outgoing'].',';
            $y['miss'] .= $v['missed'].',';
            $y['audio'] .= $v['audio'].',';
            $y['message'] .= $v['message'].',';
            $y['vedio'] .= $v['vedio'].',';
            $y['call_time'] .= $v['call_time'].',';
            $y['recording_time'] .= $v['recording_time'].',';
        }
        $x .= "]";
        $arr = "{
            title : {text: '按天统计记录',subtext: ''},
            tooltip : {trigger: 'axis'},
            legend: {data:['来电总数','去电总数','未接来电','音频记录','来电留言','视频记录','通话时长','录音时长']},
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
                    name:'音频记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['audio']}],
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
                    name:'留言记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['message']}],
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
                    name:'视频记录',
                    type:'line',
                    smooth:true,
                    data:[{$y['vedio']}],
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
                    name:'通话时长',
                    type:'line',
                    smooth:true,
                    data:[{$y['call_time']}],
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
                    name:'录音时长',
                    type:'line',
                    smooth:true,
                    data:[{$y['recording_time']}],
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

        $data['option'] = $arr;
        $data['devices'] = $count;
        $data['times'] = $days;
        //计算每天的数据量
        $data['bytime']['all'] = round($data['all']['all']/$data['times'],2);
        $data['bytime']['comeing'] = round($data['all']['comeing']/$data['times'],2);
        $data['bytime']['outgoing'] = round($data['all']['outgoing']/$data['times'],2);
        $data['bytime']['missed'] = round($data['all']['missed']/$data['times'],2);
        $data['bytime']['audio'] = round($data['all']['audio']/$data['times'],2);
        $data['bytime']['message'] = round($data['all']['message']/$data['times'],2);
        $data['bytime']['vedio'] = round($data['all']['vedio']/$data['times'],2);
        $data['bytime']['call_time'] = round($data['all']['call_time']/$data['times'],2);
        $data['bytime']['recording_time'] = round($data['all']['recording_time']/$data['times'],2);
        //每台设备的
        $data['bydevice']['all'] = round($data['all']['all']/$data['devices'],2);
        $data['bydevice']['comeing'] = round($data['all']['comeing']/$data['devices'],2);
        $data['bydevice']['outgoing'] = round($data['all']['outgoing']/$data['devices'],2);
        $data['bydevice']['missed'] = round($data['all']['missed']/$data['devices'],2);
        $data['bydevice']['audio'] = round($data['all']['audio']/$data['devices'],2);
        $data['bydevice']['message'] = round($data['all']['message']/$data['devices'],2);
        $data['bydevice']['vedio'] = round($data['all']['vedio']/$data['devices'],2);
        $data['bydevice']['call_time'] = round($data['all']['call_time']/$data['devices'],2);
        $data['bydevice']['recording_time'] = round($data['all']['recording_time']/$data['devices'],2);
        //每天设备每天的
        $data['bydevicetime']['all'] = round($data['all']['all']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['comeing'] = round($data['all']['comeing']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['outgoing'] = round($data['all']['outgoing']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['missed'] = round($data['all']['missed']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['audio'] = round($data['all']['audio']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['message'] = round($data['all']['message']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['vedio'] = round($data['all']['vedio']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['call_time'] = round($data['all']['call_time']/$data['devices']/$data['times'],2);
        $data['bydevicetime']['recording_time'] = round($data['all']['recording_time']/$data['devices']/$data['times'],2);
        return $data;
    }

    /**
     * 按小时获取记录数据
     */
    public function getCallCountByhour($ids,$start,$end){
       //获取时间段开始和结束时间
        $time1=strtotime(date('Y-m-d H:00:00',strtotime($start)));
        $time2 = strtotime(date( 'Y-m-d H:59:59', strtotime($end)));
        $hours = self::getHourFromRange($start);
        $ids = implode($ids,',');
        $where['device_id'] = array("in","($ids)");
        $where['add_time'] = array('between',"{$time1},$time2");

        $all = M("device_call")->where($where)->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as coming,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as miss,FROM_UNIXTIME(add_time,'%k') as date")->group("date")->where($where)->select();
        foreach($all as $v){
            $select[$v['date']] = $v;
            unset($select[$v['date']]['date']);
        }
         foreach($hours as $k=>$v){
            if(!$select[$k]){
                $select[$k]['coming'] = 0;
                $select[$k]['outing'] = 0;
                $select[$k]['missed'] = 0;
            }
        }
         ksort($select);
        foreach($select as $k=>$v){
            $data['all']['coming'] += $v['coming'];
            $data['all']['outing'] += $v['outing'];
            $data['all']['miss'] += $v['miss'];
        }
         $x = '[';
        $y = array();
        foreach($select as $k => $v){
            $x .= "'".$k."',";
            $y['coming'] .= $v['coming'].',';
            $y['outing'] .= $v['outing'].',';
            $y['miss'] .= $v['miss'].',';
        }
        $x .= "]";
        $arr = "{
            title : {text: '{$log_op}',subtext: ''},
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
        };";
        $data['option'] = $arr;
        return $data;
    }

     /**
     * 按小时获取记录数据
     */
    public function getAppCallCountByhour($ids,$start,$end){
       //获取时间段开始和结束时间
        $time1=strtotime(date('Y-m-d H:00:00',strtotime($start)));
        $time2 = strtotime(date( 'Y-m-d H:59:59', strtotime($end)));
        $hours = self::getHourFromRange($start);
        $ids = implode($ids,',');
        $where['device_id'] = array("in","{$ids}");
        $where['add_time'] = array('between',"{$time1},$time2");

        $all = M("device_app_call")->where($where)->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as coming,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as miss,FROM_UNIXTIME(add_time,'%k') as date")->group("date")->where($where)->select();
        foreach($all as $v){
            $select[$v['date']] = $v;
            unset($select[$v['date']]['date']);
        }
         foreach($hours as $k=>$v){
            if(!$select[$k]){
                $select[$k]['coming'] = 0;
                $select[$k]['outing'] = 0;
                $select[$k]['missed'] = 0;
            }
        }
         ksort($select);
        foreach($select as $k=>$v){
            $data['all']['coming'] += $v['coming'];
            $data['all']['outing'] += $v['outing'];
            $data['all']['miss'] += $v['miss'];
        }
         $x = '[';
        $y = array();
        foreach($select as $k => $v){
            $x .= "'".$k."',";
            $y['coming'] .= $v['coming'].',';
            $y['outing'] .= $v['outing'].',';
            $y['miss'] .= $v['miss'].',';
        }
        $x .= "]";
        $arr = "{
            title : {text: '{$log_op}',subtext: ''},
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
        };";
        $data['option'] = $arr;
        return $data;
    }

    /**
     * 获取某天的24个小时
     */
    private static function getHourFromRange($start){
        $beginTime = strtotime(date('Y-m-d 00:00:00',strtotime($start)));//开始时间
        $endTime  = $beginTime + 86400 - 1 ;//结束时间戳
        for($i = 0; $i < 24; $i++){
            $hours[$i]['first'] = $beginTime + ($i * 3600);
            $hours[$i]['end'] = $beginTime + (($i+1) * 3600)-1;
        }
        return $hours;
    }

    /**
     * 按月份获取记录统计量
     */
    public function getLogsByMonth(){
        $data = M("device_call_data")->field(" sum(`all`) as alld,sum(comeing) as comeing, sum(`outgoing`) as outgoing,sum(`missed`) as missed,sum(`audio`) as audio,sum(`message`) as message,sum(`vedio`) as vedio,sum(`call_time`) as call_time,sum(`recording_time`) as recording_time,DATE_FORMAT(time,'%Y-%m') as date")->where("device_id != 0")->group("date")->select();
        if($data){
            return $data;
        }
        return false;
    }

    /**
     * 按月份获取记录统计量
     */
    public function getAppLogsByMonth(){
        $data = M("device_app_call_data")->field(" sum(`all`) as alld,sum(comeing) as comeing, sum(`outgoing`) as outgoing,sum(`missed`) as missed,sum(`audio`) as audio,sum(`message`) as message,sum(`vedio`) as vedio,sum(`call_time`) as call_time,sum(`recording_time`) as recording_time,DATE_FORMAT(time,'%Y-%m') as date")->where("device_id != 0")->group("date")->select();
        if($data){
            return $data;
        }
        return false;
    }

    /**
     * 数据统计方式1 通过选择设备已经设定开始和结束时间
     * @param start_date 开始时间
     * @param end_date 结束时间
     * @param ids 设备id集合
     */
    public function getStatisByDevices($ids,$start_date,$end_date){
            $device_ids = implode($ids, ',');
            //处理时间
            $start_date && $start_date = date("Y-m-d 00:00:00",strtotime($start_date));
            $end_date && $end_date = date("Y-m-d 00:00:00",strtotime($end_date));
            //where条件处理
            if($start_date && $end_date){
                $where['a.time'] = array("between",array($start_date,$end_date));
            }else{
               $start_date && $where['a.time'] = array('glt',$start_date);
                $end_date && $where['a.time'] = array('elt',$end_date); 
            }
            
            $where['a.type'] = '0';
            $where['a.device_id'] = array("IN","$device_ids");
            $data = M("device_call_data")->alias("a")
                ->join("right join devices b on a.device_id = b.id")
                ->join("left join device_group c on c.id = b.group_id ")
                ->where($where)
                ->field("COALESCE(sum(a.comeing),0) as comeing,COALESCE(sum(a.outgoing),0) as outgoing,COALESCE(sum(a.missed),0) as missed,COALESCE(sum(a.audio),0) as audio,COALESCE(sum(a.message),0) as message,COALESCE(sum(a.vedio),0) as vedio,COALESCE(sum(a.call_time),0) as call_time,COALESCE(sum(a.recording_time),0) as recording_time,COALESCE(sum(a.comeing_time),0) as comeing_time,COALESCE(sum(a.outgoing_time),0) as outgoing_time,b.code,b.name,b.id,c.name as gname")->order("b.id desc")
                ->group("a.device_id")
                ->select();
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
                    $data[$v]['call_time'] = self::secToTime($result[$v]['call_time']);
                    $data[$v]['recording_time'] = self::secToTime($result[$v]['recording_time']);
                    $data[$v]['comeing_time'] = self::secToTime($result[$v]['comeing_time']);
                    $data[$v]['outgoing_time'] = self::secToTime($result[$v]['outgoing_time']);
                }else{//设备没有查询到数据 说明都是0
                    $data[$v]['id'] = $v;
                    //查询设备信息
                    $device = M("devices")->alias('a')->join("left join device_group b on a.group_id = b.id")->where("a.id = {$v}")->field("a.code,a.name,b.name as gname")->find();
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
                    $data[$v]['comeing_time'] = "00:00:00";
                    $data[$v]['outgoing_time'] = "00:00:00";
                    $data[$v]['gname'] = $device['gname'];
                }
            }
            return $data;       
    }

    /**
     * 数据统计方式2 按条件查询方式 
     * @param device_name 设备名称
     * @param device_code 设备编码
     * @param portname 端口名称
     * @param port_code 端口编码
     * @param tel 电话号码
     * @param search_type 查询条件
     * @param start_date 录音开始时间最小值
     * @param end_date 录音开始时间最大值
     * @param min_call 最小通话时长
     * @param max_call 最大通话时长
     * @param min_recording 最小录音时长
     * @param max_recording 最大录音时长
     */
    public function getStatisByConditions($account_id){
        $device_name = I('device_name');
        $device_code = I('device_code');
        $portname = I('portname');
        $port_code = I('port_code');
        $tel = I('tel');
        $search_type = I('search_type');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $min_call = I('min_call');
        $max_call = I('max_call');
        $min_recording = I('min_recording');
        $max_recording = I('max_recording');
        
        $device_code = $device_name='';
        $lineIds = I('lineIds');
        //要查询device_call 记录表其中设备和端口信息需要转化成对应的line_id 和device_id 其他条件是可以直接使用的
        //where条件处理
        $where = array();
        //device_id line_id 这两个条件最后必须合成一个条件 才能
        if($device_name && $device_code){
            $device_ids = M("account_purview")->alias("a")
                        ->join("left join devices b on a.device_id = b.id")
                        ->where("b.name like '%{$device_name}%' and b.code like '%{$device_code}%' and a.account_id = {$account_id}")
                        ->field("a.device_id")
                        ->select();
        }else if($device_name || $device_code){
            $device_name && $device_ids = M("account_purview")->alias("a")
                        ->join("left join devices b on a.device_id = b.id")
                        ->where("b.name like '%{$device_name}%' and a.account_id = {$account_id}")
                        ->field("a.device_id")
                        ->select();
            $device_code && $device_ids = M("account_purview")->alias("a")
                        ->join("left join devices b on a.device_id = b.id")
                        ->where("b.code like '%{$device_code}%' and a.account_id = {$account_id}")
                        ->field("a.device_id")
                        ->select();
        }else{
//            $device_ids = M("account_purview")->where("account_id = {$account_id}")->field("device_id")->select();
        }
        if($lineIds){
            $purviewLines=(array)M("account_purview_line",null)->where(['line_id'=>['in',$lineIds],'account_id'=>$account_id])->select();
            $device_ids = array_column($purviewLines,'device_id');
        }else{
            $purviewLines=(array)M("account_purview_line",null)->where(['account_id'=>$account_id])->select();
            $device_ids = array_column($purviewLines,'device_id');
            $lineIds = array_column($purviewLines,'line_id');
        }
        $device_ids = array_unique($device_ids);
        if(!$device_ids){
            return false;
        }
        $dids = "";
        foreach($device_ids as $k=>$v){
            $dids .=$v['device_id'].',';
        }
        $dids = substr($dids,0,strlen($dids)-1); //最终需要的设备id集合
        //处理端口
        $lines = array();
        if($portname && $port_code){ //同时输入端口号和端口名称 这种情况是 权限设备对应的端口的名称必须是指定的才能匹配
            $lines = M("device_line")->where("device_id in ({$dids}) and code like '%{$port_code}%' and portname line '%{$portname}%'")->getField('id',true);
        }else if($portname || $port_code){ //如果指定的事端口号 那么就是指定设备的 
            $portname && $lines = M("device_line")->where("device_id in ({$dids}) and portname like '%{$portname}%'")->getField('id',true);
            $port_code && $lines = M("device_line")->where("device_id in ({$dids}) and code like '%{$port_code}%'")->getField('id',true);
        }else{ //没有选端口 那么就是指定设备的所有端口
            $lines = M("device_line")->where("device_id in ({$dids})")->getField('id',true);
        }
        if($lineIds){
            $lines=$lineIds;
        }
        //所有的设备对应各自的端口数组 需要处理成where条件
        if(!$lines){
            return false;
        }
        //where条件处理
        $where['line'] = array("IN",$lines);
        //处理其他device_call表本身条件
        $tel && $where['tel']= array('like',"%{$tel}%");
        $search_type && $where['type'] = $search_type;
        if($start_date && $end_date){
            $where['add_time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['add_time'] = array('egt',strtotime($start_date));
            $end_date && $where['add_time'] = array('elt',strtotime($end_date));
        }
        if($min_call && $max_call){
            $where['call_time'] = array('between',array($min_call,$max_call));
        }else{
            $min_call && $where['call_time'] = array('egt',$min_call);
            $max_call && $where['call_time'] = array('elt',$max_call);
        }
        if($min_recording && $max_recording){
            $where['recording_time'] = array('between',array($min_recording,$max_recording));
        }else{
            $min_recording && $where['recording_time'] = array('egt',$min_recording);
            $max_recording && $where['recording_time'] = array('elt',$max_recording);
        }
        $data = M("device_call")->where($where)->field("COALESCE(sum(case when type = 10 then 1 else 0 end ),0) as coming,COALESCE(sum(case when type = 9 then 1 else 0 end ),0) as outing,COALESCE(sum(case when type = 11 then 1 else 0 end ),0) as miss,COALESCE(sum(case when type = 28 then 1 else 0 end ),0) as audio,COALESCE(sum(case when type = 29 then 1 else 0 end ),0) as message,COALESCE(sum(case when type = 50 then 1 else 0 end ),0) as vedio,COALESCE(sum(call_time),0) as call_time,COALESCE(sum(recording_time),0) as recording_time,device_id,line_id,line")->group("line")->select();
        //这里处理如果没有找到对应的数据 处理为0
        foreach($data as $k=>$v){
            if($lineIds && !in_array($v['line'],$lineIds)){
                continue;
            }
            $data1[$v['line']] = $v;
        }
        foreach($lines as $k=>$v){
            if(!is_array($data1[$v])){ //说明这个端口没有数组那么默认就给0
                $data1[$v]['coming'] = 0;
                $data1[$v]['outing'] = 0;
                $data1[$v]['miss'] = 0;
                $data1[$v]['audio'] = 0;
                $data1[$v]['message'] = 0;
                $data1[$v]['vedio'] = 0;
                $data1[$v]['call_time'] = 0;
                $data1[$v]['recording_time'] = 0;
                $data1[$v]['line'] = $v;
                //找到对应的设备和端口
                $line = M("device_line")->where("id = {$v}")->field("portname,code,device_id")->find();
                $data1[$v]['device_id'] = $line['device_id'];
                $data1[$v]['line_id'] = $line['code'];
                $data1[$v]['portname'] = $line['portname'];
            }else{
                $portname = M("device_line")->where("id = {$v}")->getField("portname");
                $data1[$v]['portname'] = $portname;
            }
        }
        //把统计结果变成按照设备的进行分组
        unset($data);
        $data = array();
        foreach($data1 as $k=>$v){
          
            $data['device'][$v['device_id']]['line'][$k] = $v;
            $data['total']['coming'] += $v['coming'];
            $data['total']['outing'] += $v['outing'];
            $data['total']['miss'] += $v['miss'];
            $data['total']['audio'] += $v['audio'];
            $data['total']['message'] += $v['message'];
            $data['total']['vedio'] += $v['vedio'];
            $data['total']['call_time'] += $v['call_time'];
            $data['total']['recording_time'] += $v['recording_time'];
        }
        foreach($data['device'] as $k=>$v){
            foreach($v['line'] as $x=>$y){
                $data['device'][$k]['total']['coming'] += $y['coming'];
                $data['device'][$k]['total']['outing'] += $y['outing'];
                $data['device'][$k]['total']['miss'] += $y['miss'];
                $data['device'][$k]['total']['audio'] += $y['audio'];
                $data['device'][$k]['total']['message'] += $y['message'];
                $data['device'][$k]['total']['vedio'] += $y['vedio'];
                $data['device'][$k]['total']['call_time'] += $y['call_time'];
                $data['device'][$k]['total']['recording_time'] += $y['recording_time'];
            }
            $name = M("devices")->where("id = {$k}")->getField("name");
            $data['device'][$k]['device_name'] = $name;
            
        }
        $data['total']['call_time'] = self::secToTime($data['total']['call_time']);
        $data['total']['recording_time'] = self::secToTime($data['total']['recording_time']);
        foreach($data['device'] as $k=>$v){
            $data['device'][$k]['total']['call_time'] = self::secToTime($v['total']['call_time']);
            $data['device'][$k]['total']['recording_time'] = self::secToTime($v['total']['recording_time']);
            foreach($v['line'] as $x=>$y){
                $data['device'][$k]['line'][$x]['call_time'] = self::secToTime($y['call_time']);
                $data['device'][$k]['line'][$x]['recording_time'] = self::secToTime($y['recording_time']);
            }
        }
        return $data;
    }

    /**
     * 学威统计数据
     */
    public function xuewei($ids,$start_date,$end_date,$score=0){
        if(!$ids || !$start_date || !$end_date){
            return false;
        }
        if(0 != $score) $score = $score - 1;
        $where = [];
        $where['device_id'] = array('in',$ids);
       
        if($start_date && $end_date){
            $where['add_time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['add_time'] = array('egt',strtotime($start_date));
            $end_date && $where['add_time'] = array('elt',strtotime($end_date));
        }
        $score && $where['score'] = $score;
        //获取设备和端口信息
        $device = [];
        $line = [];
        $where_line['a.device_id'] = array('in',$ids); 
        $line = M('device_line')->alias('a')->join('LEFT JOIN devices b on a.device_id = b.id')->where($where_line)->field('a.id as line_id,a.device_id,a.PortName,b.name')->select();
        //数据统计
        $where_out = $where;
        $where_out['type'] = 9;
        //首先按照设备和端口分组查询 去电次数 去电总时长  
        $data = [];
        $data['out'] = M("device_call")
        ->where($where_out)
        ->field("sum(call_time) as out_time,device_id,line_id,count(*) as count")
        ->group("device_id,line_id")
        ->select();

        //统计来电总数 来电总时长
        $where_come = $where;
        $where_come['type'] = 10;
        $data['come'] = M("device_call")
        ->where($where_come)
        ->field("sum(call_time) as out_time,device_id,line_id,count(*) as count")
        ->group("device_id,line_id")
        ->select();
        //统计通话时长超过60秒的总个数 总时长
        $where_youxiao = $where_out;
        $where_youxiao['call_time'] = array('egt',60);
        $data['youxiao'] = M("device_call")
        ->where($where_youxiao)
        ->field("sum(call_time) as out_time,device_id,line_id,count(*) as count")
        ->group("device_id,line_id")
        ->select();
        //统计无效的通话时长 通话个数
        $where_wuxiao = $where_out;
        $where_wuxiao['call_time'] = array('lt',60);
        $data['wuxiao'] =  M("device_call")
        ->where($where_wuxiao)
        ->field("sum(call_time) as out_time,device_id,line_id,count(*) as count")
        ->group("device_id,line_id")
        ->select();
        //统计每个端口打出的第一个超过60秒的通话
        $where_list = $where_youxiao;
        $data['list'] = M("device_call")
        ->where($where_list)
        ->field("device_id,line_id,add_time")
        ->group("device_id,line_id")
        ->order('add_time desc')
        ->select();
       //数据整合
        foreach($line as $k=>$v){
            $line[$k]['out_time'] = 0;
            $line[$k]['out_count'] = 0;
            $line[$k]['come_time'] = 0;
            $line[$k]['come_count'] = 0;
            $line[$k]['youxiao_time'] = 0;
            $line[$k]['youxiao_count'] = 0;
            $line[$k]['wuxiao_time'] = 0;
            $line[$k]['wuxiao_count'] = 0;
            $line[$k]['first'] = null;
            foreach($data['out'] as $m=>$n){
                if($v['device_id'] == $n['device_id'] && $v['line_id'] == $n['line_id']){
                    $line[$k]['out_time'] = $n['out_time'];
                    $line[$k]['out_count'] = $n['count'];
                }
            }
            foreach($data['come'] as $m=>$n){
                if($v['device_id'] == $n['device_id'] && $v['line_id'] == $n['line_id']){
                    $line[$k]['come_time'] = $n['out_time'];
                    $line[$k]['come_count'] = $n['count'];
                }
            }
            foreach($data['youxiao'] as $m=>$n){
                if($v['device_id'] == $n['device_id'] && $v['line_id'] == $n['line_id']){
                    $line[$k]['youxiao_time'] = $n['out_time'];
                    $line[$k]['youxiao_count'] = $n['count'];
                }
            }
            foreach($data['wuxiao'] as $m=>$n){
                if($v['device_id'] == $n['device_id'] && $v['line_id'] == $n['line_id']){
                    $line[$k]['wuxiao_time'] = $n['out_time'];
                    $line[$k]['wuxiao_count'] = $n['count'];
                }
            }

            foreach($data['list'] as $m=>$n){
                if($v['device_id'] == $n['device_id'] && $v['line_id'] == $n['line_id']){
                    $line[$k]['first'] = $n['add_time'];
                }
            }
        }
        return $line;
    }

    public function xuewei_detail($device_id,$line_id,$start_date,$end_date,$score){
        if(!$device_id || !$line_id || !$start_date || !$end_date){
            return false;
        }
        $p = I('p') ? I('p') : 1;
        if(0 != $score) $score = $score - 1;
        $where = [];
        $where['device_id'] = $device_id;
        $where['line_id'] = $line_id;
        $where['type'] = 9;
        if($start_date && $end_date){
            $where['add_time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['add_time'] = array('egt',strtotime($start_date));
            $end_date && $where['add_time'] = array('elt',strtotime($end_date));
        }
        $score && $where['score'] = $score;
        $count = M("device_call")->where($where)->count();
        $page_array = array(
            'p'=>$p,
            'device_id'=>$device_id,
            'line_id'=>$line_id,
            'start_date'=>$start_date,
            'end_date'=>$end_date,
            'pingfen'=>$score
        );
        $page = self::page($count,100,$page_array);
        $callList = M("device_call")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        if($callList){
            foreach($callList as $k=>$call){
                $img = D("Common/DeviceCall")->icon($call['type']);
                $type = D("Common/DeviceCall")->type($call['type']);
                $callList[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $callList[$k]['call_time'] = self::secToTime($call['call_time']);
                $callList[$k]['recording_time'] = self::secToTime($call['recording_time']);
                //检测是否有文件
                if(!$call['files']){
                    $callList[$k]['files'] = 0;
                }else{
                    $files = D("Common/DeviceCall")->getFileDir($call['files']).D("Common/DeviceCall")->replaceForFilename($call['files']);
                    if(!is_file($files)){
                        $callList[$k]['files'] = 0;
                    }else{
                        $callList[$k]['files'] = 1;
                    }
                }
            }
        }
        $data = array();
        $data['page'] = $page->show();
        $data['callList'] = $callList;
        return $data;
    }

    /**
     * 获取记录列表
     */
    public function getCallList($account_id,$device_ids=null,$excel=0){
        if(!$device_ids){ //设备id集合可能是传递过来的 也可能是通过条件进行查找
            $device_name = I('device_name');
            $device_code = I('device_code');
            $where = array();
            //device_id line_id 这两个条件最后必须合成一个条件 才能
            if($device_name && $device_code){
                $device_ids = M("account_purview")->alias("a")
                            ->join("left join devices b on a.device_id = b.id")
                            ->where("b.name like '%{$device_name}%' and b.code like '%{$device_code}%' and a.account_id = {$account_id}")
                            ->getField("device_id",true);
            }else if($device_name || $device_code){
                $device_name && $device_ids = M("account_purview")->alias("a")
                            ->join("left join devices b on a.device_id = b.id")
                            ->where("b.name like '%{$device_name}%' and a.account_id = {$account_id}")
                            ->getField("device_id",true);
                $device_code && $device_ids = M("account_purview")->alias("a")
                            ->join("left join devices b on a.device_id = b.id")
                            ->where("b.code like '%{$device_code}%' and a.account_id = {$account_id}")
                            ->getField("device_id",true);
            }else{
                $device_ids = M("account_purview")->where("account_id = {$account_id}")->getField('device_id',true);
            }
        }
        if(!$device_ids){
            return false;
        }
        //where条件处理
        //处理端口
        $lines = array();
        $where_line = array();
        $portname = I('portname');
        $port_code = I('port_code');
        $where_line['device_id'] = array('IN',$device_ids);
        if($portname && $port_code){ //同时输入端口号和端口名称 这种情况是 权限设备对应的端口的名称必须是指定的才能匹配
            $where_line['code'] = array('like',"%{$port_code}%");
            $where_line['PortName'] = array('like',"%{$portname}%");
            $dids = implode(',',$device_ids);
            $lines = M("device_line")->where("device_id in ({$dids}) and code like '%{$port_code}%' and portname like '%{$portname}%'")->getField('id',true);
        }else if($portname || $port_code){ //如果指定的事端口号 那么就是指定设备的 
            $portname && $where_line['PortName'] = array('like',"%{$portname}%");
            $port_code && $where_line['code'] = array('like',"%{$port_code}%");
        }///没有选端口 那么就是指定设备的所有端口
        $lines =  M("device_line")->where($where_line)->getField('id',true);
        //所有的设备对应各自的端口数组 需要处理成where条件
        if(!$lines){
            return false;
        }
        //where条件处理
        $where['line'] = array("IN",$lines);
        
        $tel = I('tel');
        $search_type = I('search_type');
        $start_date = I('start_date');
        $end_date = I('end_date');
        $min_call = I('min_call');
        $max_call = I('max_call');
        $min_recording = I('min_recording');
        $max_recording = I('max_recording');
        $p = I('p') ? I('p') : 1;
        //处理其他device_call表本身条件
        $tel && $where['tel']= array('like',"%{$tel}%");
        $search_type && $where['type'] = $search_type;
        if($start_date && $end_date){
            $where['add_time'] = array('between',array(strtotime($start_date),strtotime($end_date)));
        }else{
            $start_date && $where['add_time'] = array('egt',strtotime($start_date));
            $end_date && $where['add_time'] = array('elt',strtotime($end_date));
        }
        if($min_call && $max_call){
            $where['call_time'] = array('between',array($min_call,$max_call));
        }else{
            $min_call && $where['call_time'] = array('egt',$min_call);
            $max_call && $where['call_time'] = array('elt',$max_call);
        }
        if($min_recording && $max_recording){
            $where['recording_time'] = array('between',array($min_recording,$max_recording));
        }else{
            $min_recording && $where['recording_time'] = array('egt',$min_recording);
            $max_recording && $where['recording_time'] = array('elt',$max_recording);
        }
        
        //查询call记录
        $count = M("device_call")->where($where)->count();
        $page_array = array();
        $page_array = $_REQUEST;
        $page_array['p'] = $p;
        unset($page_array['gids']);
        if($page_array['ids']){
            $page_array['ids'] = json_encode($device_ids);
        }
        $page = self::page($count,100,$page_array);
        if(1 == $excel){
            $callList = M("device_call")->where($where)->order("id desc")->select();
        }else{
            $callList = M("device_call")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        }
        //查询设备以及对应的端口名称
        if($device_ids){
            $where_devices['id'] = array('in',$device_ids); 
            $devices = M("devices")->where($where_devices)->getField('id,name',true);
        }
        if($lines){
            $where_lines['id'] = array('in',$lines);
            $Lines = M("device_line")->where($where_lines)->getField('id,portname,code');
        }
        if($callList){
            foreach($callList as $k=>$call){
                $callList[$k]['device_name'] = $devices[$call['device_id']];
                $callList[$k]['portname'] = $Lines[$call['line']]['portname'] ? $Lines[$call['line']]['portname'] :$Lines[$call['line']]['code'];
                $img = D("Common/DeviceCall")->icon($call['type']);
                $type = D("Common/DeviceCall")->type($call['type']);
                $callList[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $callList[$k]['type1'] = $call['type'];
                $callList[$k]['call_time'] = self::secToTime($call['call_time']);
                $callList[$k]['recording_time'] = self::secToTime($call['recording_time']);
                if(M("device_call_flag")->where("call_id = {$call['id']} and account_id = {$account_id}")->find()){
                    $callList[$k]['is_flag'] = 1;
                }else{
                    $callList[$k]['is_flag'] = 0;
                }
                //检测是否有文件
                if(!$call['files']){
                    $callList[$k]['files'] = 0;
                }else{
                    $files = D("Common/DeviceCall")->getFileDir($call['files']).D("Common/DeviceCall")->replaceForFilename($call['files']);
                    if(!is_file($files)){
                        $callList[$k]['files'] = 0;
                    }else{
                        $callList[$k]['files'] = 1;
                    }
                }
            }
        }
        $data = array();
        $data['page'] = $page->show();
        $data['callList'] = $callList;
        return $data;
    }

    /**
     * 获取两个时间段内的月份
     */
    static private function getMonthFromRange($start,$end){
        $start=date('Y-m',strtotime($start));
        $end=date('Y-m',strtotime($end));
        //转为时间戳
        $start=strtotime($start.'-01');
        $end=strtotime($end.'-01');
        $i=0;
        $d=array();
        while($start<=$end){
            //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
            $d[$i]=trim(date('Y-m',$start),' ');
            $start+=strtotime('+1 month',$start)-$start;
            $i++;
        }
        $month = array();
        foreach($d as $k => $v){ //获取每个月的开始和结束时间戳
            $month[$v]['first'] = strtotime($v);
            $month[$v]['end'] = mktime(23, 59, 59, date('m', strtotime($v))+1, 00);
        }
        return $month;
    }

    /**
     * 将s 转化为小时
    */
    private static function trantSecondToHour($seconds){
        return round($seconds/3600,2);
    }

    /**
     * 吧秒数 装换为标准格式
     */
    private  static function secToTime($times){  
        $result = '00:00:00';  
        if ($times>0) {  
                $hour = floor($times/3600);  
                $minute = floor(($times-3600 * $hour)/60);  
                $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);  
                $result = $hour.':'.$minute.':'.$second;  
        }  
        return $result;  
    }

    private function page($Total_Size = 1, $Page_Size = 0,$config) {
        import ( 'Page' );
        
        $Page = new \Think\Page ( $Total_Size, $Page_Size,$config);
        $Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');  
        $Page->setConfig('prev', '上一页');  
        $Page->setConfig('next', '下一页');  
        $Page->setConfig('last', '末页');  
        $Page->setConfig('first', '首页');  
        $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');  
        $Page->lastSuffix = false;//最后一页不显示为总页数
        return $Page;
    }
}
