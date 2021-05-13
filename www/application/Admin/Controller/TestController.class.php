<?php

namespace Admin\Controller;
use Common\Controller\ShopbaseController;
use PHPExcel_IOFactory;
use PHPExcel;

/**
 * 拨号推送板块
 */
class TestController extends ShopbaseController
{
    protected $nav_id = 6;
    protected $second_nav = array();
    // 全部是为了方便 下拉列表
    protected $feedbackTypeList = ['全部', '未选择意向', '有意向', '无意向', '拒绝'];
    
    function _initialize()
    {
        parent::_initialize();
        $second_nav = array(
            array('id' => 1, 'a' => U('/Concat/index'), 'name' => '联系人'),
            array('id' => 2, 'a' => U('/Concat/GroupList'), 'name' => '通讯组'),
//            array('id' => 3, 'a' => U('/Concat/statistics'), 'name' => '统计'),
        );
        $this->assign('nav_id', $this->nav_id);
        $this->assign('second_nav', $second_nav);
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
     * 数据量很大 影响到查询速度可以根据注册时间缩小范围x!!!!!!!s
     */
    public function index()
    {
        $p                  = i('p') ? i('p') : 1;
        $limit              = i('limit') > 0 ? i('limit') : 20;
        $limit              = min($limit, 1000);
        $name               = i('name');
        $tel1               = i('tel1');
        $gender             = i('gender') ? i('gender') : 0;
        $group_id           = i('group_id');
        $select_userid      = i('select_userid');
        $add_start_date     = i('add_start_date');
        $add_end_date       = i('add_end_date');
        $upd_start_date     = i('upd_start_date');
        $upd_end_date       = i('upd_end_date');
        $feedbacktypeform   = (int)i('form_feedback');
        $form_select_userid = (int)i('form_select_userid');
        $form_is_dailed     = (int)i('form_is_dailed');
        $tel = i('tel');
        //where条件处理
        $name && $where['a.name'] = array('like', "%{$name}%");
        $tel1 && $where['a.tel1'] = array('like', "%{$tel1}%");
        $gender && $where['a.gender'] = $gender;
        $group_id && $where['a.group_id'] = $group_id;
        
        $feedbackTypeForm && $where['a.feedback_type'] = $feedbackTypeForm;
        if ($add_start_date && $add_end_date) {
            $where['a.add_time'] = array('between', "{$add_start_date},{$add_end_date}");
        } else {
            $add_start_date && $where['a.add_time'] = array('gt', $add_start_date);
            $add_end_date && $where['a.add_time'] = array('lt', $add_end_date);
        }
        if ($upd_start_date && $upd_end_date) {
            $where['a.upd_time'] = array('between', "{$upd_start_date},{$upd_end_date}");
        } else {
            $upd_start_date && $where['a.upd_time'] = array('gt', $upd_start_date);
            $upd_end_date && $where['a.upd_time'] = array('lt', $upd_end_date);
        }
        
        $isadmin = $this->account_id == 10000000;
        if (!$isadmin) {
            $form_is_dailed && $where['ct.is_dailed'] = $form_is_dailed;
            $where['ct.account_id'] = ['eq', $this->account_id];
            //分页处理
            $count = m("contacts")->alias('a')
                ->join("inner join contacts_to_accounts as ct on ct.contact_id=a.id")
                ->join("left join contact_stat b on a.id = b.contact_id")
                ->where($where)
                ->count();
        } else {
            $form_is_dailed && $where['a.is_dailed'] = $form_is_dailed;
            if ($form_select_userid != 0) {
                //                -1">未分配 -2 已经分配
                if ($form_select_userid == -1 || $form_select_userid == -2) {
                    $allcontactstoaccounts = m('contacts_to_accounts', null)->query("select distinct contact_id from contacts_to_accounts");
                } elseif ($form_select_userid > 0) {
                    $allcontactstoaccounts = m('contacts_to_accounts', null)->query("select distinct contact_id from contacts_to_accounts where account_id=" . (int)$form_select_userid);
                }
                $allcontactstoaccounts   = array_column($allcontactstoaccounts, 'contact_id');
                $allcontactstoaccounts[] = -1;
                if ($form_select_userid == -1) {
                    $where['a.id'] = ['not in', $allcontactstoaccounts];
                } else {
                    $where['a.id'] = ['in', $allcontactstoaccounts];
                }
            }
            //分页处理
            $count = m("contacts")->alias('a')
                ->join("left join contact_stat b on a.id = b.contact_id")
                ->where($where)
                ->count();
        }
        $page = $this->page($count, $limit, array('tel'=>$tel,'p' => $p, 'limit' => $limit, "name" => $name, "tel1" => $tel1, "gender" => $gender, "group_id" => $group_id, "add_start_date" => $add_start_date, "add_end_date" => $add_end_date, 'upd_start_date' => $upd_start_date, 'upd_end_date' => $upd_end_date, 'form_feedback' => $feedbacktypeform, 'form_is_dailed' => $form_is_dailed));
        if (!$isadmin) {
            //查询数据
            $data = m("contacts")->alias('a')
                ->join("inner join contacts_to_accounts as ct on ct.contact_id=a.id")
                ->join("left join contact_stat b on a.id = b.contact_id")
                ->join("left join contact_group c on a.group_id = c.id")
                ->where($where)
                ->field("a.feedback_type,a.id,a.name,a.gender,a.tel1,a.group_id,a.add_time,a.upd_time,ct.is_dailed,b.comeing,b.outgoing,b.missed,b.videod,c.name as gname")
                ->order("a.id desc")
                ->limit($page->firstrow . ',' . $page->listrows)
                ->select();
        } else {
            //查询数据
            $data = m("contacts")->alias('a')
                ->join("left join contact_stat b on a.id = b.contact_id")
                ->join("left join contact_group c on a.group_id = c.id")
                ->where($where)
                ->field("a.feedback_type,a.id,a.name,a.gender,a.tel1,a.group_id,a.add_time,a.upd_time,a.is_dailed,b.comeing,b.outgoing,b.missed,b.videod,c.name as gname")
                ->order("a.id desc")
                ->limit($page->firstrow . ',' . $page->listrows)
                ->select();
        }
        
        $users = M('accounts', null)->query("select id,name from `accounts`  where id!=10000000");
        
        $id_to_name = [];
        if ($data) {
            $ids = array_column($data, 'id');
            $ids = implode(',', $ids);
            $sql = "SELECT a.account_id,b.name,a.contact_id FROM `contacts_to_accounts` a inner JOIN `accounts` b on a.account_id=b.id where a.contact_id in ({$ids}) order BY a.contact_id";
            $r   = M('contacts_to_accounts', null)->query($sql);
            if ($r) {
                foreach ($r as $item) {
                    $id_to_name[$item['contact_id']] = $id_to_name[$item['contact_id']] . ' ' . $item['name'] . ' ';
                }
            }
        }
        foreach ($data as $k => $v) {
            if (!$isAdmin) {
                $data[$k]['tel1_old'] = $v['tel1'];
                $data[$k]['tel1']     = $this->_formatTel(trim($v['tel1']));
            }
            $data[$k]['contactAccounts'] = (string)$id_to_name[$v['id']];
        }
        //获取分组
        $groups   = D("Common/ContactGroup")->getOption();//通讯组
        $wsConfig = M('ws_connect_config', null)->where(['account_id' => $this->account_id])->find();
        $this->accountLogs->addLog("查看联系人列表，查询条件：联系人名称：{$name},联系人电话{$tel1},联系人分组id：{$group_id},联系人性别：{$gender},联系人注册时间最小值：{$add_start_date},联系人注册时间最大值：{$add_end_date},联系人更新最近时间：{$upd_end_date},联系人更新最远时间：{$upd_start_date}");
        $option_is_dailed = ['0' => '全部', '-1' => '未拨号', '1' => '已拨号'];
        $this->assign('name', $name);
        $this->assign('tel1', $tel1);
        $this->assign('gender', $gender);
        $this->assign('group_id', $group_id);
        $this->assign('select_userid', $select_userid);
        $this->assign('add_start_date', $add_start_date);
        $this->assign('add_end_date', $add_end_date);
        $this->assign('upd_start_date', $upd_start_date);
        $this->assign('upd_end_date', $upd_end_date);
        $this->assign('data', $data);
        $this->assign('gdata', json_encode($data));
        $this->assign('Page', $page->show());
        $this->assign('groups', $groups);
        $this->assign('users', $users);
        $this->assign('limit', $limit);
        $this->assign('isAdmin', $isAdmin);
        $this->assign('second_nav_id', 1);
        $this->assign('form_select_userid', $form_select_userid);
        $this->assign('wsConfig', $wsConfig);
        $this->assign('option_is_dailed', $option_is_dailed);
        $this->assign('form_is_dailed', $form_is_dailed);
        $this->assign('feedbackType', $this->feedbackTypeList);
        $this->assign('feedbackTypeForm', $feedbackTypeForm);
        $this->assign('tel',$tel);
        $this->display();
    }
    
    private function _formatTel($tel)
    {
        if (!$tel) {
            return $tel;
        }
        return substr($tel, 0, 3) . "****" . substr($tel, 7);
    }
    
    /**
     * 导出联系人
     */
    public function index_excel()
    {
        if (IS_POST) {
            if (1 == I('excel')) {
                $data = json_decode(htmlspecialchars_decode(I('data')), 1);
                if (!$data) {
                    $this->ajaxReturn(array('status' => 100, 'msg' => '没有数据可导出'));
                }
                $title  = "联系人记录";
                $header = array(
                    array('name', '名称'),
                    array('tel1', '电话'),
                    array('gender', '性别'),
                    array('gname', '分组'),
                    array('comeing', '来电'),
                    array('outgoing', '去电'),
                    array('missed', '未接'),
                    array('add_time', '注册时间'),
                    array('upd_time', '更新时间'),
                );
                foreach ($data as $k => $v) {
                   if (1 == $v['gender']) {
                        $data[$k]['gender'] = iconv('utf-8', 'gbk', '男');
                    } else if (2 == $v['gender']) {
                        $data[$k]['gender'] = iconv('utf-8', 'gbk', '女');
                    } else {
                        $data[$k]['gender'] = iconv('utf-8', 'gbk', '保密');
                    }
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
    
    /**asdfaf
     * 添加、编辑联系人
     * @param id 联系人id
     */
    public function add()
    {
        $id = I('id');
        if ($id) { //编辑
            $data = M("contacts")->find($id);
            if (!$data) {
                $this->error("联系人不存在");
            }
            $this->assign('data', $data);
            $this->assign('title', 2);
        } else { //添加
            $this->assign('title', 1);
        }
        $groups = D("Common/ContactGroup")->getOption();//通讯组
        $this->assign('groups', $groups);
        $this->assign('second_nav_id', 1);
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
    public function save()
    {
        if (IS_AJAX) {
            $data = I('data');
            if (!$data['name'] || !$data['tel1']) {
                echo json_encode(array('code' => 300, 'msg' => '请填写联系人名称分组和联系方式'));
                exit;
            }
            if ($data['id']) { //编辑联系人
                //判断id是否正确
                if (!M('contacts')->find($data['id'])) {
                    echo json_encode(array('code' => 300, 'msg' => '联系人不存在'));
                    exit;
                }
                $data['upd_time'] = date('Y-m-d H:i:s', time());
                $data['keywords'] = D("Common/Contact")->getKeywords($data);
                if (M('contacts')->save($data)) {
                    $this->accountLogs->addLog("编辑联系人：联系人id{$data['id']}");
                    echo json_encode(array('code' => 200, 'msg' => '编辑联系人成功'));
                    exit;
                } else {
                    echo json_encode(array('code' => 300, 'msg' => '编辑联系人失败'));
                    exit;
                }
            } else { //添加联系人
                if (M('contacts')->where("tel1 = '{$data['tel1']}'")->find()) {
                    echo json_encode(array('code' => 300, 'msg' => '联系人已存在'));
                    exit;
                }
                $data['add_time'] = date('Y-m-d H:i:s', time());
                $data['upd_time'] = date('Y-m-d H:i:s', time());
                $data['keywords'] = D("Common/Contact")->getKeywords($data);
                if ($id = M("contacts")->add($data)) {
                    M("contact_stat")->add(array('contact_id' => $id)); //添加联系人统计一行
                    $this->accountLogs->addLog("添加联系人：联系人id{$id}");
                    echo json_encode(array('code' => 200, 'msg' => '添加联系人成功'));
                    exit;
                } else {
                    echo json_encode(array('code' => 300, 'msg' => '添加联系人失败'));
                    exit;
                }
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 删除联系人
     * @param id 联系人id
     */
    public
    function delete()
    {
        if (IS_AJAX) {
            $id = I('id', 'int');
            if (!M("contacts")->find($id)) {
                echo json_encode(array('code' => 300, 'msg' => '联系人不存在'));
                exit;
            }
            M("contact_stat")->where("contact_id = {$id}")->delete();//删除联系人统计数据
            //删除
            if (M("contacts")->delete($id)) {
                M("contacts_to_accounts", null)->where(['contact_id' => $id])->delete();
                $this->accountLogs->addLog("删除联系人：联系人id{$id}");
                echo json_encode(array('code' => 200, 'msg' => '删除联系人成功'));
                exit;
            } else {
                echo json_encode(array('code' => 300, 'msg' => '删除联系人失败'));
                exit;
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
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
    public
    function GroupList()
    {
        $p            = I("p") ? I("p") : 1;
        $limit        = I("limit") ? I("limit") : 20;
        $name         = I("name");
        $add_time_min = I('add_time_min');
        $add_time_max = I('add_time_max');
        $upd_time_min = I('upd_time_min');
        $upd_time_max = I('upd_time_max');
        
        //where条件处理
        $name && $where['name'] = array('like', "%{$name}%");
        if ($add_time_min && $add_time_max) {
            $where['add_time'] = array('between', "{$add_time_min},{$add_time_max}");
        } else {
            $add_time_min && $where['add_time'] = array('gt', $add_time_min);
            $add_time_max && $where['add_time'] = array('lt', $add_time_max);
        }
        if ($upd_time_min && $upd_time_max) {
            $where['upd_time'] = array('between', "{$upd_time_min},{$upd_time_max}");
        } else {
            $upd_time_min && $where['upd_time'] = array('gt', $upd_time_min);
            $upd_time_max && $where['upd_time'] = array('lt', $upd_time_max);
        }
        
        //分页处理
        $count = M("contact_group")->where($where)->count();
        $page  = $this->page($count, $limit, array('p' => $p, "name" => $name, "add_time_min" => $add_time_min, "add_time_max" => $add_time_max, "upd_time_min" => $upd_time_min, "upd_time_max" => $upd_time_max));
        //查询列表
        $data = M("contact_group")->where($where)->order("id desc")->limit($page->firstRow . ',' . $page->listRows)->select();
        //统计查询每个分组的设备数量
        foreach ($data as $k => $v) {
            $data[$k]['num'] = M("contacts")->where("group_id = {$v['id']}")->count();
        }
        $this->accountLogs->addLog("查询通讯组，查询条件：分组名称：{$name},添加最小时间：{$add_time_min},添加最大时间：{$add_time_max},修改最小时间：{$upd_time_min},修改最大时间：{$upd_time_max}");
        $this->assign('name', $name);
        $this->assign('add_time_min', $add_time_min);
        $this->assign('add_time_max', $add_time_max);
        $this->assign('upd_time_min', $upd_time_min);
        $this->assign('upd_time_max', $upd_time_max);
        $this->assign('data', $data);
        $this->assign('gdata', json_encode($data));
        $this->assign('Page', $page->show());
        $this->assign('second_nav_id', 2);
        $this->display();
    }
    
    /**
     * 通讯组导出
     */
    public
    function GroupList_excel()
    {
        if (IS_POST) {
            if (1 == I('excel')) {
                $data = json_decode(htmlspecialchars_decode(I('data')), 1);
                if (!$data) {
                    $this->ajaxReturn(array('status' => 100, 'msg' => '没有数据可导出'));
                }
                $title  = "通讯组";
                $header = array(
                    array('name', '分组名称'),
                    array('num', '联系人数量'),
                    array('add_time', '添加时间'),
                    array('upd_time', '更新时间'),
                );
                foreach ($data as $k => $v) {
                    $data[$k]['num'] = $v['num'] ? $v['num'] : 0;
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
     * 添加、编辑通讯组
     * @param id通讯组id
     */
    public
    function addGroup()
    {
        $id = I('id');
        if ($id) { //编辑
            $group = M("contact_group")->find($id);
            if (!$group) {
                $this->error("通讯组不存在");
            }
            $this->assign('data', $group);
            $this->assign('title', 2);
        } else { //添加
            $this->assign('title', 1);
        }
        $this->assign('second_nav_id', 2);
        $this->display();
    }
    
    /**
     * 保存添加、编辑通讯组
     * @param id 通讯组的id
     * @param name 通讯组名称
     */
    public
    function saveGroup()
    {
        if (IS_AJAX) {
            $data = I('data');
            if ($data['id']) { //编辑
                if (!M('contact_group')->find($data['id'])) {
                    echo json_encode(array('code' => 300, 'msg' => '分组不存在'));
                    exit;
                }
                $data['upd_time'] = date("Y-m-d H:i:s", time());
                if (M("contact_group")->save($data)) {
                    $this->accountLogs->addLog("编辑通讯组，组id：{$data['id']}");
                    echo json_encode(array('code' => 200, 'msg' => '编辑分组成功'));
                    exit;
                } else {
                    echo json_encode(array('code' => 200, 'msg' => '编辑分组失败'));
                    exit;
                }
            } else { //添加
                if (M("contact_group")->where(array("name" => $data['name']))->find()) {
                    echo json_encode(array('code' => 200, 'msg' => '分组已经存在'));
                    exit;
                } else {
                    //添加分组
                    $data['add_time'] = date("Y-m-d H:i:s", time());
                    $data['upd_time'] = date("Y-m-d H:i:s", time());
                    if ($id = M("contact_group")->add($data)) {
                        $this->accountLogs->addLog("添加通讯组，组id：{$id}");
                        echo json_encode(array('code' => 200, 'msg' => '添加分组成功'));
                        exit;
                    } else {
                        echo json_encode(array('code' => 200, 'msg' => '添加分组成功'));
                        exit;
                    }
                }
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 删除通讯组
     * @param id 通讯组的id
     */
    public
    function deleteGroup()
    {
        if (IS_AJAX) {
            $id = I('id', 'int');
            if (!M("contact_group")->find($id)) {
                echo json_encode(array('code' => 300, 'msg' => '分组不存在'));
                exit;
            }
            //进行删除
            //首先将本分组下的联系人分组更换为0
            M("contacts")->where("group_id = {$id}")->save(array("group_id" => 0));
            //删除分组
            if (M("contact_group")->delete($id)) {
                $this->accountLogs->addLog("删除通讯组，组id：{$id}");
                echo json_encode(array('code' => 200, 'msg' => '删除分组成功'));
                exit;
            } else {
                echo json_encode(array('code' => 300, 'msg' => '删除分组失败'));
                exit;
            }
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    /**
     * 批量删除通讯组
     * @param ids id集合
     */
    public
    function deleteAllGroup()
    {
        if (IS_POST) {
            $ids  = I('ids');//通讯组id集合
            $gids = implode($ids, ',');
            if (!$gids) {
                echo json_encode(array('code' => 300, 'msg' => '请选择要删除的分组'));
                exit;
            }
            foreach ($gids as $k => $v) {
                if (!M('contact_group')->find($v)) {
                    continue;
                }
                M("contacts")->where("group_id = {$v}")->save(array("group_id" => 0));
                M("contact_group")->delete($v);
            }
            echo json_encode(array('code' => 200, 'msg' => '删除分组成功'));
            exit;
        } else {
            echo json_encode(array('code' => 300, 'msg' => '请求方式错误'));
            exit;
        }
    }
    
    public
    function import_excel()
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        
        $dst = 'file';
        if (!is_array($_FILES[$dst]) || !is_uploaded_file($_FILES[$dst]['tmp_name'])) {
            echo json_encode(array('code' => 300, 'msg' => '未接收到文件！'));
            exit;
        }
        $filename = uniqid() . '.' . array_pop(explode('.', $_FILES[$dst]['name']));
        
        $full_dirs = SPSTATIC . DIRECTORY_SEPARATOR . 'CSV' . DIRECTORY_SEPARATOR;
        if (!is_dir($full_dirs)) {
            mkdir($full_dirs, 0777, true);
        }
        $upload_file = $full_dirs . $filename;
        @move_uploaded_file($_FILES[$dst]['tmp_name'], $upload_file);
        
        //        $upload_file = 'D:\phpStudy\PHPTutorial\WWW\deviceV3\public\CSV\33.xlsx';
        
        require_once VENDOR_PATH . 'PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
        
        $file_name = $upload_file;
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));//判断导入表格后缀格式
        if ($extension == 'xlsx') {
            $objReader   = \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');
        } else if ($extension == 'xls') {
            $objReader   = \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name, $encode = 'utf-8');
        }
        $sheet         = $objPHPExcel->getSheet(0);
        $highestRow    = $sheet->getHighestRow();//取得总行数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        //        $minGroupId = M('')->field('min(id)')->getField('min(id)');
        $r    = [];
        $time = date('Y-m-d H:i:s');
        for ($i = 2; $i <= $highestRow; $i++) {
            //            $data = [];
            //看这里看这里,前面小写的a是表中的字段名，后面的大写A是excel中位置
            $data['name']     = $objPHPExcel->getActiveSheet()->getCell("A" . $i)->getValue();
            $data['tel1']     = trim($objPHPExcel->getActiveSheet()->getCell("B" . $i)->getValue());
            $data['gender']   = $objPHPExcel->getActiveSheet()->getCell("C" . $i)->getValue();
            $data['gender']   = $data['gender'] == '男' ? 1 : ($data['gender'] == '女' ? 2 : 0);
            $data['add_time'] = $time;
            $r[]              = $data;
        }
        $existsTels = M('contacts', null)->field('tel1')->where(['tel1' => ['in', array_column($r, 'tel1')]])->select();
        if ($existsTels) {
            $existsTels = array_column($existsTels, 'tel1');
            echo json_encode(array('code' => 300, 'msg' => '导入失败！' . implode(',', $existsTels) . '这些电话已存在！'));
            exit;
        }
        //        $repeatTelMsg = $repeatTelMsg ? rtrim($repeatTelMsg,',').'这些电话已存在！未导入这些数据';
        M('contacts', null)->addAll($r);
        unlink($upload_file);
        echo json_encode(array('code' => 200, 'msg' => '添加联系人成功'));
        exit;
    }
    
    public function telToAccount()
    {
        if ($this->account_id != 10000000) {
            echo json_encode(array('code' => 300, 'msg' => '分配联系需要Admin账号!'));
        }
        $param     = I('');
        $accountId = intval($param['account_id']);
        $ids       = $param['ids'];
        if (!$accountId || !$ids) {
            echo json_encode(array('code' => 300, 'msg' => '请选择管理员账号和并勾选联系人！'));
            die;
        }
        $ids  = explode(',', trim($ids, ','));
        $data = [];
        $time = time();
        foreach ($ids as $v) {
            $r               = [];
            $r['contact_id'] = (int)$v;
            $r['account_id'] = $accountId;
            $r['add_time'] = $time;
            
            if ($param['del']) {
                M('contacts_to_accounts', null)->execute("delete from `contacts_to_accounts` where contact_id='{$r['contact_id']}' and account_id='{$r['account_id']}'");
            }
            if(empty($param['del']) && !M('contacts_to_accounts', null)->where($r)->find() ){
                $data[] = $r;
            }
        }
        if (empty($param['del'])) {
            M('contacts_to_accounts', null)->addAll($data);
        }
        echo json_encode(array('code' => 200, 'msg' => '操作成功'));
    }
    
    public function wsConnectBefore()
    {
        $param = I('');
        $id    = (int)$param['id'];
        if (!$param['ws_address'] || !$param['ws_line'] || !$param['district_code']) {
            echo json_encode(array('code' => 300, 'msg' => '拨号信息需要填写完整！'));
            die;
        }
        $dialLocal = (int)$_GET['dialLocal'];
        //拨打指定号码方式拨号
        if($id==-1 && $param['tel']){
            $telInfo = $this->_telInfo($param['tel'], trim($param['district_code']), trim($param['switch_num']), $dialLocal);
            echo json_encode(array('code' => 200, 'data' => $telInfo, 'msg' => '请求成功'));
            die;
        }
        if ($this->account_id != 10000000) {
            if (!M('contacts_to_accounts', null)->query("select id from contacts_to_accounts where account_id={$this->account_id} and contact_id={$id}")) {
                echo json_encode(array('code' => 300, 'msg' => '联系人未分配给当前用户'));
                die;
            }
        }
       
        $r = M('contacts', null)->field('tel1')->find($id);
        if ($r) {
            $data = [
                'account_id'    => $this->account_id,
                'ws_address'    => trim($param['ws_address']),
                'ws_line'       => trim($param['ws_line']),
//                'switch_num'    => intval($param['switch_num']),
                'district_id'   => intval($param['district_id']),
                'district_code' => trim($param['district_code']),
            ];
            if(is_numeric($param['switch_num'])){
                $data['switch_num'] = intval($param['switch_num']);
            }
            if (!M('ws_connect_config', null)->where($data)->find()) {
                M('ws_connect_config', null)->add($data, [], true);
            }
            //            M('contacts', null)->where(['id'=>$id])->save(['is_dailed'=>1]);
            //            if($this->account_id!==10000000){
            //                M('contacts_to_accounts', null)->where(['contact_id'=>$id,'account_id'=>$this->account_id])->save(['is_dailed'=>1]);
            //            }
            $telInfo = $this->_telInfo($r['tel1'], trim($param['district_code']), trim($param['switch_num']), $dialLocal);
            echo json_encode(array('code' => 200, 'data' => $telInfo, 'msg' => '请求成功'));
            die;
        } else {
            echo json_encode(array('code' => 300, 'msg' => '联系人已被删除'));
            die;
        }
    }
    
    public function dailed()
    {
        $param = I('');
        $id    = (int)$param['id'];
        M('contacts', null)->where(['id' => $id])->save(['is_dailed' => 1]);
        $time = time();
        $timeArr = [
            'call_time' => $time,
            'call_time_h' => strtotime(date('Y-m-d',$time)) + 3600*date('H',$time),
            'call_time_d' => strtotime(date('Y-m-d',$time)),
            'call_time_w' => strtotime(date('Y-m-d',($time-((date('w',$time)==0?7:date('w',$time))-1)*24*3600))),
            'call_time_m' => strtotime(date('Y-m',$time)),
            'call_time_y' => strtotime(date('Y',$time).'-1-1'),
        ];
        if ($this->account_id != 10000000) {
            M('contacts_to_accounts', null)->where(['contact_id' => $id, 'account_id' => $this->account_id])->save(array_merge(['is_dailed' => 1],$timeArr));
        } else {
            M('contacts_to_accounts', null)->add(array_merge(['contact_id' => $id, 'account_id' => $this->account_id,'is_dailed' => 1],$timeArr), [], true);
        }
        echo json_encode(array('code' => 200, 'data' => [], 'msg' => '请求成功'));
        die;
    }
    
    /**
     * 按键号码+0+手机号，如果是本地号码不用加 0
     * @param $tel
     * @param $direct_code
     * @param $switch_num
     * @return array
     */
    private function _telInfo($tel, $direct_code, $switch_num, $dialLocal = 0)
    {
        $tel1 = $tel;
        $tel = trim($tel1);
        if ($dialLocal) {
            $tel = $switch_num . $tel;
            return [
                'tel'       => $tel,
                'phone_msg' => "拨打手机号码：<span style='color: #00a0f0'>$tel</span>",
                //            'province'  => (string)$r['province'], 'city' => (string)$r['city'], 'direct_code' => (string)$r['city_code'], 'is_local' => $is_local
            ];
        }
        $r = M('phone', null)->where(['phone' => substr($tel1, 0, 7)])->find();
        if (!$r) {
//            $tel = $switch_num  . '0' . $tel;
            $tel = $switch_num  . $tel;
            return [
                'tel'       => $tel,
//                'phone_msg' => "<span style='color: orangered'>未查到区号信息，外地拨号方式， 拨打手机号码：$tel,如未拨通请选择 “本地拨号”</span>",
                'phone_msg' => "<span style='color: orangered'>未查到区号信息，使用本地拨号方式， 拨打手机号码：$tel</span>",

                //            'province'  => (string)$r['province'], 'city' => (string)$r['city'], 'direct_code' => (string)$r['city_code'], 'is_local' => $is_local
            ];
        }
        
        $is_local = '外地号码';
        if ($direct_code == $r['city_code']) {
            $tel      = $switch_num . '' . $tel;
            $is_local = '本地号码';
        } else {
            $tel = $switch_num . '0' . $tel;
        }
        //        $tel = 13552037965;
        return [
            'tel'       => $tel,
            'phone_msg' => "拨打手机号码：<span style='color: #00a0f0'>$tel</span>; 省份：<span style='color: #00a0f0'>{$r['province']}</span>; 城市：<span style='color: #00a0f0'>{$r['city']}</span>; 区号：<span style='color: #00a0f0'>{$r['city_code']}</span>; <span style='color: #00a0f0'>{$is_local}</span>",
            //            'province'  => (string)$r['province'], 'city' => (string)$r['city'], 'direct_code' => (string)$r['city_code'], 'is_local' => $is_local
        ];
    }
    
    public function changeFeedBack()
    {
        $params       = I('');
        $id           = intval($params['id']);
        $feedbackType = intval($params['feedback_type']);
        if (!$feedbackType || !in_array($feedbackType, array_keys($this->feedbackTypeList))) {
            echo json_encode(array('code' => 300, 'msg' => '意向类型不正确请重新选择'));
            die;
        }
        $r = M('contacts', null)->find($id);
        if($r['is_dailed']==-1){
            echo json_encode(array('code' => 200, 'msg' => '还未拨号！'));
            die;
        }
        M('contacts', null)->where(['id' => $id])->save(['feedback_type' => $feedbackType]);
        M('contacts_to_accounts', null)->where(['contact_id' => $id,'account_id'=>$this->account_id])->save(['feedback_type' => $feedbackType]);
        echo json_encode(array('code' => 200, 'msg' => '操作成功'));
        die;
    }
    
    public function statistics()
    {
        
        $this->display();
    }
    public function doStatistics(){
        $accounts = M('',null)->query('select * from accounts');
        $accounts = array_column($accounts,'name','id');
        $sql = "SELECT count(1) as num,account_id,ct.feedback_type FROM `contacts_to_accounts` as ct
              inner JOIN contacts as  c on c.id=ct.contact_id
              GROUP BY ct.account_id,ct.feedback_type";
        $r = M('',null)->query($sql);
        
        $numToAccountId = M('',null)->query("select count(1) as total,account_id from contacts_to_accounts GROUP by account_id");
        $numToAccountId = array_column($numToAccountId,'total','account_id');
    
        $undailToAccountId = M('',null)->query("select count(1) as total,account_id from contacts_to_accounts where is_dailed='-1' GROUP by account_id");
        $undailToAccountId = array_column($undailToAccountId,'total','account_id');
//        dump($r);die;
        $data = [];
        
        foreach ($accounts as $accountId=>$name){
            $data[$accountId]['name'] = $accounts[$accountId];
            $data[$accountId]['total'] = (int)$numToAccountId[$accountId];
            $data[$accountId]['total_dailed'] = $data[$accountId]['total'] - (int)$undailToAccountId[$accountId];
            $data[$accountId]['total_undailed'] = (int)$undailToAccountId[$accountId];
            foreach ($this->feedbackTypeList as $type=>$text){
                if($type){
                    $data[$accountId]['feedback_type'][$type] = 0;
                }
            }
            $data[$accountId]['dailednum_in_time']=array_sum($data[$accountId]['feedback_type']);
        }
        foreach ($r as $v){
            foreach ($this->feedbackTypeList as $type=>$text){
                if($type&&$v['feedback_type']==$type){
                    $data[$v['account_id']]['feedback_type'][$type]=$v['num'];
                }
            }
            $data[$v['account_id']]['dailednum_in_time']=array_sum($data[$v['account_id']]['feedback_type']);
        }
//                dump($data);die;
        $this->assign('feedbackTypeList',$this->feedbackTypeList);
        $this->assign('data',$data);
        $this->display();

    }
    public function doStatistics1(){
        $accounts = M('',null)->query('select * from accounts');
        $accounts = array_column($accounts,'name','id');
        $sql = "SELECT count(1) as num,account_id,call_time_h,ct.feedback_type FROM `contacts_to_accounts`  as  ct
                inner JOIN contacts as  c on c.id=ct.contact_id
                left join phone as p on left(c.tel1,7)=p.phone
                where ct.is_dailed=1
                group by ct.account_id,ct.call_time_h,ct.feedback_type";
        $r = M('',null)->query($sql);
        $numToAccountId = M('',null)->query("select count(1) as total,account_id from contacts_to_accounts GROUP by account_id");
        $numToAccountId = array_column($numToAccountId,'total','account_id');
        
        $undailToAccountId = M('',null)->query("select count(1) as total,account_id from contacts_to_accounts where is_dailed=-1 GROUP by account_id");
        $undailToAccountId = array_column($undailToAccountId,'total','account_id');

        //        dump($numToAccountId);die;
        $data = [];
        
        foreach ($r as $k=>$v){
            $data[$v['account_id']][]=$v;
            $data[$v['account_id']][0]['dailed_total'] +=$v['num'];
        }
        foreach ($accounts as $accountId=>$name){
            $numToAccountId[$accountId] = (int)$numToAccountId[$accountId];
            $undailToAccountId[$accountId] = (int)$undailToAccountId[$accountId];
            $dailedNum = $numToAccountId[$accountId]-$undailToAccountId[$accountId];
            $data[$accountId][0]['profile']="{$name} 总分配:{$numToAccountId[$accountId]},已拨号:{$dailedNum},未拨号:{$undailToAccountId[$accountId]}";
        }
//        foreach ($numToAccountId as $accountId=>$total){
//            $data[$accountId][0]['total']=$total;
//            $dailedNum = $total-$undailToAccountId[$accountId];
//            $data[$accountId][0]['profile']="{$accounts[$accountId]} 总分配:{$total},已拨号:{$dailedNum},未拨号:{$undailToAccountId[$accountId]}";
//        }
//        foreach ($data as $accountId=>$v) {
//            $data[$accountId][0]['dailed_total'] = count((array)array_column($v,'num'));
//        }
        
        dump($data);die;
//        dump($r);die;
        $sql = "SELECT count(1),account_id,province,city FROM `contacts_to_accounts`  as  ct
                inner JOIN contacts as  c on c.id=ct.contact_id
                left join phone as p on left(c.tel1,7)=p.phone
                group by ct.account_id,p.city";
        $sql = "SELECT count(1),account_id FROM `contacts_to_accounts` group by account_id";
        $sql = "SELECT count(1),account_id FROM `contacts_to_accounts` where is_dailed=1 group by account_id";
        
    }
	
	  /**  极光推送

     * @param $addtag 拨打手机
     * @param $phone  号码列表
     * @param $type   推送类型
     * @return array
     */
    public function addPush(){


        $params = I('');



            if ($params['ids'] ) {
                $ids = $params['ids'];
                $ids = rtrim($ids,",");
                $params['phone']= explode(",",$ids);

            }

            if (!$params['phone']  ) {
                echo json_encode(array('code' => 300, 'msg' => '请输入电话号码'));
                die;
            }




        $addtag  =  I("session.ACCOUNTS")['tel'];

        $phone = is_array($params['phone'])?$params['phone']:array($params['phone']);

        $id='1';
  //        $addtag='18310479300';
//             $addtag='16218800284';  //测试机2
        $type='call';





//        $phone=array('10086');
        //new极光推送类，C函数是thinkphp取配置文件里的方法，这里取得是刚刚准备好的key&secret
        require VENDOR_PATH.'JPush/autoload.php';

        $client = new \JPush\Client(C('jpush_key'), C('jpush_secret'));


        $response = $client->push()
            //设置发送的平台
            ->setPlatform(array('ios', 'android'))
            //别名，用于推送到指定用户
            ->addalias($addtag)
            //设置android平台推送
            ->message('1', array(
                'title' => 'vaa推送',//拨打电话
                "content_type"=>"1",//拨打类型   1.拨打  2.挂断
                'extras' => array(
                    'id' => $id,
                    'phone' => $phone,//手机号 数组
                    'type' => $type,//推送类型   call  拨打   stopcall  挂断

                ),
            ))

            ->options(array(
                'apns_production' => 0,   //设置推送环境是开发环境 or 运营模式（上线模式）
                'time_to_live' => 0,
            ))

            //执行推送
            ->send();

      //  $this->error("电话拨打成功");
        echo json_encode(array('code' => 200, 'msg' => '电话拨打成功'));
        die;



    }

    public function addPushf(){
        try {
        $params = I('');
        if (!$params['phone'] ||  !preg_match("/^[1-9]\d*$/", $params['phone'])  ) {
            echo json_encode(array('code' => 3100, 'msg' => '请输入正确电话号码'));
            die;
        }
        $addtag  = $params['tel'];

        $phone = $params['phone']?array($params['phone']):array('10086');


        $id='1';
//        $addtag='18310479300';
//             $addtag='16218800284';  //测试机2
        $type='call';
//        $phone=array('10086');
        //new极光推送类，C函数是thinkphp取配置文件里的方法，这里取得是刚刚准备好的key&secret
        require VENDOR_PATH.'JPush/autoload.php';

        $client = new \JPush\Client(C('jpush_key'), C('jpush_secret'));


        $response = $client->push()
            //设置发送的平台
            ->setPlatform(array('ios', 'android'))
            //别名，用于推送到指定用户
            ->addalias($addtag)
            //设置android平台推送
            ->message('1', array(
                'title' => 'vaa推送',//拨打电话
                "content_type"=>"1",
                'extras' => array(
                    'id' => $id,
                    'phone' => $phone,//手机号 数组
                    'type' => $type,//推送类型   call  拨打   stopcaall  挂断

                ),
            ))

            ->options(array(
                'apns_production' => 0,   //设置推送环境是开发环境 or 运营模式（上线模式）
                'time_to_live' => 0,
            ))

            //执行推送
            ->send();

        echo json_encode(array('code' => 200, 'msg' => '拨打成功！'));
        die;

        }catch (\Exception $e){

            if("Invalid alias value"==$e->getMessage()){
                echo json_encode(array('code' => 203, 'msg' => '当前登录账户没有手机号！'));
            }else if("cannot find user by this audience"==$e->getMessage()){
                echo json_encode(array('code' => 202, 'msg' => '推送手机号异常！'));
            }

    }

    }




    /**
     * 极光推送
     * @param tel 拨打手机
     *  @param phone 拨打手机
     *  @param id 推送 id   1拨打    2挂断
     */
    public function jPushs($param){
        try {

            $addtag  = $param['tel'];
            $params['phone'] = $param['phone']?array($param['phone']):array('10086');
           /* if (!$params['phone'] ||  !preg_match("/^[1-9]\d*$/", $params['phone'])  ) {
                echo json_encode(array('code' => 300, 'msg' => '请输入正确电话号码'));
                die;
            }*/

            //new极光推送类，C函数是thinkphp取配置文件里的方法，这里取得是刚刚准备好的key&secret
            require VENDOR_PATH.'JPush/autoload.php';

            $client = new \JPush\Client(C('jpush_key'), C('jpush_secret'));


            $response = $client->push()
                //设置发送的平台
                ->setPlatform(array('ios', 'android'))
                //别名，用于推送到指定用户
                ->addalias($addtag)
                //设置android平台推送
                ->message('1', array(
                    'title' => 'vaa推送',//拨打电话
                    "content_type"=>"1",
                    'extras' => array(
                        'id' => $param['id'],
                        'phone' => $param['phone'],//手机号 数组
                        'type' =>$param['type'] ,//推送类型   call  拨打   stopcaall  挂断

                    ),
                ))

                ->options(array(
                    'apns_production' => 0,   //设置推送环境是开发环境 or 运营模式（上线模式）
                    'time_to_live' => 0,
                ))

                //执行推送
                ->send();

            echo json_encode(array('code' => 200, 'msg' => '操作成功！'));
            die;

        }catch (\Exception $e){

            if("Invalid alias value"==$e->getMessage()){
                echo json_encode(array('code' => 203, 'msg' => '当前登录账户没有手机号！'));
            }else if("cannot find user by this audience"==$e->getMessage()){
                echo json_encode(array('code' => 202, 'msg' => '推送手机号异常！'));

            }else{
                echo json_encode(array('code' => 202, 'msg' => '拨号失败：'.$e->getMessage()));
            }
            die;

        }

    }

    public function Pushs(){

        $param = I('');
        $param['id']=2;



        $this->jPushs($param);
    }
	
	
}