<?php
/**
 * Created by PhpStorm.
 * User: zxl
 * Date: 2020/7/28
 * Time: 9:11
 */

namespace Api\Controller;

use Think\Controller;

class StatisticsController extends Controller
{
    public function index(){
        $r=M('devices')->select();
        $endTime=strtotime(date('Y-m-d'));
        $startTime=$endTime-3600*24;
        $endDate=date('Y-m-d');
        $startDate=date('Y-m-d',strtotime('-1 day'));
        $sql="select count(1) as total,device_id from device_call
        where call_date>='{$startDate}' and call_date<'{$endDate}' and files!='null' group by device_id";
        $counts=M('devices')->query($sql);
        $counts=array_column($counts,'total','device_id');
        $pathEnd=date('Y').'/'.date('m').'/'.date('d');
        $totalSize=0;
        $totalCount=0;
        echo date('Y-m-d',time()-3600*24)."统计情况\n";
        foreach ($r as $v){
            $path=UPLOAD_PATH.'/'.$v['code'].'/'.$pathEnd;
            $size=((int)exec("du -sk $path"))/1024;
            $totalSize+=$size;
            $size.='M';
            $count=(int)$counts[$v['id']];
            $totalCount+=$count;
            echo "设备名称：{$v['name']}[{$v['code']}],当天上传录音容量：{$size},录音数量：{$count} \n";
        }
    
        $a['free'] = round((@disk_free_space('/')/(1024*1024*1024)),2);
        $a['total'] = round((@disk_total_space('/')/(1024*1024*1024)),2);
        $recordPath=UPLOAD_PATH;
        $recordTotal=exec("du -bs $recordPath");
        $recordTotal=intval($recordTotal);
        $a['total_record'] = round($recordTotal/(1024*1024*1024),2);
        $a['use'] = $a['total'] - $a['free'];
        $a['round'] = 100 * round($a['use'] / $a['total'],2);
        $a['round_record'] = 100 * round($a['total_record'] / $a['total'],2);
        $totalSize.='M';
        echo "当天上传录音总容量：{$totalSize}G,总录音数量:{$totalCount}\n";
        echo "系统总容量：{$a['total']}G,已使用容量：{$a['use']}G, \n
        使用百分比：{$a['round']}%,剩余容量：{$a['free']}G,\n
        录音占用容量:{$a['total_record']}G, 录音占用容量百分比：{$a['round_record']}%\n";
    }
}