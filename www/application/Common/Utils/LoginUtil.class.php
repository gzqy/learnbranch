<?php
namespace Common\Utils;
/**
 * 中信系统 账号登录系统验证接口
 */
class LoginUtil {
	private $AppId="RECORD-XIANFENG@CITICS.COM";//应用标示
	private $AppKey="puXpzlrh!rw2z&5v";//应用接入安全码
	private $authHeader;//应用请求头
	private $host="https://passport.citicsinfo.com/passport/";//应用请求地址
	private $abstrunct="accessApplication";//应用请求接口
	
	/**
	 * 生成请求头
	 */
	private function createHeader(){
		if(empty($this->authHeader)){
			$authHeader = 'Basic '.base64_encode($this->AppId.$this->AppKey);
			$this->authHeader = $authHeader;
		}
		return $this->authHeader;
	}

	/**
	 * 生成请求体
	 */
	private function checkParams($sessionData){
		if(!$sessionData)
			return false;
		if(empty($sessionData))
			return false;
		return true;
	}

	public function Login($sessionData){
		if(!$this->checkParams($sessionData)){
			return -1;//参数验证失败
		}
		$info = $this->curl_post($sessionData);
		//预处理
		$data = [];
		if(200 == $info['code']){//登录成功
			$data['code'] = 1;
			$data['uid'] = $info['uid'];
			$data['name'] = $info['name'];
		}else{ //登录失败
			$data['code'] = 0;
		}	
		return $data;
	}

	private function curl_post($sessionData){
		$authHeader = $this->createHeader();
        $header_data = [
            'Authorization:'.$authHeader,
            'Content-Type:application/x-www-form-urlencoded; charset=utf-8'
        ];
        $body_data = "sessionData={$sessionData}";
        $url = $this->host.$this->abstrunct;   
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