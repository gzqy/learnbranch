<?php
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';
/**
 * 自定义菜单回复
 * @author Administrator
 *
 */
	/**
	 * 创建分组
	 * 
	 */
	 function addMenu($access_token,$content){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
		$count = count($content['button'] );
		$post_msg = "{\"button\": [";
		foreach($content['button'] as $key=>$button){
			$post_msg .= "{";
				if($button['name']){
					$post_msg .= "\"name\": \"{$button['name']}\",";
				}
				if($button['type']){
					$post_msg .= "\"type\": \"{$button['type']}\",";
				}
				if($button['key']){
					$post_msg .= "\"key\": \"{$button['key']}\"";
				}
				if($button['url']){
					$post_msg .= "\"url\": \"{$button['url']}\"";
					if($button['sub_button'] && count($button['sub_button'])>0 ){
						$post_msg .= ",";
					}
				}
				if($button['sub_button'] && count($button['sub_button'])>0 ){
					$post_msg .= "\"sub_button\": [";
					$tmp_count = count( $button['sub_button'] );
					foreach ($button['sub_button'] as $key=>$sub_button){
						$post_msg .=
						"{
						\"type\": \"{$sub_button['type']}\",
						\"name\": \"{$sub_button['name']}\",
						\"key\": \"{$sub_button['key']}\"
						}";
						if($key !== $tmp_count-1 ){
							$post_msg .= ",";
						}
					}
					$post_msg .= "]";
				}
			$post_msg .= "}";
			if($key !== $count-1 ){
				$post_msg .= ",";
			}
		}
		$post_msg .= "]}";
		dump($post_msg);
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}

	/**
	 * 查询菜单
	 */
	 function selectMenu($access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 删除菜单
	 */
	 function deleteMenu($access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//正确的Json返回结果:{"errcode":0,"errmsg":"ok"}
	}

?>