<?php

/**
 * 常用的正则表达式来验证信息.如:网址 邮箱 手机号等
 * @author feng
 */
namespace Common\Utils;

class CheckValidUtil {
	/**
	 * 正则表达式验证email格式
	 *
	 * @param string $str
	 *        	所要验证的邮箱地址
	 * @return boolean
	 */
	public static function isEmail($str) {
		if (! $str) {
			return false;
		}
		return preg_match ( '#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str ) ? true : false;
	}
	/**
	 * 正则表达式验证网址
	 *
	 * @param string $str
	 *        	所要验证的网址
	 * @return boolean
	 */
	public static function isUrl($str) {
		if (! $str) {
			return false;
		}
		return preg_match ( '#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str ) ? true : false;
	}
	/**
	 * 验证字符串中是否含有汉字
	 *
	 * @param integer $string
	 *        	所要验证的字符串。注：字符串编码仅支持UTF-8
	 * @return boolean
	 */
	public static function isChineseCharacter($string) {
		if (! $string) {
			return false;
		}
		return preg_match ( '~[\x{4e00}-\x{9fa5}]+~u', $string ) ? true : false;
	}
	/**
	 * 验证字符串中是否含有非法字符
	 *
	 * @param string $string
	 *        	待验证的字符串
	 * @return boolean
	 */
	public static function isInvalidStr($string) {
		if (! $string) {
			return false;
		}
		return preg_match ( '#[!#$%^&*(){}~`"\';:?+=<>/\[\]]+#', $string ) ? true : false;
	}
	/**
	 * 用正则表达式验证邮证编码
	 *
	 * @param integer $num
	 *        	所要验证的邮政编码
	 * @return boolean
	 */
	public static function isPostNum($num) {
		if (! $num) {
			return false;
		}
		return preg_match ( '#^[1-9][0-9]{5}$#', $num ) ? true : false;
	}
	/**
	 * 正则表达式验证身份证号码
	 *
	 * @param integer $num
	 *        	所要验证的身份证号码
	 * @return boolean
	 */
	public static function isPersonalCard($num) {
		if (! $num) {
			return false;
		}
		return preg_match ( '#^[\d]{15}$|^[\d]{18}$#', $num ) ? true : false;
	}
	/**
	 * 正则表达式验证IP地址, 注:仅限IPv4
	 *
	 * @param string $str
	 *        	所要验证的IP地址
	 * @return boolean
	 */
	public static function isIp($str) {
		if (! $str) {
			return false;
		}
		if (! preg_match ( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str )) {
			return false;
		}
		$ipArray = explode ( '.', $str );
		// 真实的ip地址每个数字不能大于255（0-255）
		return ($ipArray [0] <= 255 && $ipArray [1] <= 255 && $ipArray [2] <= 255 && $ipArray [3] <= 255) ? true : false;
	}
	/**
	 * 用正则表达式验证出版物的ISBN号
	 *
	 * @param integer $str
	 *        	所要验证的ISBN号,通常是由13位数字构成
	 * @return boolean
	 */
	public static function isBookIsbn($str) {
		if (! $str) {
			return false;
		}
		return preg_match ( '#^978[\d]{10}$|^978-[\d]{10}$#', $str ) ? true : false;
	}
	/**
	 * 用正则表达式验证手机号码(中国大陆区)
	 *
	 * @param integer $num
	 *        	所要验证的手机号
	 * @return boolean
	 */
	public static function isMobile($num) {
		if (!$num ) {
			return false;
		}
		// 13[0-9]{9}|15[0-9]{9}|18[0-9]{9}|145[0-9]{8}|147[0-9]{8}|17[0-9]{9}
		return preg_match ( '#^13[0-9]\d{8}$|^14[0-9]\d{8}$|^15[0-9]\d{8}$|^18[0-9]\d{8}$|^17[0-9]\d{8}$#', $num ) ? true : false;
	}
	/**
	 * 检查字符串是否为空
	 *
	 * @access public
	 * @param string $string
	 *        	字符串内容
	 * @return boolean
	 */
	public static function isMust($string = null) {
		// 参数分析
		if (is_null ( $string )) {
			return false;
		}
		return empty ( $string ) ? false : true;
	}
	/**
	 * 检查字符串长度
	 *
	 * @access public
	 * @param string $string
	 *        	字符串内容
	 * @param integer $min
	 *        	最小的字符串数
	 * @param integer $max
	 *        	最大的字符串数
	 */
	public static function isLength($string = null, $min = 0, $max = 255) {
		// 参数分析
		if (is_null ( $string )) {
			return false;
		}
		// 获取字符串长度
		$length = strlen ( trim ( $string ) );
		return (($length >= ( int ) $min) && ($length <= ( int ) $max)) ? true : false;
	}
	/**
	 * 检测设备类型是否正确,1为苹果，2为安卓
	 *
	 * @param unknown $deviceType        	
	 * @return boolean
	 */
	public static function isDeviceType($deviceType) {
		if (in_array ( $deviceType, array (
				Constants::DEVICE_TYPE_IOS,
				Constants::DEVICE_TYPE_ANDROID 
		) )) {
			return true;
		}
		return false;
	}
	/**
	 * 检测性别是否正确，0表示女，1表示男
	 *
	 * @param unknown $gender        	
	 * @return boolean
	 */
	public static function isGender($gender) {
		if (in_array ( $gender, array (
				Constants::SEX_FEMALE,
				Constants::SEX_MALE 
		) )) {
			return true;
		}
		return false;
	}
	/**
	 * 数组是否包含数组
	 *
	 * @param unknown $checkArray        	
	 * @param unknown $baseArray        	
	 * @return boolean
	 */
	public static function isInArray($checkArray, $baseArray) {
		foreach ( $checkArray as $tag ) {
			if (! in_array ( $tag, $baseArray ))
				return false;
		}
		return true;
	}
	/**
	 * 匹配以逗号分隔数字的字符串,可以是一个数字
	 *
	 * @param unknown $str        	
	 * @return boolean
	 */
	public static function isSplitByCommas($str) {
		if (! $str) {
			return false;
		}
		return preg_match ( '/^([0-9]+,)*[0-9]+$/', $str ) ? true : false;
	}
	/**
	 * 检测是否为订单状态
	 *
	 * @param unknown $status        	
	 */
	public static function isOrderStatus($status) {
		$orderStatus = C ( 'ORDER_STATUS' );
		if (in_array ( $status, array (
				$orderStatus ['START'] ['VAL'],
				$orderStatus ['WAIT_FOR_PAY'] ['VAL'],
				$orderStatus ['WAIT_FOR_ITEM'] ['VAL'],
				$orderStatus ['WAIT_FOR_CONFIRM'] ['VAL'],
				$orderStatus ['WAIT_FOR_SUGGEST'] ['VAL'],
				$orderStatus ['COMPLETE'] ['VAL'],
				$orderStatus ['CLOSE'] ['VAL'],
				$orderStatus ['REJECT'] ['VAL'] 
		) )) {
			return true;
		}
		return false;
	}
	/**
	 * 检测是否为退货类型
	 *
	 * @param unknown $cancelType        	
	 * @return boolean
	 */
	public static function isCancelType($cancelType) {
		if (! in_array ( $cancelType, array (
				C ( 'CANCEL_ORDER.ONLY_MONEY' ),
				C ( 'CANCEL_ORDER.MONEY_ITEM' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否为处理结果
	 *
	 * @param unknown $dealResult        	
	 */
	public static function isDealResult($dealResult) {
		if (! in_array ( $dealResult, array (
				C ( 'DEAL_RESULT.WAIT_DEAL' ),
				C ( 'DEAL_RESULT.WAIT_SEND' ),
				C ( 'DEAL_RESULT.WAIT_CONFIRM' ),
				C ( 'DEAL_RESULT.COMPLETE' ),
				C ( 'DEAL_RESULT.REJECT' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否为商品状态
	 *
	 * @param unknown $status        	
	 */
	public static function isItemStatus($status) {
		if (! in_array ( $status, array (
				C ( 'ITEM_STATUS.IS_ON_SALE' ),
				C ( 'ITEM_STATUS.IS_ON_STORE' ),
				C ( 'ITEM_STATUS.TOTAL' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 判断是否为商品排序方式
	 *
	 * @param unknown $sort        	
	 */
	public static function isItemSort($sort) {
		if (! in_array ( $sort, array (
				C ( 'ITEM_SORT.SALE_NUM' ),
				C ( 'ITEM_SORT.STORE_NUM' ),
				C ( 'ITEM_SORT.RELEASE_TIME' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否是设置商品状态
	 *
	 * @param unknown $status        	
	 */
	public static function isSetItemStatus($status) {
		if (! in_array ( $status, array (
				C ( 'ITEM_STATUS.IS_ON_SALE' ),
				C ( 'ITEM_STATUS.IS_ON_STORE' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否是处理分销申请的状态
	 *
	 * @param unknown $status        	
	 */
	public static function isRetailApply($status) {
		if (! in_array ( $status, array (
				C ( 'SUPPLY_VERIFY.AGREE' ),
				C ( 'SUPPLY_VERIFY.DISAGREE' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否是设置分销审批的状态
	 *
	 * @param unknown $status        	
	 */
	public static function isSupplyAuto($status) {
		if (! in_array ( $status, array (
				C ( 'SUPPLY_AUTO.YES' ),
				C ( 'SUPPLY_AUTO.NO' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否为推荐类型
	 *
	 * @param unknown $status        	
	 */
	public static function isRecommendStatus($status) {
		if (! in_array ( $status, array (
				C ( 'RECOMMEND.YES' ),
				C ( 'RECOMMEND.NO' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否为获取所有商品/商店接口的类型
	 *
	 * @param unknown $type        	
	 */
	public static function isOtherShopType($type) {
		if (! in_array ( $type, array (
				C ( 'OTHER_SHOP.SHOP' ),
				C ( 'OTHER_SHOP.ITEM' ) 
		) )) {
			return false;
		}
		return true;
	}
	/**
	 * 检测是否为支付渠道
	 *
	 * @param unknown $channel        	
	 */
	public static function isChannel($channel) {
		if (! in_array ( $channel, array (
				C ( 'PAY_CHANNEL.ALIPAY_WAP' ),
				C ( 'PAY_CHANNEL.UPMP_WAP' ),
				C ( 'PAY_CHANNEL.BFB_WAP' ),
				C ( 'PAY_CHANNEL.UPACP_WAP' ),
				C ( 'PAY_CHANNEL.WX_PUB' ),
				C ( 'PAY_CHANNEL.WX_PUB_QR' ),
				C ( 'PAY_CHANNEL.YEEPAY_WAP' ),
				C ( 'PAY_CHANNEL.JDPAY_WAP' ) 
		) )) {
			return false;
		}
		return true;
	}
}
 
