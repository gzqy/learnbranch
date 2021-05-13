<?php
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';
/**
 * 微信用户分组管理
 * 1、自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。
   2、一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
   3、创建自定义菜单后，由于微信客户端缓存，需要24小时微信客户端才会展现出来。测试时可以尝试取消关注公众账号后再次关注，则可以看到创建后的效果。
 * @author Administrator
 *
 */
	/**
	 * 创建分组
	 * name 分组名字
	 */
	 function addGroup($access_token,$name){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$access_token}";
		$post_msg = "{\"group\":{\"name\":\"{$name}\"}}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，返回添加分组的id。name，则返回false
	}
	
	/**
	 * 查询分组
	 * 返回groups数组，包含id。name，count 错误则返回false
	 */
	 function getGroups($access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，groupid:102   错误则返回false
	}
	
	/**
	 * 查询用户所在分组
	 */
	 function selectUserGroup($access_token,$openid){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create";
		$get_msg['access_token'] = $access_token;
		$post_msg = "{'openid':'{$openid}'}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，错误则返回false
	}
	
	/**
	 * 修改分组名
	 */
	 function updateGroupName($access_token,$id,$name){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update";
		$get_msg['access_token'] = $access_token;
		$post_msg = "{'group':{'id':{$id},'name':'{$name}'}}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，{"errcode": 0, "errmsg": "ok"}，错误则返回false
	}
	
	/**
	 * 移动用户分组
	 */
	 function moveGroup($access_token,$openid,$to_groupid){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";
		$post_msg = "{\"openid\":\"{$openid}\",\"to_groupid\":{$to_groupid} }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，{"errcode": 0, "errmsg": "ok"}，错误则返回false
	}
	
	/**
	 * 批量移动用户分组
	 */
	 function moveGroupBatch($access_token,$openid_list,$to_groupid){
	 	$url = "https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token={$access_token}";
	 	$count = count($openid_list);
	 	$post_msg = "{\"openid_list\":[";
	 			foreach ($openid_list as $key=>$openid){
	 				$post_msg .= "\"{$openid}\"";
	 				if($key != $count-1){
	 					$post_msg .= ",";
	 				}
	 			}
				$post_msg .= "],\"to_groupid\":{$to_groupid}}";
		echo $post_msg;
	 	$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
	 	return $rs;//返回结果，{"errcode": 0, "errmsg": "ok"}，错误则返回false
	}
	
	/**
	 * 删除分组（未完成，group参数咋用的，没用吧）
	 */
	 function deleteGroup($access_token,$id){
		$url = "https://api.weixin.qq.com/cgi-bin/groups/delete?access_token={$access_token}";
		$post_msg = "{\"group\":{\"id\":{$id}}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，{"errcode": 0, "errmsg": "ok"}，错误则返回false
	}
	
	/**
	 * 获取用户列表
	 * @param unknown $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
	 * @return Ambigous <mixed, boolean>
	 * 正确时返回JSON数据包
	 * {"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
	 */
	 function getUserList($access_token,$next_openid){
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$next_openid}";
		$post_msg = null;
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，错误则返回false
	}
	
	/**
	 * 获取某个用户基本信息（包括UnionID机制）
	 * @param unknown $access_token
	 * @param unknown $next_openid
	 * @return Ambigous <mixed, boolean>
	 */
	 function getUserDetail($access_token,$openid){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
		$post_msg = null;
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，错误则返回false
	}
	
	/**
	 * 批量获取某个用户基本信息
	 * @param unknown $access_token
	 * @param unknown $openids  要获取信息的数组
	 * @return Ambigous <mixed, boolean>
	 */
	 function getUserListDetail($access_token,$openids){
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget";
		$get_msg['access_token'] = $access_token;
		$get_msg['openid'] = $openid;
		//拼接post
		$post_msg = "{'user_list': [";
		   foreach ($openids as $openid){
			   	$post_msg .= "{
	            	'openid': '{$openid}', 
	            	'lang': 'zh-CN'
	       	   		}, 
			   	";    
		   }
       	$post_msg .= "]}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回结果，错误则返回false
	}

?>