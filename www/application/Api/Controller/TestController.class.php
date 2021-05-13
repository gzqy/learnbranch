<?php

namespace Api\Controller;

use Think\Controller;

class TestController extends Controller
{
    
	
	
	 function ferretStatus(){
		 
		$List = I('List');
		
        
		
        //查找设备是否注册
		$m = M('devices_app');
		$sql="SELECT
		IF
			( unix_timestamp( now( )) - ( a.upd_time) < 301, '1', '2' ) appStatus,
			1 onlineStatus,
			b.onlineType onlineType,
			b.revision appVersion,
			b.endtime authDate,
			b.modeln deviceInfo,
			b.last_time lastUpdatetime,
			b.last_time lastUploadtime,
			a.CODE mobile,
			
			a.NAME deviceName 
		FROM
			devices_app a
			LEFT JOIN device_app_stat b ON a.id = b.device_id 
		WHERE
			a.CODE = '".$List."'";
		$data=$m->query($sql);
	
		foreach($data as $k=>$v){
			if(empty($v['authDate']) || $v['authDate']==0  ){
				$data[$k]['appStatus']='2';
			}
			
		}

		$arrar= array('current'=>'0','errMsg'=>"string", 'items'=>$data,"result"=>'0','resultMessage'=>"string",'total'=>'0');

		 echo json_encode($arrar);exit;
        



    }
	
	function recordlist(){
		
		
		$List = I('List');
	

		$current = I('current');//条
		$startTime = I('startTime');
		$endTime = I('endTime');
		
		
		if(empty($List)  || !is_array(I('List'))  ){
			$arrar= array('current'=>'0','errMsg'=>"string", 'items'=>$data,"result"=>'-1','resultMessage'=>"手机号列表异常",'total'=>'0');
			echo json_encode($arrar);exit;	
		}
		
		if(empty($endTime) &&  empty($endTime)){
			$arrar= array('current'=>'0','errMsg'=>"string", 'items'=>$data,"result"=>'-2','resultMessage'=>"请传入正确的时间",'total'=>'0');
			echo json_encode($arrar);exit;	
		}
		
		if(empty($current)){
			$arrar= array('current'=>'0','errMsg'=>"string", 'items'=>$data,"result"=>'-2','resultMessage'=>"请传入正确的条数",'total'=>'0');
			echo json_encode($arrar);exit;	
		}
	
		
		
		
	
		$List=   implode(",",$List);
		
		
		$List ='a.CODE in ('.$List.')' ;
        $stime=' AND  stime between '.$startTime.'  and '.$endTime . ' AND  b.id> '.$current;
		$limit= " limit $current, 20";
		
		$where=$List.$stime.$limit;
		$where1=$List.$stime;

		$wherecount=$List.$stime;
        //查找设备是否注册
		$m = M('devices_app');
		$sql1="SELECT  count(1)  count
			FROM
			devices_app a
			LEFT JOIN device_app_call b ON a.id = b.device_id 
		WHERE  $where1 
			   ";
		$data1=$m->query($sql1);
		

		$sql="SELECT
		IF	( b.type < 10,  b.tel,a.code ) recMobile,
		IF	( b.type >= 10, a.code, b.tel )  callMobile,
			a.code deviceMobile    ,
			b.stime callTime,
			b.call_time durtime
		FROM
			devices_app a
			LEFT JOIN device_app_call b ON a.id = b.device_id 
		WHERE  $where  ";
		$data=$m->query($sql);
		$arrar= array('current'=>'0','errMsg'=>"string", 'items'=>$data,"result"=>'0','resultMessage'=>"string",'total'=>$data1[0]['count']);

		 echo json_encode($arrar);exit;
        



    }
	
	
	public function updateLastTime(){
	
        $code = I('code');
        $revision = I('revision');
        $modeln = I('modeln');
        $endtime = I('endtime');
        $onlineType = I('onlineType');
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        $deviceId=M('devices_app',null)->where(['code'=>$code])->getField('id');
        M('device_app_stat',null)->where(['device_id'=>$deviceId])->save(['last_time'=>time(),'revision'=>$revision,'modeln'=>$modeln,'endtime'=>$endtime,'onlineType'=>$onlineType]);
        exit(json_encode(['code'=>'0000','msg'=>'success']));

     }
	 
	 
	 
	 function getFileDir($filename=null){
		$filename='20200918-20210409074806-O-L01-EN-100867.wav';
        if ( empty($filename) ) {
            return false;
        }
       
         $a= substr($filename,0,8);
         $b=substr($filename,9,4);
         $c=substr($filename,13,2);
         $d=substr($filename,15,2);
         $e=substr($filename,18,2);

         $a1=$b;
         $b=$a1.$c;
         $c=$b.$d;

         $code1=substr($filename,27,2);
         



         $data = M("devices")
             ->alias("a")
             ->join("inner join device_line b on a.id=b.device_id")
             ->where("a.code='$a' && b.code= '$code1'")
             ->getField("b.PortName");
		print_r(M()->_sql());
         if($data  &&  !empty($data)) {
             $d=$code1.'-'.$data;
             $file_dir = UPLOAD_PATH.'/'.$a.'/'.$a1.'/'.$b.'/'.$c.'/'.$d.'/';

             return $file_dir;
         }else{ //设备未注册
             $file_dir = UPLOAD_PATH.'/'.$a.'/';
             return $file_dir;
         }





    }


}