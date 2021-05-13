<?php
require_once 'weixinUtil/util/passwd/wxBizMsgCrypt.php';
require_once 'weixinUtil/util/XMLUtil.php';
require_once 'weixinUtil/util/CURLUtil.php';

/**
 * 发送接收消息相关接口
 * @author jm
 *
 */
	
	/**
	 * 对微信传过来消息（有加解密过程）
	 * @param unknown $appid
	 * @param unknown $secret
	 * @return Ambigous <mixed, boolean> 返回中根据MsgType来判断类型
	 */
	function getWeChat_message($token,$encodingAesKey,$appId){
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
			$content['ToUserName'] = XMLUtil::getItemValue($msg,"ToUserName");
			$content['FromUserName'] = XMLUtil::getItemValue($msg,"FromUserName");
			$content['CreateTime'] = XMLUtil::getItemValue($msg,"CreateTime");
			$content['MsgType'] = XMLUtil::getItemValue($msg,"MsgType");
			//对不同类型获得不同具体数据（待扩充）
			if($content['MsgType'] == "text"){//文本内容
				$content['Content'] = XMLUtil::getItemValue($msg,"Content");
			}else if($content['MsgType'] == "event"){//点击自定义菜单事件或订阅取消订阅事件
				$content['Event'] = XMLUtil::getItemValue($msg,"Event");
				if(strcasecmp($content['Event'],"CLICK") === 0){//key事件
					$content['EventKey'] = XMLUtil::getItemValue($msg,"EventKey");
				}
			}else {
				return "错误代码".$errCode;
			}
			//加密用消息体，可用来加密返回用
			$content['msg_signature'] =$msg_signature ;
			$content['timestamp'] = $timestamp;
			$content['nonce'] = $nonce;
			return $content;
		}
	}
	
	/**
	 * 不想返回数据时，返回success通知接收数据成功
	 * @return string
	 */
	 function replay_defult(){
		echo  "success";
	 }
	
	/**
	 * 消息群发(文本类型)
	 * @param unknown $access_token
	 * @param boolean $is_to_all 是否对所有用户发送信息
	 * @param unknown $group_id 指定发送用户分组，不是id
	 * @param unknown $content 发送内容
	 */
	function sendMassText($access_token,$is_to_all,$group_id,$content){
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";
		$post_msg = "{
				   \"filter\":{
				      \"is_to_all\":{$is_to_all},
				      \"group_id\":\"{$group_id}\"
				   },
				   \"text\":{
				      \"content\":\"{$content}\"
				   },
				    \"msgtype\":\"text\"
					}";
		$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
		return $rs;//返回数据示例{"errcode":0,}
	}
	
	/**
	 * 返回文字内容
	 */
	 function replay_test($token, $encodingAesKey, $appId,$timeStamp,$nonce,$toUser,$fromUser,$text){
		$context ="<xml>
			<ToUserName><![CDATA[{$toUser}]]></ToUserName>
			<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
			<CreateTime>{$timeStamp}</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[{$text}]]></Content>
			</xml>";
		$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
		//加密
		$rs = "";
		$errCode = $pc->encryptMsg($context, $timeStamp, $nonce, $rs);
		if($errCode != 0){//错误消息
			$data['content'] = $errCode;
			M("log")->add($data);
			WeChat_public_message::replay_defult();
			return $errCode;
		}
		echo $rs;//打印加密后结果
		return true;//正确返回true
	}
	
	/**
	 * 返回图片内容
	 */
	function replay_picture($token, $encodingAesKey, $appId,$timeStamp,$nonce,$toUser,$fromUser,$media_id){
			$context ="<xml>
			<ToUserName><![CDATA[{$toUser}]]></ToUserName>
			<FromUserName><![CDATA[{$fromUser}]]></FromUserName>
			<CreateTime>{$timeStamp}</CreateTime>
			<MsgType><![CDATA[image]]></MsgType>
			<Image>
			<MediaId><![CDATA[{$media_id}]]></MediaId>
			</Image>
			</xml>";
			$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
			//加密
			$rs = "";
			$errCode = $pc->encryptMsg($context, $timeStamp, $nonce, $rs);
			if($errCode != 0){//错误消息
				WeChat_public_message::replay_defult();
				return $errCode;
			}
			echo $rs;//打印加密后结果
			return true;//正确返回true
	}
	
	/**
	 * 返回图文内容(尚未完成)
	 */
	 function replay_pictureAndText($token, $encodingAesKey, $appId,$timeStamp,$nonce,$toUser,$fromUser,$media_id){
			$context ="<xml>
					<ToUserName><![CDATA[toUser]]></ToUserName>
					<FromUserName><![CDATA[fromUser]]></FromUserName>
					<CreateTime>12345678</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>2</ArticleCount>
					<Articles>
					<item>
					<Title><![CDATA[title1]]></Title> 
					<Description><![CDATA[description1]]></Description>
					<PicUrl><![CDATA[picurl]]></PicUrl>
					<Url><![CDATA[url]]></Url>
					</item>
					<item>
					<Title><![CDATA[title]]></Title>
					<Description><![CDATA[description]]></Description>
					<PicUrl><![CDATA[picurl]]></PicUrl>
					<Url><![CDATA[url]]></Url>
					</item>
					</Articles>
						</xml> ";
			$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
			//加密
			$rs = "";
			$errCode = $pc->encryptMsg($context, $timeStamp, $nonce, $rs);
			if($errCode != 0){//错误消息
			WeChat_public_message::replay_defult();
			return $errCode;
			}
		echo $rs;//打印加密后结果
		return true;//正确返回true
	}
	
	/**
	 * 发送客服消息
	 * @param unknown $access_token
	 * @param boolean $openId 用户id
	 * @param unknown $content 发送内容
	 */
	function sendCustomText($access_token,$openId,$content){
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		$post_msg = "{
		    \"touser\":\"{$openId}\",
		    \"msgtype\":\"text\",
		    \"text\":
		    {
		         \"content\":\"{$content}\"
		    }
		}";
	$rs = CURLUtil::accessWebJsonToArray($url, $post_msg, $get_msg);
	return $rs;//返回数据示例{"errcode":0,}
	}
?>