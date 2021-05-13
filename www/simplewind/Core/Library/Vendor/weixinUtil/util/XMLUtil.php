<?php
/**
 * ��xml����
 * @author jm
 *
 */
class XMLUtil {
	
	/**
	 * ���Ԫ�����ȡԪ������
	 * @param unknown $item_name
	 */
	public static function getItemValue($content,$item_name){
		$xml = new DOMDocument();
		$xml->loadXML($content);
		$reqToUserName = $xml->getElementsByTagName($item_name)->item(0)->nodeValue;
		return $reqToUserName;
	}
}

?>