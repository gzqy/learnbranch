<?php
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';
/**
 * 数据统计
 * @author jm
 *
 */
	/**
	 * 用户每天新增和取消数据接口
	 * 搜索时间格式：2014-12-07 end_date允许设置的最大值为昨日
	 * 最多时间跨度为7天
	 */
	 function getDaliyUser($access_token,$begin_date,$end_date){
		$url = "https://api.weixin.qq.com/datacube/getusersummary?access_token={$access_token}";
			$post_msg = "{ 
	    	 \"begin_date\":\"{$begin_date}\", 
	   		 \"end_date\":\"{$end_date}\"
			 }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取每天总用户量
	 * 搜索时间格式：2014-12-07 end_date允许设置的最大值为昨日
	 * 最多时间跨度为7天
	 */
	 function getAllUser($access_token,$begin_date,$end_date){
		$url = "https://api.weixin.qq.com/datacube/getusercumulate?access_token={$access_token}";
		$post_msg = "{ 
	    	 \"begin_date\":\"{$begin_date}\", 
	   		 \"end_date\":\"{$end_date}\"
			 }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取一天内接口数据
	 * @param unknown $access_token
	 * @param unknown $begin_date
	 * @param unknown $end_date
	 * @return Ambigous <mixed, boolean>
	 */
	 function getDayMessage($access_token,$begin_date,$end_date){
		$url = "https://api.weixin.qq.com/datacube/getupstreammsg?access_token={$access_token}";
		$post_msg = "{ 
	    	 \"begin_date\":\"{$begin_date}\", 
	   		 \"end_date\":\"{$end_date}\"
			 }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取一周内接口数据
	 * @param unknown $access_token
	 * @param unknown $begin_date
	 * @param unknown $end_date
	 * @return Ambigous <mixed, boolean>
	 */
	 function getWeekyMessage($access_token,$begin_date,$end_date){
		$url = "https://api.weixin.qq.com/datacube/getupstreammsgweek?access_token={$access_token}";
		$post_msg = "{ 
	    	 \"begin_date\":\"{$begin_date}\", 
	   		 \"end_date\":\"{$end_date}\"
			 }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取一月内接口数据
	 * @param unknown $access_token
	 * @param unknown $begin_date
	 * @param unknown $end_date
	 * @return Ambigous <mixed, boolean>
	 */
	 function getMonthMessage($access_token,$begin_date,$end_date){
		$url = "https://api.weixin.qq.com/datacube/getupstreammsgmonth?access_token={$access_token}";
		$post_msg = "{ 
	    	 \"begin_date\":\"{$begin_date}\", 
	   		 \"end_date\":\"{$end_date}\"
			 }";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}

?>