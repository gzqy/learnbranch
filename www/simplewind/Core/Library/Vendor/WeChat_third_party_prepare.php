<?php
/**
 * 微信连接准备类，提供基础字段获取等功能
 * @author Administrator
 *
 */
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';
	/**
	 * 每十分钟让服务器调用获得对应信息
	 * 获得AppId、CreateTime、InfoType、ComponentVerifyTicket
	 * 若返回负数则为错误，请查官网获得结果
	 */
	 function getComponent_verify_ticket($token,$encodingAesKey,$appId){
		$encrypt_type = $_REQUEST["encrypt_type"];
		// 微信加密签名
		$msgSignature = $_REQUEST["msg_signature"];
		// 时间戳
		$timeStamp = $_REQUEST["timestamp"];
		// 随机数
		$nonce = $_REQUEST["nonce"];
		//密文
		$text = file_get_contents("php://input");
		//实例化解密类
		$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
		$msg = "";
		$errCode = $pc->decryptMsg($msgSignature, $timeStamp, $nonce, $text, $msg);
		if($errCode == 0){
			$content['AppId'] = XMLUtil::getItemValue($msg,"AppId");
			$content['CreateTime'] = XMLUtil::getItemValue($msg,"CreateTime");
			$content['InfoType'] = XMLUtil::getItemValue($msg,"InfoType");
			$content['ComponentVerifyTicket'] = XMLUtil::getItemValue($msg,"ComponentVerifyTicket");
		}else{
			echo 'success';
			return $errCode;
		}
		
		echo 'success';//通知微信接收成功
		return $content;
	}
	
	/**
	 * 第三方平台获取access_token,返回数组
	 * 获得component_access_token、expires_in（有效期）
	 * @param unknown $component_appid
	 * @param unknown $component_appsecret
	 * @param unknown $component_verify_ticket
	 * @return Ambigous <mixed, boolean>
	 */
	 function getAccess_token($component_appid,$component_appsecret,$component_verify_ticket){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
		$get_msg = null;
		
		$post_msg = "{
				\"component_appid\":\"{$component_appid}\" ,
				\"component_appsecret\": \"{$component_appsecret}\", 
				\"component_verify_ticket\": \"{$component_verify_ticket}\"
				}
				";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 获取预授权码
	 * 返回  pre_auth_code预授权码  expires_in（有效期，为20分钟）
	 * @param unknown $component_access_token 授权码
	 * @param unknown $component_appid
	 * @return Ambigous <mixed, boolean>
	 */
	 function getPre_auth_code($component_access_token,$component_appid){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token={$component_access_token}";
		$post_msg ="
				{
					\"component_appid\":\"{$component_appid}\" 
				}
				";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 使用授权码换取公众号的授权信息
	 * @param unknown $component_access_token第三方平台access_token
	 * @param unknown $component_appid 第三方平台appid
	 * @param unknown $authorization_code 授权code,会在授权成功时返回给第三方平台
	 * @return Ambigous <mixed, boolean>
	 */
	 function getAuthorization_info($component_access_token,$component_appid,$authorization_code){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token={$component_access_token}";
		$post_msg = "
				{
					\"component_appid\":\"{$component_appid}\" ,
					\"authorization_code\": \"{$authorization_code}\"
				}
				";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 获取（刷新）授权公众号的令牌
	 * @param unknown $component_access_token
	 * @param unknown $authorizer_appid
	 * @param unknown $authorizer_refresh_token 刷新公众号令牌的令牌
	 * @return Ambigous <mixed, boolean>
	 */
	 function getAuthorizer_access_token($component_access_token,$component_appid,$authorizer_appid,$authorizer_refresh_token){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token={$component_access_token}";
		$post_msg = "{
						\"component_appid\":\"{$component_appid}\",
						\"authorizer_appid\":\"{$authorizer_appid}\",
						\"authorizer_refresh_token\":\"{$authorizer_refresh_token}\"
					}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 获取授权方的账户信息(非授权信息注意区分)
	 * @param unknown $component_access_token
	 * @param unknown $authorizer_appid
	 * @param unknown $authorizer_appid
	 * @return Ambigous <mixed, boolean>
	 */
	 function getAuthorizer_info($component_access_token,$component_appid,$authorizer_appid){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token={$component_access_token}";
		$post_msg = "
				{
					\"component_appid\":\"{$component_appid}\",
					\"authorizer_appid\":\"{$authorizer_appid}\" 
					}
				";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	
	/**
	 * 获取授权方的选项设置信息
	 * @param unknown $component_access_token
	 * @param unknown $component_appid
	 * @param unknown $option_name
	 * @return Ambigous <mixed, boolean>
	 */
	 function getAuthorizer_option($component_access_token,$component_appid,$authorizer_appid,$option_name){
		$url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info";
		$get_msg['component_access_token'] = $component_access_token;
		$post_msg['component_appid'] = $component_appid;
		$post_msg['authorizer_appid'] = $authorizer_appid;
		$post_msg['option_name'] = $option_name;
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 设置授权方的选项信息
	 * @param unknown $component_access_token
	 * @param unknown $component_appid
	 * @param unknown $authorizer_appid
	 * @param unknown $option_name
	 * @param unknown $option_value
	 * @return Ambigous <mixed, boolean>
	 */
	 function setAuthorizer_option($component_access_token,$component_appid,$authorizer_appid,$option_name,$option_value){
		$url = "https://api.weixin.qq.com/cgi-bin/component/ api_set_authorizer_option";
		$get_msg['component_access_token'] = $component_access_token;
		$post_msg['component_appid'] = $component_appid;
		$post_msg['authorizer_appid'] = $authorizer_appid;
		$post_msg['option_name'] = $option_name;
		$post_msg['option_value'] = $option_value;
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	

?>