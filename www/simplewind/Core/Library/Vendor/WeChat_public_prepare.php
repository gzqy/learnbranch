<?php
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';

/**
 * 微信公众号调用接口
 * @author jm
 *
 */
	
	/**
	 * 获取access token
	 * grant_type不知道对不对
	 * @param unknown $appid
	 * @param unknown $secret
	 * @return Ambigous <mixed, boolean> 返回access_token（获取到的凭证）、expires_in
	 * 错误返回{"errcode":40013,"errmsg":"invalid appid"}
	 */
	  function getAccess_token($appid,$secret){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，若为假，则返回false
	}
	
	/**
	 * 获取微信服务器IP地址列表(好像没啥用，先不写)
	 */
	  function getCallback_ip(){
		
	}

?>