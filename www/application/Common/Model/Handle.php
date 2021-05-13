<?php
namespace Models;
class Handle {
    /**
     * 数据库操作,, 如果有id,修改, 没有id, 新增
     * 需要包含 add_time  upd_time 两个datetime字段
     * @param type $_model
     * @param type $_data
     * @return boolean
     */
    public function replaceModel($_model,$_data){
        if ( empty($_model) ) {
            return false;
        }
        $date_time = date("Y-m-d H:i:s");
        if ( empty($_data['id']) ) {
            $_data['add_time'] = $date_time;
            $id = $_model->insertGetId($_data);
        } else {
            $id = $_data['id'];
            unset($_data['id']);
            $_data['upd_time'] = $date_time;
            $_model->where('id',$id)->update($_data);
        }
        return $id;
    }

    /**
     * 科大讯飞 测试语音转文字
     * @param $file
     */
    function curl_truncate($file){
        $appid = '5aebccd1';
        $appkey = 'f5880b16d22fd0475c376d0619c9e1b5';
        $param = ['engine_type' => 'sms8k','aue' => 'raw'];
        $time = (string)time();
        $x_param = base64_encode(json_encode($param));
        $header_data = [
            'X-Appid:'.$appid,
            'X-CurTime:'.$time,
            'X-Param:'.$x_param, 
            'X-CheckSum:'.md5($appkey.$time.$x_param),        
            'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
        ];    //Body
        $file_content = file_get_contents($file);
        $body_data = 'audio='.urlencode(base64_encode($file_content));    //Request
        $url = "http://api.xfyun.cn/v1/service/v1/iat";   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_data);    
        $result = curl_exec($ch);
        curl_close($ch);    
        return json_decode($result,1);
    }
}