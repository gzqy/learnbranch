<?php

require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';
/**
 * 素材管理
 * @author jm
 *
 */
	
	/**
	 * 上传普通单文件素材
	 * @param unknown $access_token
	 * @param string $type 图片（image）、语音（voice）、视频（video）和缩略图（thumb）
	 * @param unknown $real_path 文件真实路径 如D:/。。。/www/。。。
	 * @param unknown $file_info 格式如下：
	 		$file_info=array(
		    'filename'=>'/images/1.png',  //国片相对于网站根目录的路径
		    'content-type'=>'image/png',  //文件类型
		    'filelength'=>'11011'         //图文大小
				);
	 * @return Ambigous <mixed, boolean>
	 */
	 function add_general_material($access_token,$type,$real_path,$file_info){
		$url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}";
		$post_msg = array("media"=>"@{$real_path}","type"=>$type,"form-data"=>$file_info);
		echo $post_msg = str_replace("\\/", "/",  $post_msg);
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 上传永久文件素材
	 * @param unknown $access_token
	 * @param unknown $articles 
	 * 		thumb_media_id:图文消息的封面图片素材id（必须是永久mediaID）
	 * 	   	author:作者
	 * 		digest:图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
	 * 		show_cover_pic:是否显示封面，0为false，即不显示，1为true，即显示
	 * 		content:图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
	 * 		content_source_url：图文消息的原文地址，即点击“阅读原文”后的URL
	 * @return Ambigous <mixed, boolean> 新增的图文消息素材的media_id。
	 */
	function add_multiple_material($access_token,$articles){
		$url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$access_token}";
		$post_msg = "{\"articles\": [";
		foreach ( $articles as $article){
			$post_msg .="{
	       \"title\": {$article['title']},
	       \"thumb_media_id\": {$article['thumb_media_id']},
	       \"author\": {$article['author']},
	       \"digest\": {$article['digest']},
	       \"show_cover_pic\": {$article['show_cover_pic']},
	       \"content\": {$article['content']},
	       \"content_source_url\": {$article['content_source_url']}
   			 },";
		}
		$post_msg .= "]}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获得普通素材列表
	 * @param unknown $access_token
	 * @param unknown $type
	 * @param number $offset
	 * @param number $count
	 * @return Ambigous <mixed, boolean>
	 */
	function get_general_material_List($access_token,$type,$offset=0,$count=20){
		$url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$access_token}";
		$post_msg = "{
					    \"type\":\"{$type}\",
					    \"offset\":{$offset},
					    \"count\":{$count}
					}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取指定素材
	 * @return $media_id 获取素材的id
	 * @return Ambigous <mixed, boolean>
	 */
	function get_material($access_token,$media_id){
		$url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token={$access_token}";
		$post_msg = "{
					\"media_id\":{$media_id}
					}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	
	/**
	 * 获取素材总数
	 * @param unknown $access_token
	 * @return Ambigous <mixed, boolean>
	 * 返回说明：
			{
			  "voice_count":COUNT,
			  "video_count":COUNT,
			  "image_count":COUNT,
			  "news_count":COUNT
			}
	 */
	function  get_material_count($access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$access_token}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}
	/**
	 * 删除指定素材
	 * @return $media_id 删除素材的id
	 * @return Ambigous <mixed, boolean>
	 */
	function delete_material($access_token,$media_id){
		$url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token={$access_token}";
		echo $post_msg = "{\"media_id\":\"$media_id\"}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//则返回false
	}

?>