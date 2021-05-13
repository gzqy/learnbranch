<?php
	include_once "wxBizMsgCrypt.php";
	include_once "../XMLUtil.php";
	$token = "pamtest";
	$encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
	$appId = "11111111111";
	
	$encrypt_type = $_REQUEST["encrypt_type"];
	// 微信加密签名
	$msgSignature = $_REQUEST["msg_signature"];
	// 时间戳
	$timeStamp = $_REQUEST["timestamp"];
	// 随机数
	$nonce = $_REQUEST["nonce"];
	//密文
	$text = file_get_contents("php://input");
	
	//打印
	$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
	$msg = "";
	$errCode = $pc->decryptMsg($msgSignature, $timeStamp, $nonce, $text, $msg);
	$content = XMLUtil::getItemValue($msg,"Content");
	$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
 	fwrite($myfile, "msgSignature".$msgSignature.'\r\r\n');
 	fwrite($myfile, "timeStamp".$timeStamp.'\r\r\n');
 	fwrite($myfile, "nonce".$nonce.'\r\r\n');
  	//fwrite($myfile, "text".$text.'\r\r\n');
 	fwrite($myfile, "errCode:".$errCode.'\r\r\n');
 	fwrite($myfile, "result::".$content.'\r\r\n');
	$txt = "Steve Jobs\n";
	fwrite($myfile, $txt);

	fclose($myfile);
	echo $content;
	return "success";
?>