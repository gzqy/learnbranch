<?php
namespace Admin\Controller;
use Common\Controller\ShopbaseController;
/**
 * 通讯录板块
 */
class ConcatController extends ShopbaseController {
	protected $nav_id = 6;
    protected $second_nav = array();
    public static $feedbackTypeList=[];
    function _initialize(){
        parent::_initialize ();
        $second_nav = array(
            array('id'=>1,'a'=>U('/Concat/index'),'name'=>'联系人'), 
            array('id'=>2,'a'=>U('/Concat/GroupList'),'name'=>'通讯组'), 
        ); 
        $this->assign('nav_id',$this -> nav_id);
        $this->assign('second_nav',$second_nav);
    }

    /**
     * 联系人列表
     * @param p 分页当前页 默认1
     * @param limit 分页页数 默认20
     * @param name 姓名
     * @param tel1 电话号码
     * @param gender 性别 0 所有 1 男 2 女
     * @param group_id 所属组 0 全部 
     * @param add_start_date 注册最小时间
     * @param add_end_date 注册最打时间
     * @param upd_start_date 更新最近时间
     * @param upd_end_date 更新最大时间
     */
    public function index(){
        $p = I('p') ? I('p') : 1;
        $limit = I('limit') ? I('limit') : 20;
        $name = I('name');
        $tel1 = I('tel1');
        $gender = I('gender') ? I('gender') : 0;
        $group_id = I('group_id');
        $add_start_date = I('add_start_date');
        $add_end_date = I('add_end_date');
        $upd_start_date = I('upd_start_date');
        $upd_end_date = I('upd_end_date');

        //where条件处理
        $name && $where['a.name'] = array('like',"%{$name}%");
        $tel1 && $where['a.tel1'] = array('like',"%{$tel1}%");
        $gender && $where['a.gender'] = $gender;
        $group_id && $where['a.group_id'] = $group_id;
        if( $add_start_date && $add_end_date){
            $where['a.add_time'] = array('between',"{$add_start_date},{$add_end_date}");
        }else{
            $add_start_date && $where['a.add_time'] = array('gt',$add_start_date);
            $add_end_date && $where['a.add_time'] = array('lt',$add_end_date);
        }
        if($upd_start_date && $upd_end_date){
            $where['a.upd_time'] = array('between',"{$upd_start_date},{$upd_end_date}");
        }else{
             $upd_start_date && $where['a.upd_time'] = array('gt',$upd_start_date);
            $upd_end_date && $where['a.upd_time'] = array('lt',$upd_end_date);
        }
       
        //分页处理
        $count = M("contacts")->alias('a')
                ->join("left join contact_stat b on a.id = b.contact_id")
                ->where($where)
                ->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"tel1"=>$tel1,"gender"=>$gender,"group_id"=>$group_id,"add_start_date"=>$add_start_date,"add_end_date"=>$add_end_date,'upd_start_date'=>$upd_start_date,'upd_end_date'=>$upd_end_date));
        //查询数据
        $data = M("contacts")->alias('a')
            ->join("left join contact_stat b on a.id = b.contact_id")
            ->join("left join contact_group c on a.group_id = c.id")
            ->where($where)
            ->field("a.id,a.name,a.gender,a.tel1,a.group_id,a.add_time,a.upd_time,b.comeing,b.outgoing,b.missed,b.videod,c.name as gname")
            ->order("a.id desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        //获取分组
        $groups = D("Common/ContactGroup")->getOption();//通讯组    
        $this->accountLogs->addLog("查看联系人列表，查询条件：联系人名称：{$name},联系人电话{$tel1},联系人分组id：{$group_id},联系人性别：{$gender},联系人注册时间最小值：{$add_start_date},联系人注册时间最大值：{$add_end_date},联系人更新最近时间：{$upd_end_date},联系人更新最远时间：{$upd_start_date}");
        $this->assign('name',$name);
        $this->assign('tel1',$tel1);
        $this->assign('gender',$gender);
        $this->assign('group_id',$group_id);
        $this->assign('add_start_date',$add_start_date);
        $this->assign('add_end_date',$add_end_date);
        $this->assign('upd_start_date',$upd_start_date);
        $this->assign('upd_end_date',$upd_end_date);
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('Page',$page->show());
        $this->assign('groups',$groups);
        $this->assign('second_nav_id',1);
        $this->display();
    }

    /**
     * 导出联系人
     */
    public function index_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "联系人记录";
                $header = array(
                            array('name','名称'),
                            array('tel1','电话'),
                            array('gender','性别'),
                            array('gname','分组'),
                            array('comeing','来电'),
                            array('outgoing','去电'),
                            array('missed','未接'),
                            array('add_time','注册时间'),
                            array('upd_time','更新时间'),
                        );
                foreach($data as $k=>$v){
                    if(1 == $v['gender']){
                        $data[$k]['gender'] =  iconv('utf-8','gbk','男');
                    }else if(2 == $v['gender']){
                        $data[$k]['gender'] =  iconv('utf-8','gbk','女');
                    }else{
                        $data[$k]['gender'] =  iconv('utf-8','gbk','保密');
                    }
                }
                $path = $this->exportExcel($title,$header,$data);
                ob_end_clean();// 就是加这句

                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename='.$path);
                header('Cache-Control: max-age=0');
                $this->ajaxReturn(array('status'=>200,'url'=>$path));
            }else{
               exit('非法请求'); 
            }
        }else{
            exit('非法请求');
        }
    }

    /**
     * 添加、编辑联系人
     * @param id 联系人id
     */
    public function add(){
    	$id = I('id');
    	if($id){ //编辑
    		$data = M("contacts")->find($id);
    		if(!$data){
    			$this->error("联系人不存在");
    		}
    		$this->assign('data',$data);
    		$this->assign('title',2);
    	}else{ //添加
    		$this->assign('title',1);
    	}
    	$groups = D("Common/ContactGroup")->getOption();//通讯组
    	$this->assign('groups',$groups);
    	$this->assign('second_nav_id',1);
    	$this->display();
    }

    /**
     * 保存添加、编辑联系人
     * @param name 联系人名称
     * @param gender 联系人性别 1 男 2 女
     * @param group_id 联系人分组
     * @param tel1 联系人手机
     * @param birthday 联系人生日
     * @param company 联系人公司
     * @param position 联系人职位
     * @param country 联系人国家
     * @param province 联系人省份
     * @param city 联系人国家
     * @param address 联系人地址
     * @param email 联系人邮箱
     * @param fax 联系人传真
     * @param tel2 联系人联系方式2
     * @param tel3 联系人联系方式3
     * @param note 备注
     * @param id 联系人id 编辑使用 
     */
    public function save(){
        if(IS_AJAX){
            $data = I('data');
            if(!$data['name'] || !$data['group_id'] || !$data['tel1']){
                echo json_encode(array('code'=>300,'msg'=>'请填写联系人名称分组和联系方式'));exit;
            }
            if($data['id']){ //编辑联系人
                //判断id是否正确
                if(!M('contacts')->find($data['id'])){
                    echo json_encode(array('code'=>300,'msg'=>'联系人不存在'));exit;
                }
                $data['upd_time'] = date('Y-m-d H:i:s',time());
                $data['keywords'] = D("Common/Contact")->getKeywords($data);
                if(M('contacts')->save($data)){
                    $this->accountLogs->addLog("编辑联系人：联系人id{$data['id']}");
                    echo json_encode(array('code'=>200,'msg'=>'编辑联系人成功'));exit;
                }else{
                    echo json_encode(array('code'=>300,'msg'=>'编辑联系人失败'));exit;
                }
            }else{ //添加联系人
                if(M('contacts')->where("name = '{$data['name']}'")->find()){
                    echo json_encode(array('code'=>300,'msg'=>'联系人已存在'));exit;
                }
                $data['add_time'] = date('Y-m-d H:i:s',time());
                $data['upd_time'] = date('Y-m-d H:i:s',time());
                $data['keywords'] = D("Common/Contact")->getKeywords($data);
                print_r($data);

                if($id = M("contacts")->add($data)){
                    M("contact_stat")->add(array('contact_id'=>$id)); //添加联系人统计一行
                    $this->accountLogs->addLog("添加联系人：联系人id{$id}");
                    echo json_encode(array('code'=>200,'msg'=>'添加联系人成功'));exit;
                }else{
                    echo json_encode(array('code'=>300,'msg'=>'添加联系人失败'));exit;
                }
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 删除联系人
     * @param id 联系人id
     */
    public function delete(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!M("contacts")->find($id)){
                echo json_encode(array('code'=>300,'msg'=>'联系人不存在'));exit;
            }
            M("contact_stat")->where("contact_id = {$id}")->delete();//删除联系人统计数据
            //删除
            if(M("contacts")->delete($id)){
                $this->accountLogs->addLog("删除联系人：联系人id{$id}");
                echo json_encode(array('code'=>200,'msg'=>'删除联系人成功'));exit; 
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除联系人失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 通讯组列表
     * @param p 分页 当前页默认为1
     * @param limit 分页 页数默认20
     * @param name 组名称
     * @param add_time_min 添加最小时间
     * @param add_time_max 添加最大时间
     * @param upd_time_min 修改最小时间
     * @param upd_time_max 修改最大时间
     */
    public function GroupList(){
    	$p = I("p") ? I("p") : 1;
        $limit = I("limit") ? I("limit") : 20;
        $name  = I("name");
        $add_time_min = I('add_time_min');
        $add_time_max  =I('add_time_max');
        $upd_time_min = I('upd_time_min');
        $upd_time_max = I('upd_time_max');

        //where条件处理
        $name && $where['name'] = array('like',"%{$name}%");
        if($add_time_min && $add_time_max){
            $where['add_time'] = array('between',"{$add_time_min},{$add_time_max}");
        }else{
            $add_time_min && $where['add_time'] = array('gt',$add_time_min);
            $add_time_max && $where['add_time'] = array('lt',$add_time_max);
        }
         if($upd_time_min && $upd_time_max){
            $where['upd_time'] = array('between',"{$upd_time_min},{$upd_time_max}");
        }else{
            $upd_time_min && $where['upd_time'] = array('gt',$upd_time_min);
            $upd_time_max && $where['upd_time'] = array('lt',$upd_time_max);
        }
       
        //分页处理
        $count = M("contact_group")->where($where)->count();
        $page = $this->page($count,$limit,array('p'=>$p,"name"=>$name,"add_time_min"=>$add_time_min,"add_time_max"=>$add_time_max,"upd_time_min"=>$upd_time_min,"upd_time_max"=>$upd_time_max));
        //查询列表
        $data = M("contact_group")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();
        //统计查询每个分组的设备数量
        foreach($data as $k=>$v){
            $data[$k]['num'] = M("contacts")->where("group_id = {$v['id']}")->count();
        }
        $this->accountLogs->addLog("查询通讯组，查询条件：分组名称：{$name},添加最小时间：{$add_time_min},添加最大时间：{$add_time_max},修改最小时间：{$upd_time_min},修改最大时间：{$upd_time_max}");
        $this->assign('name',$name);
        $this->assign('add_time_min',$add_time_min);
        $this->assign('add_time_max',$add_time_max);
        $this->assign('upd_time_min',$upd_time_min);
        $this->assign('upd_time_max',$upd_time_max);
        $this->assign('data',$data);
        $this->assign('gdata',json_encode($data));
        $this->assign('Page',$page->show());
        $this->assign('second_nav_id',2);
        $this->display();
    }

    /**
     * 通讯组导出
     */
    public function GroupList_excel(){
        if(IS_POST){
            if(1 == I('excel')){
                $data = json_decode(htmlspecialchars_decode(I('data')),1);
                if(!$data){
                    $this->ajaxReturn(array('status'=>100,'msg'=>'没有数据可导出'));
                }
                $title = "通讯组";
                $header = array(
                            array('name','分组名称'),
                            array('num','联系人数量'),
                            array('add_time','添加时间'),
                            array('upd_time','更新时间'),
                        );
                foreach($data as $k=>$v){
                    $data[$k]['num'] = $v['num'] ? $v['num'] :0;
                }
                $path = $this->exportExcel($title,$header,$data);
                $this->ajaxReturn(array('status'=>200,'url'=>$path));
            }else{
               exit('非法请求'); 
            }
        }else{
            exit('非法请求');
        }
    }

    /**
     * 添加、编辑通讯组
     * @param id通讯组id
     */
    public function addGroup(){
    	$id = I('id');
    	if($id){ //编辑
    		$group = M("contact_group")->find($id);
    		if(!$group){
    			$this->error("通讯组不存在");
    		}
    		$this->assign('data',$group);
    		$this->assign('title',2);
    	}else{ //添加
    		$this->assign('title',1);
    	}
    	$this->assign('second_nav_id',2);
    	$this->display();
    }

    /**
     * 保存添加、编辑通讯组
     * @param id 通讯组的id
     * @param name 通讯组名称
     */
    public function saveGroup(){
    	if(IS_AJAX){
            $data = I('data');
            if($data['id']){ //编辑
                if(!M('contact_group')->find($data['id'])){
                     echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
                }
                $data['upd_time'] = date("Y-m-d H:i:s",time());
                if(M("contact_group")->save($data)){
                    $this->accountLogs->addLog("编辑通讯组，组id：{$data['id']}");
                     echo json_encode(array('code'=>200,'msg'=>'编辑分组成功'));exit;
                }else{
                     echo json_encode(array('code'=>200,'msg'=>'编辑分组失败'));exit;
                }
            }else{ //添加
                if(M("contact_group")->where(array("name"=>$data['name']))->find()){
                     echo json_encode(array('code'=>200,'msg'=>'分组已经存在'));exit;
                }else{
                    //添加分组
                    $data['add_time'] = date("Y-m-d H:i:s",time());
                    $data['upd_time'] = date("Y-m-d H:i:s",time());
                    if($id = M("contact_group")->add($data)){
                        $this->accountLogs->addLog("添加通讯组，组id：{$id}");
                        echo json_encode(array('code'=>200,'msg'=>'添加分组成功'));exit;
                    }else{
                        echo json_encode(array('code'=>200,'msg'=>'添加分组成功'));exit;
                    }
                }
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 删除通讯组
     * @param id 通讯组的id
     */
     public function deleteGroup(){
        if(IS_AJAX){
            $id = I('id','int');
            if(!M("contact_group")->find($id)){
                echo json_encode(array('code'=>300,'msg'=>'分组不存在'));exit;
            }
            //进行删除
            //首先将本分组下的联系人分组更换为0
            M("contacts")->where("group_id = {$id}")->save(array("group_id"=>0));
            //删除分组
            if(M("contact_group")->delete($id)){
                $this->accountLogs->addLog("删除通讯组，组id：{$id}");
                echo json_encode(array('code'=>200,'msg'=>'删除分组成功'));exit; 
            }else{
                echo json_encode(array('code'=>300,'msg'=>'删除分组失败'));exit;
            }
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit;
        }
    }

    /**
     * 批量删除通讯组
     * @param ids id集合
     */
    public function deleteAllGroup(){
        if(IS_POST){
            $ids = I('ids');//通讯组id集合
            $gids = implode($ids,',');
            if(!$gids){
                echo json_encode(array('code'=>300,'msg'=>'请选择要删除的分组'));exit; 
            }
            foreach($gids as $k=>$v){
                if(!M('contact_group')->find($v)){
                    continue;
                }
                M("contacts")->where("group_id = {$v}")->save(array("group_id"=>0));
                M("contact_group")->delete($v);
            }
            echo json_encode(array('code'=>200,'msg'=>'删除分组成功'));exit; 
        }else{
            echo json_encode(array('code'=>300,'msg'=>'请求方式错误'));exit; 
        }
    }
}