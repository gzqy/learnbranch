<?php

namespace Api\Controller;

use Think\Controller;

class AppContactController extends Controller
{
    
    private $_token_key = 'HVbXAIUT1G2GRmzW'; //加密token 用于验证机制
    private $_token_open = false;
    private $_token1_open = false;
    
    function _initialize() {
        $this->_tokenCheck();
        $code = I('device_id');
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
    }
    
    public function _tokenCheck(){
        if(!$this->_token1_open){
            return 1;
        }
        $params=I('');
        if(abs(time()-$params['timestamp'])>600){
            exit(json_encode(['code'=>'0001','msg'=>'token timestamp overtime!']));
        }
        $token=md5( $params['timestamp']. $_SERVER['HTTP_USER_AGENT'] . $this->_token_key);
        if($token!=$params['token1']){
            exit(json_encode(['code'=>'0001','msg'=>'token error']));
        }
    }
    public function groupList(){
        $code = I('device_id');
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        $r=(array)M('contact_group_app')->where(['app_code'=>$code])->select();
        exit(json_encode(['code'=>'0000','data'=>$r,'msg'=>'操作成功']));
    }
    public function customerList(){
        $code = I('device_id');
        $tel1_name=I('tel1_name');
        $appGroupId = I('group_id');
        $sort = I('sort') ? 'asc' : 'desc';
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;
        $where['app_delete']=['eq',0];
        $appGroupId&&$where['app_group_id']=['eq',$appGroupId];
        if(is_numeric($tel1_name)){
            $tel=$tel1_name;
        }else{
            $name=$tel1_name;
        }
        $tel&&$where['tel1']=['like','%'.$tel.'%'];
        $name&&$where['name']=['like','%'.$name.'%'];
        $code&&$where['app_code']=['eq',$code];
        //分页处理
        $count= M("contacts")->alias("b")
            ->join("left join device_app_call c on b.tel1 = c.tel")
            ->join("inner join accounts as ct on ct.tel=b.app_code")
            ->where($where)->count();
        //查询列表
        $data=M('contacts',null)->where($where)->limit(($p-1)*$limit,$limit)->order('id '.$sort)->select();
        $data = M("contacts")->alias("b")
            ->join("left join device_app_call c on b.tel1 = c.tel")
            ->join("left join accounts as ct on ct.tel=b.app_code")
            ->where($where)
            ->limit(($p-1)*$limit,$limit)
            ->field(" sum(case when c.type='10' then 1 else 0 end) as comeing,sum(case when c.type='9' then 1 else 0 end) as outgoing,sum(case when c.type='11' then 1 else 0 end) as missed,b.*")
            ->group('tel1')
            ->order('id '.'desc')
            ->select();





//        $data = M("contacts")->alias('b')
//            ->join("right join accounts as ct on ct.tel=b.app_code")
//            ->join("left join device_app_call l on ct.tel = l.device_id")
//            ->join("left join devices_app p on ct.tel = p.code")
//            ->join("left join contact_group_app c on b.app_group_id = c.id")
//            ->where($where)
//            ->field("b.*,sum(case when l.type='10' then 1 else 0 end) as comeing,sum(case when l.type='9' then 1 else 0 end) as outgoing,sum(case when l.type='11' then 1 else 0 end) as missed")
//            ->group('b.tel1')
//            ->order("b.id desc")
//            ->limit(($p-1)*$limit,$limit)
//            ->select();


        $r1=(array)M('contact_group_app')->where(['app_code'=>$code])->select();
        $r1=array_column($r1,'name','id');
        foreach ($data as $k=>$v){
           $data[$k]['gender']=$this->_genderToChar($v['gender']);
           $data[$k]['app_group']=$v['app_group_id']==-1?'其他': (string)$r1[$v['app_group_id']];
        }
        $r['pageInfo']=[
            'pages'=>ceil($count/$limit),
            'count'=>$count,
            'page'=>$p,
            'limit'=>$limit,
        ];
        $r['recordList']=(array)$data;
        exit(json_encode(['code'=>'0000','data'=>$r,'msg'=>'success']));
    }
    public function customerRecordList(){
        $code = I('device_id');
        $id=(int)I('id');
        $sort = I('sort') ? 'asc' : 'desc';
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') :20;
        $search_type = I('event_type');
        if(!$id){
            exit(json_encode(['code'=>'0001','msg'=>'客户id不能为空']));
        }
        $contactInfo=M('contacts')->find($id);
        $tel=$contactInfo['tel1'];
//        $where['contact_id']=['eq',$id];
        $tel&&$where['tel']=['eq',$tel];
        $search_type && $where['type']=$search_type;
        $count=M('device_app_call',null)->where($where)->count();
        //查询列表
        $data=M('device_app_call',null)->where($where)->limit(($p-1)*$limit,$limit)->order('id '.$sort)->select();
        foreach ($data as $k=>$v){
            $data[$k]['stime1']=date('Y-m-d H:i:s',$v['stime']);
            //            $data[$k]['call_time']=date('Y-m-d H:i:s',$v['call_time']);
            $data[$k]['call_date1']=date('Y-m-d H:i:s',$v['call_date']);
        
            if($v['files']){
                $data[$k]['filePath']=D("Common/DeviceCall")->getAppFilePlayDir($v['files']);;
                $data[$k]['filePath'] = 'http://' .$_SERVER['HTTP_HOST'].str_replace('/index.php','',$_SERVER['SCRIPT_NAME']) . trim($data[$k]['filePath'],'.').$v['files'];
            }
        }
        $r['pageInfo']=[
            'pages'=>ceil($count/$limit),
            'count'=>$count,
            'page'=>$p,
            'limit'=>$limit,
        ];
        $r['recordList']=(array)$data;
        exit(json_encode(['code'=>'0000','data'=>$r,'msg'=>'success']));
    }
    public function addCustomer(){
        $data=I('');
        $code = $data['device_id'];
        $data['app_code']=$code;
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        $data['tel1'] = empty($data['tel1']) ? $data['app_code'] : $data['tel1'];
        if(empty($data['tel1'])){
            exit(json_encode(['code'=>'0001','msg'=>'客户手机号不能为空']));
        }
        $id=M('contacts')->where(['tel1'=>$data['tel1'],'app_code'=>$code,'app_delete'=>0])
            ->getField('id');
        if($id){
            exit(json_encode(['code'=>'0001','msg'=>'客户手机号码已存在']));
        }
        unset($data['id']);
        $data['gender']=$this->_genderToNum($data['gender']);
        if($data['group']){
//            $appGroupId=M('contact_group_app',null)->
//            where(['app_code'=>$code,'name'=>$data['group']])->getField('id');
            $data['app_group_id']=$data['group'];
        }else{
            //未分组
            $data['app_group_id']=-1;
        }
        include_once APP_PATH . '/../application/Common/Utils/FirstChar.php';
        $py     = new \FirstChar();
        $data['letter']=$py->getFirstchar($data['name']);
        M('contacts',null)->add($data);
        exit(json_encode(['code'=>'0000','msg'=>'操作成功']));
    }
    public function editCustomer(){
        $data=I('');
        $id=$data['id'];
        $code=$data['device_id'];
        if(empty($id)){
            exit(json_encode(['code'=>'0001','msg'=>'缺少参数']));
        }
        if(empty($data['tel1'])){
            exit(json_encode(['code'=>'0001','msg'=>'手机号码不能为空']));
        }
        $id1=M('contacts')->where(['id'=>['neq',$id],'tel1'=>$data['tel1'],'app_code'=>$code,'app_delete'=>0])
            ->getField('id');
        if($id1){
            exit(json_encode(['code'=>'0001','msg'=>'客户手机号码已存在']));
        }
        $data['gender']=$this->_genderToNum($data['gender']);
        if($data['group']){
            $data['app_group_id']=$data['group'];
        }
        include_once APP_PATH . '/../application/Common/Utils/FirstChar.php';
        $py     = new \FirstChar();
        $data['letter']=$py->getFirstchar($data['name']);
        M('contacts',null)->where(['id'=>$id,'app_code'=>$data['device_id']])->save($data);
        exit(json_encode(['code'=>'0000','msg'=>'修改成功']));
    }
    private function _genderToChar($num){
       $s=[1=>'男',2=>'女'];
       return $s[$num] ? $s[$num] : '';
    }
    private function _genderToNum($char){
        $s=['男'=>1,'女'=>2];
        return $s[$char] ? $s[$char] : 0;
    }
    public function addGroup(){
        $data=I('');
        $code = $data['device_id'];
        $data['app_code']=$code;
        if(empty($code)){
            exit(json_encode(['code'=>'0001','msg'=>'设备手机号不能为空']));
        }
        if(empty($data['name'])){
            exit(json_encode(['code'=>'0001','msg'=>'分组名称不能为空']));
        }
         $appGroupId=M('contact_group_app',null)->
         where(['app_code'=>$code,'name'=>$data['name']])->getField('id');
         if($appGroupId){
             exit(json_encode(['code'=>'0000','msg'=>'分组名称已经添加']));
         }
         M('contact_group_app')->add($data);
         exit(json_encode(['code'=>'0000','msg'=>'分组名称已经添加']));
    }
    public function customerInfo(){
        $data=I('');
        $id=$data['id'];
        $r=M('contacts')->find($id);
        if(!$r||$r['app_delete']||$r['app_code']!=$data['device_id']){
            exit(json_encode(['code'=>'0001','data'=>$r,'msg'=>'没有数据']));
        }else{
            $r['gender']=$this->_genderToChar($r['gender']);
            $gname=M('contact_group_app')->where(['id'=>$r['app_group_id']])->getField('name');
            $r['app_group']=$gname;
            exit(json_encode(['code'=>'0000','data'=>$r,'msg'=>'请求成功']));
        }
    }
    public function delCustomer(){
        $data=I('');
        $ids=$data['ids'];
        $ids=trim($ids,',');
        if(empty($ids)){
            exit(json_encode(['code'=>'0001','msg'=>'缺少参数']));
        }
        M('contacts')->where(['app_code'=>$data['device_id'],'id'=>['in',$ids]])->
        save(['app_delete'=>1]);
        exit(json_encode(['code'=>'0000','msg'=>'删除成功']));
    }
    public function importCustomer(){
        $code = I('device_id');
        $jsonData=I('jsonData');
        $jsonData=str_replace('&quot;','"',$jsonData);
        $data=json_decode($jsonData,true);
        if(!$data){
            exit(json_encode(['code'=>'0000','msg'=>'未发现导入数据或者导入格式错误']));
        }
        foreach ($data as $k=>$v){
            $data[$k]['tel1']=str_replace(' ','',$v['tel1']);
        }
        $data1=array_column($data,'name','tel1');
        $all=M('contacts')->where(['app_code'=>$code,'app_delete'=>0])->field('name,tel1')->select();
        $all1=array_column($all,'name','tel1');
        include_once APP_PATH . '/../application/Common/Utils/FirstChar.php';
        $py     = new \FirstChar();
        $addData=[];
        foreach ($data1 as $tel1=>$name){
            if(!isset($all1[$tel1])){
                $nameChar=$py->getFirstchar($name);
               $addData[]=[
                 'app_code'=>$code,
                 'tel1'=>$tel1,
                 'name'=>$name?$name:$tel1,
                 'letter'=>$nameChar,
                 'app_group_id'=>-1,
               ];
            }else{
                if($all1[$tel1]!=$name){
                    $nameChar=$py->getFirstchar($name);
                    M('contacts')->where(['app_code'=>$code,'tel1'=>$tel1])->
                    save(['letter'=>$nameChar,'name'=>$name]);
                }
            }
        }
        if($addData){
            M('contacts')->addAll($addData);
        }
        exit(json_encode(['code'=>'0000','msg'=>'导入成功']));
    }
    public function updateRecordNote(){
        $code = I('device_id');
        $id=I('id');
        $note=I('note');
        M('device_app_call')->where(['id'=>$id])->save(['note'=>$note]);
        exit(json_encode(['code'=>'0000','msg'=>'修改成功']));
    }
    
}
