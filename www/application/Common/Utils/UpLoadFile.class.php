<?php

namespace Common\Utils;
use Common\Utils\ShopUtil;
/**
 * 文件上传工具类
 *
 * @author jm
 *        
 */
class UpLoadFile {
	/**
	 * 上传多个文件到默认文件夹
	 */
	public static function upLoadMultiFile($files) {
		 //检测是否存在文件夹  如果不存在 就创建
        $path ='./'.C("UPLOADPATH").date("Ymd");
        if (! file_exists ($path)) {
            mkdir ( "$path", 0777, true );
        }
		// 上传处理类
		$config = array (
				'rootPath' => './'.C("UPLOADPATH").date("Ymd").'/',
				'savePath' => '',
				'maxSize' => 11048576,
				'saveName' => array (
						'uniqid',
						'' 
				),
				'exts' => array (
						'jpg',
						'gif',
						'png',
						'jpeg',
						"txt",
						'zip' 
				),
				'autoSub' => false 
		);
		$upload = new \Think\Upload ( $config ); //
		$info = $upload->upload ( $files );
		// 开始上传
		if ($info) {
			// 上传成功
			foreach($info as $v){
				$url='./'.C("UPLOADPATH").date("Ymd").'/'.$v['savename'];
				ShopUtil::copy_thumb($url,150);
			}
			return $info;
		}else{
			return false;
		}
	}
	/**
	 * 将数组型的上传文件转化为文件数组
	 */
	public static function transf2FileArray($files) {
		$fileArray = array ();
		$nameArray = array ();
		$typeArray = array ();
		$tmp_nameArray = array ();
		$errorArray = array ();
		$sizeArray = array ();
		foreach ( $files as $k => $v ) {
			if ($k == 'name') {
				$nameArray = $v;
			} elseif ($k == 'type') {
				$typeArray = $v;
			} elseif ($k == 'tmp_name') {
				$tmp_nameArray = $v;
			} elseif ($k == 'error') {
				$errorArray = $v;
			} elseif ($k == 'size') {
				$sizeArray = $v;
			}
		}
		for($i = 0; $i < count ( $nameArray ); $i ++) {
			$fileArray [] = array (
					'name' => $nameArray [$i],
					'type' => $typeArray [$i],
					'tmp_name' => $tmp_nameArray [$i],
					'error' => $errorArray [$i],
					'size' => $sizeArray [$i] 
			);
		}
		return $fileArray;
	}
	/**
	 * 上传文件到默认文件夹
	 */
	public static function upLoadFile() {
		// 上传处理类
		$config = array (
				'rootPath' => './' . C ( "UPLOADPATH" ),
				'savePath' => '',
				'maxSize' => 11048576,
				'saveName' => array (
						'uniqid',
						'' 
				),
				'exts' => array (
						'jpg',
						'gif',
						'png',
						'jpeg',
						"txt",
						'zip',
						'mp3',
						'wma',
						'wav',
						'amr', 
				),
				'autoSub' => false 
		);
		$upload = new \Think\Upload ( $config ); //
		$info = $upload->upload ();
		// 开始上传
		if ($info) {
			// 上传成功
			// 写入附件数据库信息
			$first = array_shift ( $info );
			return $first;
		}
	}
	
	/**
	 * 上传文件到默认文件夹，指定对象
	 */
	public static function upLoadFileByObject($file) {
		// 上传处理类
		$config = array (
				'rootPath' => './' . C ( "UPLOADPATH" ),
				'savePath' => '',
				'maxSize' => 11048576,
				'saveName' => array (
						'uniqid',
						'' 
				),
				'exts' => array (
						'jpg',
						'gif',
						'png',
						'jpeg',
						"txt",
						'zip' 
				),
				'autoSub' => false 
		);
		$upload = new \Think\Upload ( $config ); //
		$info = $upload->uploadOne ( $file );
		// 开始上传
		if ($info) {
			// 上传成功
			// 写入附件数据库信息
			return $info;
		}
	}
	/**
	 * 图片地址格式为图片相对地址，不加/
	 *
	 * @param unknown $image        	
	 */
	public static function deleteImage($image) {
		if (! $image) {
			return true;
		}
		@unlink ( C ( 'UPLOAD_PATH.IMAGE' ) . $image );
		return true;
	}
	/**
	 * 获取图片的绝对地址,$image格式为 4956272f65c5448.jpg
	 *
	 * @param unknown $image        	
	 */
	public static function getImageUrl($image) {
		return C ( 'DOWNLOADPATH' ) . $image;
	}
	/**
	 * 删除商品图片
	 *
	 * @param unknown $imageList        	
	 * @return boolean
	 */
	public static function deleteItemImage($imageList) {
		if (! $imageList) {
			return true;
		}
		$imageList = json_decode ( $imageList, true );
		// $thumb = $imageList ['thumb'];
		// @unlink ( C ( 'UPLOAD_PATH.IMAGE' ) . $thumb );
		foreach ( $imageList ['thumb'] as $portrait ) {
			@unlink ( C ( 'UPLOAD_PATH.IMAGE' ) . $portrait ['url'] );
		}
		foreach ( $imageList ['photo'] as $photo ) {
			@unlink ( C ( 'UPLOAD_PATH.IMAGE' ) . $photo ['url'] );
		}
		return true;
	}
	/**
	 * 复制供应商品的图片
	 *
	 * @param unknown $imageList        	
	 */
	public static function copyImageList($itemId, $imageList) {
		if (! $imageList) {
			return "";
		}
		$imageList = json_decode ( $imageList, true );
		$thumb = $imageList ['thumb'];
		$newImageList = array ();
		// 复制thumb
		foreach ( $thumb as $portrait ) {
			$thumbSrc = C ( 'UPLOAD_PATH.IMAGE' ) . $portrait ['url'];
			$thumbTo = C ( 'UPLOAD_PATH.IMAGE' ) . $itemId . $portrait ['url'];

			copy ( $thumbSrc, $thumbTo );
			$thumbSrcInfo = getimagesize ( $thumbTo );
			if (! $thumbTo) {
				return false;
			}
			$newImageList ['thumb'] [] = array (
					'url' => $itemId . $portrait ['url'],
					'alt' => $portrait ['alt'] 
			);
		}
		// $srcFile = C ( 'UPLOAD_PATH.IMAGE' ) . $thumb;
		// $toFile = C ( 'UPLOAD_PATH.IMAGE' ) . $itemId . $thumb;
		// $srcInfo = copy ( $srcFile, $toFile );
		// if (! $srcInfo) {
		// return false;
		// }
		// $newImageList ['thumb'] = $itemId . $thumb;
		// 复制photos
		$photos = $imageList ['photo'];
		foreach ( $photos as $photo ) {
			$tempSrc = C ( 'UPLOAD_PATH.IMAGE' ) . $photo ['url'];
			$tempTo = C ( 'UPLOAD_PATH.IMAGE' ) . $itemId . $photo ['url'];
			copy ( $tempSrc, $tempTo );
			$tempSrcInfo = getimagesize ( $tempTo );
			// dump($tempTo);
			if (! $tempSrcInfo) {
				return false;
			}
			$newImageList ['photo'] [] = array (
					'url' => $itemId . $photo ['url'],
					'alt' => $photo ['alt'] 
			);
		}
		return json_encode ( $newImageList );
	}
}

?>