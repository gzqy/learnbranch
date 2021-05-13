<?php

namespace Api\Controller;

use Think\Controller;
use Common\Utils\MailUtil;

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

    	$data = [];
    	$perStartTimeStamp = strtotime(date('Y-m-d 00:00:00',strtotime("-1 day")));
    	$perEndTimeStamp   = strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")));
    	// $year 			   = date('Y') = 2018;
    	// $month             = date('m') = 10;
    	// $day      		   = date('d') = 13;
    	$year 			   =  date('Y',$perStartTimeStamp);
    	$month             =  date('m',$perStartTimeStamp);
    	$day      		   =  date('d',$perStartTimeStamp);

       	//统计昨日每个设备上传的录音数量
    	$data['perDayPerDeviceUploadNum'] = M("device_call")->alias('a')->field('count(a.device_id) as sum,a.device_id,b.name')->join('join devices b on a.device_id=b.id')->where("a.files != '0' and a.files != 'null' and a.add_time >= $perStartTimeStamp and a.add_time <= $perEndTimeStamp")->group('a.device_id')->select();
    	// echo M()->_sql();die;

    	//统计昨日所有设备上传的录音数量
    	$data['perDayAllDeviceUploadNum'] = M("device_call")->field('count(device_id) as sum')->where("files != '0' and files != 'null' and add_time >= $perStartTimeStamp and add_time <= $perEndTimeStamp")->select();

    	//服务器昨日接受每个设备所用空间
    	$dir = [];
        $dirArr = $this->listFile(UPLOAD_PATH,$dir);
        $data['perDayPerDeviceSpace'] = [];
		if($dirArr){
			foreach ($dirArr as $key => $val) {
				$tempDir = $val.'/'.$year.'/'.$month.'/'.$day.'/';
				$deviceCode = substr($val, strrpos($val, '/')+1);
                $deviceName = M("devices")->field('name')->where("code = {$deviceCode}")->find();
                $deviceName = $deviceName?$deviceName:$deviceCode;
            
				if(is_dir($tempDir)){
					$data['perDayPerDeviceSpace'][$deviceName] = round(((exec("du -sh {$tempDir}"))/1024),4);
				}else{
					$data['perDayPerDeviceSpace'][$devicaName] = '0';
				}
				$tempDir = '';
			}
		}
		    	 
    	 //服务器昨日接受录音文件所用容量 需要加上当天
    	 // $nowDayRecordPath = UPLOAD_PATH.'/';
         $data['perDayRecoadTotalSpace'] = array_sum($data['perDayPerDeviceSpace']);
    	 // $data['perDayRecoadTotalSpace'] = round((exec("du -sh {$dir}")/1024),4).'GB';

    	//服务器硬盘总空间
    	 $data['serverHardDiskSpace'] = round((@disk_total_space('/')/(1024*1024*1024)),2);
        //服务器硬盘剩下空间
         $data['serverHardDiskFreeSpace'] = round((@disk_free_space('/')/(1024*1024*1024)),2);

    	 //总录音占用服务器硬盘总空间比
    	 $dir = UPLOAD_PATH;
    	 $data['totalRecoadAndSpacingRatio'] = (round(( (exec("du -sh {$dir}")/1024)/$data['serverHardDiskSpace']),6)*100);
         //已经使用空间占用总空间占比
         $data['freeSpaceAndSpacingRatio'] = 100 - (round($data['serverHardDiskFreeSpace']/$data['serverHardDiskSpace'],2)*100);

    	 //服务器当天接受录音文件所用容量
    	 // $nowDayRecordPath = UPLOAD_PATH.''
    	 // $data['perDayRecoadTotalSpace'] = round((exec("du -sh {$dir}")/1024),'4').'GB';

         $Pemail = new MailUtil();


         $content = '<table border="1">';
         $content .= '<tr>';
         $data['perDayAllDeviceUploadNum'] = isset($data['perDayAllDeviceUploadNum'][0]['sum'])?$data['perDayPerDeviceUploadNum'][0]['sum']:'0';
         $content .= '<td>服务器接收到当天上传录音文件的数量:'.$data['perDayAllDeviceUploadNum'].'</td>';
         $content .= '<td>服务器总容量:'.$data['serverHardDiskSpace'].'GB</td>';
         $content .= '<td>服务器已使用占用总空间占比:'.$data['freeSpaceAndSpacingRatio'].'%</td>';
         $content .= '<td>服务器录音占用总空间占比:'.$data['totalRecoadAndSpacingRatio'].'%</td>';
         $content .= '<td>服务器接收当天上传录音文件使用空间:'.$data['perDayRecoadTotalSpace'].'GB</td>';
         $content .= '</tr>';
         $content .= '<tr><th>设备名称</th><th>上传数量</th></tr>';

         if(!empty($data['perDayPerDeviceUploadNum'])){
               foreach($data['perDayPerDeviceUploadNum'] as $key => $val){
                     $content .= '<tr><td>'.$val['name'].'</td><td>'.$val['sum'].'</td></tr>';
                }
            }else{
                     $content .= '<tr><td>无</td><td>无</td></tr>';
            } 
         $content .= '<tr><th>设备名称</th><th>使用空间</th></tr>';    
         if(!empty($data['perDayPerDeviceSpace'])){
            foreach($data['perDayPerDeviceSpace'] as $key => $val){
                $content.= '<tr><td>'.$key.'</td><td>'.$val.'GB</td></tr>';
            }
         }
         $content .='</table>';


         echo $content;die;
         $Pemail->setHeader('昨日数据统计');
         $Pemail->setBody($content);
         $Pemail->setClients([['email'=>'firstycyx@126.com']]);
         $status = $Pemail->send();
        if($status){
            echo '发送成功';
           }else{
            echo '发送失败';
         }
      

    }

    public function listFile($date,$dir){
        //1、首先先读取文件夹
        $temp=scandir($date);
        //遍历文件夹
        foreach($temp as $v){
            $a=$date.'/'.$v;
           if(is_dir($a)){//如果是文件夹则执行
          
               if($v=='.' || $v=='..'){//判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
                   continue;
               }
               // echo "<font color='red'>$a</font>","<br/>"; //把文件夹红名输出
               $dir[] = $a;
             
               // $this->listFile($a);//因为是文件夹所以再次调用自己这个函数，把这个文件夹下的文件遍历出来
           }else{
            	return false;
           }
          
        }
        return $dir;
    }
    
}