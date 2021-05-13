<?php
namespace Common\Utils;
class QRUtil {
	/**
	 * 获取二维码
	 * $errorCorrectionLevel  纠错级别：L、M、Q、H
	 * $matrixPointSize点的大小：1到10
	 */
	public static function getQRCode($url,$errorCorrectionLevel='L',$matrixPointSize = 10){
		require_once 'phpqrcode.class.php';
		//创建一个二维码文件
		QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 1);
		//输入二维码到浏览器
		QRcode::png($url);
	}

	/**
	 * 获取待logio标的二维码
	 */
	public static function getQRCode1($url,$filename,$errorCorrectionLevel='L',$matrixPointSize = 10){
		require_once 'phpqrcode.class.php';
			QRcode::png ( $url, './statics/code/ewm.png', $errorCorrectionLevel, $matrixPointSize, 2);//不带Logo二维码的文件名
			$logo = $filename;//需要显示在二维码中的Logo图像
			$QR = './statics/code/ewm.png';
			if ($logo !== FALSE) {
			    $QR = imagecreatefromstring ( file_get_contents ( $QR ) );
			    $logo = imagecreatefromstring ( file_get_contents ( $logo ) );
			    $QR_width = imagesx ( $QR );
			    $QR_height = imagesy ( $QR );
			    $logo_width = imagesx ( $logo );
			    $logo_height = imagesy ( $logo );
			    $logo_qr_width = $QR_width / 5;
			    $scale = $logo_width / $logo_qr_width;
			    $logo_qr_height = $logo_height / $scale;
			    $from_width = ($QR_width - $logo_qr_width) / 2;
			    $from_height = ($QR_width - $logo_qr_width) / 5;
			    imagecopyresampled ( $QR, $logo, $from_width, $from_height, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height );
			}
			$new = './statics/code/helloweixin.png';
			imagepng($QR, $new);   
			echo '<img src="'.$new.'">';

		// //创建一个二维码文件
		// QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 1);
		// //输入二维码到浏览器
		// QRcode::png($url,$filename);
	}
}
