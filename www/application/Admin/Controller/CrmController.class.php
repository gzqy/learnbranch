<?php

namespace Admin\Controller;

use Common\Controller\ShopbaseController;
use Common\Utils\LoginUtil;

/*
 * crm相关控制
 */

class CrmController extends ShopbaseController
{
    
    function _initialize()
    {
        parent::_initialize();
        $second_nav = array(
            array('id' => 1, 'a' => U('/Concat/index'), 'name' => '联系人'),
            array('id' => 2, 'a' => U('/Concat/GroupList'), 'name' => '通讯组'),
        );
        $this->assign('nav_id', $this->nav_id);
        $this->assign('second_nav', $second_nav);
    }
    
    public function ConcatInfo()
    {
        $params       = I("");
        $id           = (int)$params['id'];
        $deviceCallId = $params["device_call_id"];
        
        if (!$id && $deviceCallId) {
            $tel1            = M('device_call', null)->field('tel')->where(['id' => $deviceCallId])->getField('tel');
            $tel            = ltrim($tel1, '0');
            $sql            = "select * from contacts where tel1='{$tel}' or tel2='{$tel}' or tel3='{$tel}' limit 1";
            $idArr          = M('contacts', null)->query($sql);
            $telInfo['id']  = 0;
            $telInfo['tel'] = $tel;
            if ($idArr) {
                $id = $idArr[0]['id'];
            }
        }
        $info                    = M('contacts', null)->find($id);
        if(!$info && $tel1){
            $info = (array)M('contacts',null)->where(['id'=>['gt',0]])->find();
            $info = array_map(function($v){
                return '';
            },$info);
            $info['feedback_type']=1;
            $info['group_id']=0;
            $info['tel1'] = $tel1;
        }
        $info['remind_time'] = (string)M('contact_remind')->where(['account_id'=>$this->account_id,'contact_id'=>$id])->
                            getField('remind_time');
        $info1                   = M('contacts_to_accounts', null)->join('inner join accounts on contacts_to_accounts.account_id=accounts.id')->
        where(['contact_id' => $id])->order('contacts_to_accounts.id desc')->limit(1)->find();
        $info['gender']          = $info['gender'] == 1 ? '男' : ($info['gender'] == 2 ? '女' : '');
        $info['last_admin']      = (string)$info1['name'];
        $info['last_order_time'] = $info1['add_time'] ? date('Y-m-d H:i:s', $info1['add_time']) : '';
        if ($info['note'] && !$info['note_list']) {
            $info['note_list'] = $info['note'];
        }
        $info['note']     = '';
        $where['_string'] = '';
        $telArr           = ['tel1', 'tel2', 'tel3'];
        foreach ($telArr as $value) {
            $tel = $info[$value];
            if ($tel) {
                $where['_string'] .= ' (tel like "%' . $tel . '%") OR ';
            }
        }
        $where['_string'] = trim($where['_string'], 'OR ');
        
        //        $where['_string'] = ' (name like "%thinkphp%")  OR ( title like "%thinkphp") ';
        $logs = [];
        if ($where['_string']) {
            $logs     = (array)M('device_call', null)->where($where)->order('id desc')->select();


            if($this->account_id == 10000000){
                $app_logs = (array)M('device_app_call', null)
                    ->join('inner join devices_app on device_app_call.device_id=devices_app.id')
                    ->field("device_app_call.*,1 as app,devices_app.code as line_id,devices_app.name as portname")->
                    where(['tel' => $info['tel1']])->order('device_app_call.id desc')->select();
            }else{
                $app_logs = (array)M('device_app_call', null)
                    ->join('inner join devices_app on device_app_call.device_id=devices_app.id')
                    ->field("device_app_call.*,1 as app,devices_app.code as line_id,devices_app.name as portname")->
                    where(['tel' => $info['tel1'] ,'code' => $_SESSION['ACCOUNTS']['tel']])->order('device_app_call.id desc')->select();
            }
//            ,'line_id' => $_SESSION['ACCOUNTS']['name']
            $logs     = array_merge($logs, $app_logs);

//            $count = M("contacts")->alias('a')
//                ->join("inner join accounts as ct on ct.tel=a.app_code");
        }
        
        $str = "";
        
        foreach ($logs as $k => $val) {
            $img                   = $this->deviceCall->icon($val['type']);
            $type                  = $this->deviceCall->type($val['type']);
            $logs[$k]['type']      = "<img src='./public/pc/" . $img['icon'] . "' height='20' align='absmiddle' />" . $type;
            $logs[$k]['call_time'] = $this->deviceCall->getTime($val['call_time']);
            
            $logs[$k]['call_date'] = $val['app'] ? date('Y-m-d H:i:s', $val['call_date']) : $val['call_date'];
            if ($val['app']) {
                $logs[$k]['portname'] = $val['line_id'] . '<br>' . $val['portname'];
            } else {
                $logs[$k]['portname'] = sprintf("%04d", $val['line_id']) . '<br>' . $val['portname'];
            }
            //            $logs[$k]['recording_time'] =$this->deviceCall->getTime($val['recording_time']);
            $logs[$k]['filesInfo'] = '文件尚未上传';
            if ($val['files']) {
                if ($val['app']) {
                    $files = D("Common/DeviceCall")->getAppFileDir($val['files']) . D("Common/DeviceCall")->replaceForFilename($val['files']);
                } else {
                    $files = D("Common/DeviceCall")->getFileDir($val['files']) . D("Common/DeviceCall")->replaceForFilename($val['files']);
                }
                if (file_exists($files)) {
                    if ($val['app']) {
                        $logs[$k]['filesInfo'] = '<a  href="index.php?s=/DeviceApp/PlayLog/id/' . $val['id'] . '" target="_blank" title="播放录音文件" >播放</a><i>|</i>
                        <a href="index.php?s=/DeviceApp/DownloadLog/id/' . $val['id'] . '" target="_blank"  title="下载录音文件">下载</a>';
                    } else {
                        $logs[$k]['filesInfo'] = '<a  href="index.php?s=/Device/PlayLog/id/' . $val['id'] . '" target="_blank" title="播放录音文件" >播放</a><i>|</i>
                        <a href="index.php?s=/Device/DownloadLog/id/' . $val['id'] . '" target="_blank"  title="下载录音文件">下载</a>';
                    }
                }
            }
            $str .= '<tr>
                        <td style="width:70px;">' . ($k + 1) . '</td>
                        <td style="width:70px;">' . $logs[$k]['portname'] . '</td>
                        
                        <td style="width: 100px;"> ' . $logs[$k]['type'] . '</td>
                        <td style="width: 120px;">' . $logs[$k]['call_date'] . '</td>
                        <!--<td style="width: 80px;">{$val[\'recording_time\']}</td>-->
                        <td style="width: 80px;">' . $logs[$k]['call_time'] . '</td>
                        <td style="width: 100px;">' . $val['tel'] . '</td>
                        <td style="width: 200px;">' . $logs[$k]['filesInfo'] . '</td>
                    </tr>';
        }
        //        $str = $str.$str.$str.$str.$str.$str.$str.$str.$str.$str;
        $info['call_logs'] = $str;
        echo json_encode(array('code' => 200, 'data' => $info, 'msg' => '操作成功'));
    }
    
    public function concatSave()
    {
        $params = I("");
        $data   = [];
        foreach ($params as $k => $v) {
            $k        = str_replace('crm_', '', $k);
            $data[$k] = trim($v);
        }
        $data['gender']    = $data['gender'] == '男' ? 1 : ($data['gender'] == '女' ? 2 : 0);
        $data['note_list'] = $data['note'] ?   $data['note_list'] ."\n". date("Y-m-d H:i:s") . " " . $this->account['name'] . "\n" . $data['note'] : $data['note_list'];
        if ($data['id'] == -1|| !$data['id']) {
            if (!$data['name']) {
                echo json_encode(array('code' => 300, 'data' => [], 'msg' => '添加失败，缺少必要的参数,如：姓名'));
                die;
            }
            $sql = "select id from contacts where tel1='{$data['tel1']}' or tel2='{$data['tel1']}' or tel3='{$data['tel1']}' limit 1";
            
            if ($idArr = M('contacts', null)->query($sql)) {
                echo json_encode(array('code' => 300, 'data' => [], 'msg' => '添加失败，此号码' . $data['tel1'] . '已存在！'));
                die;
            }
            unset($data['id']);
            $r = M('contacts', null)->add($data);
            $r1['contact_id'] = (int)$r;
            $r1['account_id'] = $this->account_id;
            $r1['add_time'] = time();
            M('contacts_to_accounts', null)->add($r1);
            $data['id']=$r;
        } else {
            $r = M('contacts', null)->save($data);
        }
        
        if($data['remind_time']){
            $remindWhere=['account_id'=>$this->account_id,'contact_id'=>$data['id']];
            $remindInfo = M('contact_remind',null)->where($remindWhere)->find();
            $saveData=['add_time'=>date('Y-m-d H:i:s'),
                       'remind_time'=>$data['remind_time'],
                       'site_url'=>$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT']. $_SERVER['PHP_SELF'],
                       'is_send'=>0];
            if($remindInfo['remind_time']!=$data['remind_time']){
                if($remindInfo){
                    M('contact_remind')->where(['id'=>$remindInfo['id']])->save($saveData);
                }else{
                    M('contact_remind',null)->add(array_merge($remindWhere,$saveData));
                }
            }
        }
        
        if ($r !== false) {
            echo json_encode(array('code' => 200, 'data' => ['r' => $r], 'msg' => '操作成功'));
        } else {
            echo json_encode(array('code' => 300, 'data' => ['r' => $r], 'msg' => '操作失败'));
        }
    }
    
    /**
     * 使用ajax轮询方式实现
     * 来电后弹出电话相关信息
     */
    public function telLayer()
    {
        $info = M('ws_connect_config', null)->where(['account_id' => $this->account_id])->find();
//                echo json_encode(array('code' => 200, 'data' => ['tel'=>13552037965,'id'=>10000018], 'msg' => '没有数据'));die;
        if (!$info) {
            echo json_encode(array('code' => 300, 'data' => [], 'msg' => '没有数据'));
            die;
        }
        $deviceId = M('device_stat', null)->field('device_id')->where(['IP' => $info['ws_address']])->getField('device_id');
        if (!$deviceId) {
            echo json_encode(array('code' => 300, 'data' => [], 'msg' => '没有数据'));
            die;
        }
//        $_GET['test']=1;
        $time               = $_GET['test'] ? time() - 3600 * 24 * 30 : time() - 10;
        $con                = ['create_time' => ['egt', $time], 'device_id' => $deviceId, 'line_id' => $info['ws_line'],'popuped'=>0];
        $data               = (array)M('device_call_alert')->where($con)->order('create_time asc')->limit(1)->find();
        $res                = ['data' => [], 'code' => 300, 'msg' => '有最新来电'];
        $res['data']['tel'] = $data['tel'];
        $res['data']['id']  = 0;
        $res['code']      = $data ? 200 : 300;
        if ($data['tel']) {
            M('device_call_alert')->where(['id'=>$data['id']])->save(['popuped'=>1]);
            $param['tel']      = $data['tel'];
            $sql               = "select id from contacts where tel1='{$param['tel']}' or tel2='{$param['tel']}' or tel3='{$param['tel']}' limit 1";
            $idArr             = M('contacts', null)->query($sql);
            $res['data']['id'] = (int)$idArr[0]['id'];
        }
        echo json_encode($res);
    }
    
}
