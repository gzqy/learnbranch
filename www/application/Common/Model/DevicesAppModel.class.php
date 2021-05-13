<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DevicesAppModel extends CommonModel {
    protected $table = 'devices_app';
    public $timestamps = false;

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
        $start_date = strtotime(I('start_date'));
        $end_date =  strtotime(I('end_date'));
        $status = I('status');
        $account_id = $_SESSION['ACCOUNTS']['id'];
        //处理where条件
        $where = array();
        $where['a.account_id'] = $account_id;
        $where['b.registered'] = '1';
        $where['b.closed'] = '0';
        $group_id && $where['a.group_id'] = $group_id;
        $name && $where['b.name'] = array('like',"%{$name}%");
        $code && $where['b.code'] = array('like',"%{$code}%");
        $keywords && $where['b.keywords'] = array('like',"%{$keywords}%");
       
		
		
		 if($start_date && $end_date){
            $where['b.add_time'] = array('between',"{$start_date},{$end_date}");
        }else{
            $start_date && $where['b.add_time'] = array('gt',$start_date);
            $end_date && $where['b.add_time'] = array('lt',$end_date);
        }
        
        $last_time = time() - $this->_last_time;//是否在线时间标准
    
        //获取总的设备数  在线设备数  离线设备数
        $dcounts = array();
        $dcounts['total'] = M('account_app_purview')->alias('a')
            ->join('left join devices_app b on a.device_id = b.id')
            ->join('left join device_app_stat c on a.device_id = c.device_id')
            ->where($where)
            ->count();
        $last_time = time() - $this->_last_time;//是否在线时间标准
        $dcounts['inline'] = M('account_app_purview')->alias('a')
            ->join('left join devices_app b on a.device_id = b.id')
            ->join('left join device_app_stat c on a.device_id = c.device_id')
            ->where($where)
            ->where("c.last_time >= {$last_time}")
            ->count();
        $dcounts['out'] = $dcounts['total'] - $dcounts['inline'];
    
        //查询设备列表
        if(1 == $status){
            $where['c.last_time'] = array('egt',$last_time);
        }else if(2==$status){
            $where['c.last_time'] = array('lt',$last_time);
        }
        
        //查询设备列表
        $count = M('account_app_purview')->alias('a')
              ->join('left join devices_app b on a.device_id = b.id')
              ->join('left join device_app_stat c on a.device_id = c.device_id')
              ->where($where)
              ->count();
        //分页
        $page = $this->page($count,20,array('p'=>$p,"group_id"=>$group_id,"keywords"=>$keywords,"name"=>$name,"code"=>$code,"start_date"=>$start_date,"end_date"=>$end_date,'status'=>$status));
        $data = M('account_app_purview')->alias('a')
              ->join('left join devices_app b on a.device_id = b.id')
              ->join('left join device_app_stat c on a.device_id = c.device_id')
              ->where($where)
              ->limit($page->firstRow.','.$page->listRows)
              ->order('b.id asc')
              ->field('a.attention,b.name,b.id,c.comeing,c.outgoing,c.missed,c.last_time,b.code,b.add_time,b.upd_time,b.code,c.revision,c.modeln,c.endtime,c.onlineType')
              ->select();

        foreach ($data as $k=>$v){
            if($v['last_time'] >= $last_time){


                $data[$k]['status'] = 1;
            }else{
                $data[$k]['status'] = 0;
            }


        }
		
        $data1 = array();
        $data1['data'] = $data;
        $data1['page'] = $page->show();
        $data1['status'] = $dcounts;
        return $data1;
    }

    /**
     * 获取所有app设备并按组分好
     */
     function getDevices(){
        $where = array();
        $where['a.registered'] = '1'; //已经注册的
        $where['a.closed'] = '0'; //没有删除的
        $data = $this->alias('a')->join("left join device_group b on a.group_id = b.id")->field('a.name ,a.code,a.id,a.group_id,b.name as gname,b.id as gid')->where($where)->select();
        //对结果进行分组
        foreach($data as $k=>$v){
            $_data[$v['group_id']]['id'] = $v['gid'];
            $_data[$v['group_id']]['name'] = $v['gname'];
            $_data[$v['group_id']]['devices'][$k] = $v;   
        }
        unset($data);
        return $_data;
    }

    function getData($account_id){
      if ( empty($account_id) ) {
          return false;
      }
      $where = array();
      $where['a.account_id'] = $account_id;
      $data = M("account_app_purview")->alias('a')->join('left join devices_app b on a.device_id = b.id')->join("left join device_group d on a.group_id = d.id")->join('left join device_app_stat c on a.device_id = c.device_id')->field('a.group_id,a.device_id,b.name,c.last_time,d.name as gname')->where($where)->select();
      //对结果进行分组
      foreach($data as $k=>$v){
          $_data[$v['group_id']][$k] = $v;   
          $_data[$v['group_id']]['gname'] = $v['gname'];
          //判断是否在线
          $_data[$v['group_id']][$k]['status'] = $v['last_time'] >= (time() - $this->_last_time) ? 1 : 0;
      }
      unset($data);
      //计算每个分组总数和在线
      foreach($_data as $k=>$v){
          $_data[$k]['total'] = count($_data[$k]) - 1;
          $_data[$k]['inline'] = 0;
          $_data[$k]['out'] = 0;
          foreach($v as $key =>$val){
              if(is_int($key)){
                  if(1 == $val['status']){
                      $_data[$k]['inline'] += 1;
                  }else{
                      $_data[$k]['out'] += 1;
                  }
              }
          }
          
      }
      return $_data;
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
        $attention = M('account_app_purview')->where(array('account_id'=>$account_id,'device_id'=>$id))->find();
        if($attention){
            return true;
        }else{
            return false;
        }
    }

   /**
     * 获取设备播放列表
     * @param $id 设备id
     */
    public function getCallList($id){
        $p = I('p') ? I('p') : 1;
        $start_date = I('start_date');
        $end_date = I('end_date');
        $start_call_time = I('start_call_time');
        $end_call_time = I('end_call_time');
        $search_type = I('search_type');
         $max_recording = I('max_recording','','int');
        $min_recording = I('min_recording','','int');
        $tel = I('tel');
        $keywords = I('keywords');
        $sort = I('sort');
        $sort = empty($sort) ? 'time' : $sort;
        //where条件处理
        $where = array();
        $where['a.device_id'] = $id;
        if($start_date && $end_date){
            $a = strtotime($start_date);
            $b = strtotime($end_date);
            $where['a.add_time'] = array('between',"{$a},{$b}");
        }else{
            $start_date && $where['a.add_time'] = array('gt',strtotime($start_date));
            $end_date && $where['a.add_time'] = array('lt',strtotime($end_date));
        }
        
        $start_call_time && $where['a.call_time'] = array('gt',$start_call_time);
        $end_call_time && $where['a.call_time'] = array('lt',$end_call_time);
        $search_type && $where['a.type'] = $search_type;
        $max_recording && $where['a.recording_time'] = array('lt',$max_recording);
        $min_recording && $where['a.recording_time'] = array('gt',$min_recording);
        $tel && $where['a.tel'] = array('like',"%{$tel}%");
        $keywords && $where['a.keywords'] = array('like',"%{$keywords}%");

        //排序处理
        switch ($sort) {
            case 'type':
                $order = 'a.type asc';
                break;
            case 'recording':
                $order = 'a.recording DESC';
                break;
            case 'call':
                $order = 'a.call DESC';
                break;
            case 'tel':
                $order = 'a.tel DESC';
                break;
            default:
                $order = 'a.add_time DESC';
                break;
        }
        $count = M("device_app_call")->alias("a")
            ->where($where)
            ->field("a.id")
            ->order($order)
            ->count();
        $limit=min(5000,I('limit')?(int)I('limit'):20);
        $page = $this->page($count,$limit,array('p'=>$p,'limit'=>$limit,"sort"=>$sort,"search_type"=>$search_type,"search_line"=>$search_line,"end_call_time"=>$end_call_time,"start_date"=>$start_date,"end_date"=>$end_date,'start_call_time'=>$start_call_time,'id'=>$id,'max_recording'=>$max_recording,'min_recording'=>$min_recording,'tel'=>$tel,'keywords'=>$keywords));
        $data = M("device_app_call")->alias("a")
            ->join("left join contacts c on a.tel = c.tel1")
            ->where($where)
            ->field("a.*")
            ->order($order)
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
		
        if($data){
            foreach($data as $k=>$v){
                //时间 s 转化为时间格式
                //录音类型判断
              $data[$k]['type1'] = $v['type'];
                $img = D("Common/DeviceCall")->icon($v['type']);
                $type = D("Common/DeviceCall")->type($v['type']);
                if($v['type']==60){
                    $img=[
                        'icon'=>'VoiceMessage.png',
                        'color'=>'success  message',
                    ];
                    $type='现场录音';
                }
                $data[$k]['type'] = "<img src='./public/pc/".$img['icon']."' height='20' align='absmiddle' />".$type;
                $data[$k]['call_time'] = D("Common/DeviceCall")->getTime($v['call_time']);
                $data[$k]['recording_time'] = D("Common/DeviceCall")->getTime($v['recording_time']);
            }
        }
        $data1 = array();
        $data1['data'] = $data;
        $data1['page'] = $page->show();
        return $data1;
    }
}