<?php

namespace Common\Utils;

class RandomValidCode {
	/**
	 * 随机生成n位验证码
	 * @param number $length
	 * @return number
	 */
	public static function generate_code($length = 6) {
		return rand(pow(10,($length-1)), pow(10,$length)-1);
	}
}

?>