<?php

include_once "wxBizMsgCrypt.php";

// 第三方发送消息给公众平台
$encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
$token = "pamtest";
$timeStamp = "1445759532";
$nonce = "1866240293";
$appId = "11111111111";
$text = "<xml><ToUserName><![CDATA[我是中文]]></ToUserName></xml>";


$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
$encryptMsg = '';
$errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
if ($errCode == 0) {
	print("加密后: " . $encryptMsg . "\n");
	var_dump($encryptMsg);
} else {
	print($errCode . "\n");
}
//获取加密码
$xml_tree = new DOMDocument();
$xml_tree->loadXML($encryptMsg);
$array_e = $xml_tree->getElementsByTagName('Encrypt');
$array_s = $xml_tree->getElementsByTagName('MsgSignature');
echo "encrypt:".$encrypt = $array_e->item(0)->nodeValue.'<br>';
echo "msg_sign:".$msg_sign = $array_s->item(0)->nodeValue.'<br>';

$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
$from_xml = sprintf($format, $encrypt);
$from_xml = "<xml>
    <ToUserName><![CDATA[ffff]]></ToUserName>
    <Encrypt><![CDATA[J+4byFwR7OAm4yjCZIpG3CzeoFVlVEQsQi3M8bUlbV/iK+Hv6B0beYrDHJdgupgSA6mgXMMEUemkInbY9h03UyjliLfuEOjJnL8DSdtdZaRo2S5p5apw8gTf6cCckHWaXaDOXCiYOrYJ52sBxPXa6a87+x6A39NxCm3yP8g+J/8D1nu7WBxGnKcLFYsGKDGvVZZbOtPlIhBX80nKTIpvsL6TkH9wo9MXSuVHM9TDK5q1t9fISaoYaS8GSEtoH1Kx9qnFFHcwBILz1luMDsDxrutCEfHCt/LY1XpAlmpgaZ2zMvfv8YOUgkf71a9VypN554gm38Ns8dxKC6x84KqIeybuFZAjIiiC8PYUT/EFEr6TYeSaUunoiQiMDmsup2B+lpAY2jrsUtEpFPiSmYRCi0J6pb/8TtVp5skuRaSIrNcta0fPrYfpweSP30ZdEu321v9TEuoY5n52Q6ET1tvFu3X/lNBdkByO2KSrYNfs6Ka4XwvRYbuPKwhjh2F4r0VPt+ozjPLk6Gcm8guaXipSQ5NDcJTvkMs0Y/sXFingh9k=]]></Encrypt>
</xml>
";
$msg_sign = "3ff3f340c6e77807e2e2cf394eb53b0eaf11e06e";
// 第三方收到公众号平台发送的消息
$msg = '';
$errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
if ($errCode == 0) {
	print("解密后: " . $msg . "\n");
	var_dump($msg);
} else {
	print($errCode . "\n");
}
