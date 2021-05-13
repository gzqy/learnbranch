<?php

/**
 * 模拟form访问网页工具类
 * @author jm
 *
 */
class CURLUtil {
	
	static $timeout = 5;//最大延迟
	
	/**
	 * 访问web
	 * @param unknown $url 
	 * @param unknown $post_msg 发送post数组
	 * @param unknown $get_msg 发送get数组
	 */
	public static function accessWeb($url,$post_msg,$get_msg){
		$ch = curl_init();
		if($get_msg){//发送get数组
			$url .='?';
			foreach ($get_msg as $key=>$value){
				$url .= '{$key}={$value}&';
			}
		}
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);//结果不直接输出在页面
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);从证书中检查SSL加密算法是否存在  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//将终止从服务端进行验证，使能访问https
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, CURLUtil::$timeout);//最大延迟
		if($post_msg){//发送post数组
			curl_setopt($ch, CURLOPT_POST, true);//开启post
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_msg );
		}
		$file_contents = curl_exec($ch);//执行
		curl_close($ch);//关闭流
		return $file_contents;
	}
	
	/**
	 * 返回json格式的网页变为数组返回
	 * @param unknown $url
	 * @param unknown $post_msg
	 * @param unknown $get_msg
	 * @return mixed
	 */
	public static function accessWebJsonToArray($url,$post_msg,$get_msg){
		$rs = CURLUtil::accessWeb($url, $post_msg, $get_msg);
		if($rs){
			return json_decode($rs,true);
		}
		return $rs;
	}
}

//   $curl = new CURLUtil();
//   $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3a5657bf3462f442&secret=e42b132ca5d0c1cca432dc35ded0e7fc';
//   echo $curl->accessWeb($url, null, null);
?>