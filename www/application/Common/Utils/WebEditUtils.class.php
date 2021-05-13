<?php

namespace Common\Utils;

use Common\Utils\simple_html_dom;

/**
 *
 *
 *
 *
 * 网页编辑工具类
 *
 * @author gavin
 *        
 */
class WebEditUtils {
	/**
	 * 页面内容分割函数
	 *
	 * @param array $contentList
	 *        	内容列表
	 * @return array $result 结果列表+page对象
	 */
	public static function webPageBreak($contentList) {
		$resultList = array ();
		
		$count = count ( $contentList );
		$pages = new CPagination ( $count );
		$pages->pageSize = Yii::app ()->params ['webPageCount'];
		$count = 0;
		foreach ( $contentList as $one ) {
			$count ++;
			if ($count < ($pages->currentPage * $pages->pageSize + 1)) {
				continue;
			} else {
				if ($count > ($pages->currentPage + 1) * $pages->pageSize) {
					break;
				} else {
					$resultList [] = $one;
				}
			}
		}
		
		return array (
				$resultList,
				$pages 
		);
	}
	
	/**
	 * 删除文件夹，包括子文件
	 *
	 * @param unknown $dir        	
	 * @return boolean
	 */
	public static function deleteAllFile($dir) {
		if (strtoupper ( substr ( PHP_OS, 0, 3 ) ) == 'WIN') {
			$str = "rmdir /s/q " . $dir;
		} else {
			$str = "rm -Rf " . $dir;
		}
		exec ( $str );
	}
	
	/**
	 * 解析html&获取图像原地址
	 *
	 * @param string $content
	 *        	html内容
	 * @return array $result html内容+封面图
	 */
	public static function getHtmlImage($content, $type) {
		$contentHtml = new simple_html_dom ( $content );
		$frontImage = array (); // 封面图
		$pathArray = array ();
		$image = array (); // 图像新数据结构
		$imagePath = array ();
		$uploadPath = ""; // 图像原上传路径
		$imageName = array ();
		$imageString = array ();
		$imageExt = ""; // 图像扩展名
		
		foreach ( $contentHtml->find ( "img" ) as $item ) {
			$pathArray = explode ( "/", $item->src, - 1 );
			if (count ( $pathArray ) > 2) {
				array_shift ( $pathArray ); // 删除空格
				array_shift ( $pathArray ); // 删除yingdongli
			} else {
				continue;
			}
			$uploadPath = implode ( "/", $pathArray );
			$imageName = explode ( "/", $item->src );
			if (count ( $imageName ) > 1) {
				$uploadPath = $uploadPath . "/" . $imageName [count ( $imageName ) - 1];
			} else {
				continue;
			}
			
			$imageString = explode ( ".", $imageName [count ( $imageName ) - 1] );
			if (count ( $imageString ) >= 2) {
				$imageExt = $imageString [1];
			} else {
				continue;
			}
			// 获取新image数据
			$image = UploadUtil::uploadMutiFiles ( $_SESSION ['_admini'] ['userId'], array (
					array (
							'fileId' => 0,
							'file' => $uploadPath,
							'type' => $imageExt 
					) 
			), $type );
			// 保存新路径
			$imagePath [] = Yii::app ()->params ['downloadPath'] . $image [0] ['imageDir'] . "640" . $image [0] ['ext'];
			// 封面图像
			if (count ( $frontImage ) == 0) {
				$frontImage = json_encode ( $image [0] );
			}
			// 删除原数据
			unlink ( implode ( "/", explode ( "/", Yii::app ()->params ['uploadPath'], - 3 ) ) . "/" . $uploadPath );
		}
		
		// 更新路径
		$list = $contentHtml->find ( "img" );
		for($i = 0; $i < count ( $list ); $i ++) {
			$list [$i]->src = $imagePath [$i];
		}
		
		$doc = $contentHtml;
		$contentHtml = null;
		
		return array (
				$doc,
				$frontImage 
		);
	}
	
	/**
	 * 解析html&删除图像文件夹
	 *
	 * @param string $content
	 *        	html内容
	 * @return int $delCount 删除数量
	 */
	public static function deleteHtmlImage($content) {
		$contentHtml = new simple_html_dom ( $content );
		
		$delCount = 0;
		foreach ( $contentHtml->find ( "img" ) as $item ) {
			$pathArray = explode ( "/", $item->src );
			if (count ( $pathArray ) > 2) {
				$uploadDir = $pathArray [count ( $pathArray ) - 2]; // 图像目录
			} else {
				continue;
			}
			
			// 删除图像数据文件
			WebEditUtils::deleteAllFile ( Yii::app ()->params ['uploadPath'] . $uploadDir );
			$delCount = $delCount + 1;
		}
		
		return $delCount;
	}
	
	/**
	 * 解析html&获取图像路径数组
	 *
	 * @param string $content
	 *        	html内容
	 * @return array $imageArray 图像路径数量
	 */
	public static function getImageArrayFromHtml($content) {
		$contentHtml = new simple_html_dom ( $content );
		$imageArray = array ();
		
		foreach ( $contentHtml->find ( "img" ) as $item ) {
			$imageArray [] = $item->src;
		}
		
		return $imageArray;
	}
	
	/**
	 * 更新图像文件，删除旧文件，添加新文件
	 *
	 * @param array $oldContent
	 *        	旧Html
	 * @param array $newContent
	 *        	新Html
	 * @return array $result 新html内容+新封面图
	 */
	public static function updateHtmlImage($oldContent, $newContent, $type) {
		$oldArray = WebEditUtils::getImageArrayFromHtml ( $oldContent );
		$newArray = WebEditUtils::getImageArrayFromHtml ( $newContent );
		
		// 变量声明
		$frontImage = array (); // 封面图
		$pathArray = array ();
		$image = array (); // 图像新数据结构
		$uploadPath = ""; // 图像原上传路径
		$imageName = array ();
		$imageExt = ""; // 图像扩展名
		                
		// 旧数组与新数组的差集->需要删除的
		$delArray = array_diff ( $oldArray, $newArray );
		foreach ( $delArray as $item ) {
			$pathArray = explode ( "/", $item );
			if (count ( $pathArray ) > 2) {
				$uploadDir = $pathArray [count ( $pathArray ) - 2]; // 图像目录
			} else {
				continue;
			}
			
			// 删除图像数据文件
			WebEditUtils::deleteAllFile ( Yii::app ()->params ['uploadPath'] . $uploadDir );
		}
		
		// 新数组与旧数组的差集->需要添加的
		$addArray = array_diff ( $newArray, $oldArray );
		$imagePath = array ();
		foreach ( $addArray as $item ) {
			$pathArray = explode ( "/", $item, - 1 );
			if (count ( $pathArray ) > 2) {
				array_shift ( $pathArray ); // 删除空格
				array_shift ( $pathArray ); // 删除yingdongli
			} else {
				continue;
			}
			$uploadPath = implode ( "/", $pathArray );
			$imageName = explode ( "/", $item );
			if (count ( $imageName ) > 1) {
				$uploadPath = $uploadPath . "/" . $imageName [count ( $imageName ) - 1];
			} else {
				continue;
			}
			$imageString = explode ( ".", $imageName [count ( $imageName ) - 1] );
			if (count ( $imageString ) >= 2) {
				$imageExt = $imageString [1];
			} else {
				continue;
			}
			// 获取新image数据
			$image = UploadUtil::uploadMutiFiles ( $_SESSION ['_admini'] ['userId'], array (
					array (
							'fileId' => 0,
							'file' => $uploadPath,
							'type' => $imageExt 
					) 
			), $type );
			// 更新内容
			$imagePath [] = Yii::app ()->params ['downloadPath'] . $image [0] ['imageDir'] . "640" . $image [0] ['ext'];
			
			// 删除原数据
			unlink ( implode ( "/", explode ( "/", Yii::app ()->params ['uploadPath'], - 3 ) ) . "/" . $uploadPath );
		}
		
		// 新图像添加到html中
		$contentHtml = new simple_html_dom ( $newContent );
		$count = 0;
		$list = $contentHtml->find ( "img" );
		for($i = 0; $i < count ( $list ); $i ++) {
			$pathArray = explode ( "/", $list [$i]->src );
			if (strlen ( $pathArray [count ( $pathArray ) - 1] ) > 10) {
				// 新图
				$list [$i]->src = $imagePath [$count];
				$count = $count + 1;
			} else {
				// 已存在
			}
			if (count ( $frontImage ) == 0) {
				$pathArray = explode ( "/", $list [$i]->src );
				$uploadDir = $pathArray [count ( $pathArray ) - 2]; // 图像目录
				$image = ImageFileInfo::getImageInfoByPath ( Yii::app ()->params ['uploadPath'] . $uploadDir . "/" . $pathArray [count ( $pathArray ) - 1] );
				$frontImage = json_encode ( $image );
			}
		}
		$doc = $contentHtml;
		$contentHtml = null;
		
		return array (
				$doc,
				$frontImage 
		);
	}
	
	/**
	 * 生成html页面
	 *
	 * @param string $content
	 *        	网页主干内容
	 * @param string $title
	 *        	网页标题
	 * @param int $userId
	 *        	用户id
	 * @return string $htmlPage 完整的网页代码
	 */
	public static function makeHtmlPage($content, $title = "", $userId = 0) {
		$htmlPage = "";
		$htmlHead = "<!DOCTYPE HTML><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>";
		$htmlFoot = "</body></html>";
		
		$htmlPage = $htmlPage . $htmlHead;
		if (strlen ( $title ) > 0) {
			$htmlPage = $htmlPage . "<h3>" . $title . "</h3>";
		}
		if ($userId > 0) {
			// 用户头像+名称
		}
		$htmlPage = $htmlPage . $content . $htmlFoot;
		
		return $htmlPage;
	}
	
	/**
	 * 根据编号获取帖子类型名称
	 *
	 * @param int $id
	 *        	类型
	 * @return string $name 类型名称
	 */
	public static function getTypeById($id) {
		$name = "";
		switch ($id) {
			case 1 :
				$name = "正经事";
				break;
			case 2 :
				$name = "随便聊";
				break;
			default :
				$name = "类型错误";
				break;
		}
		
		return $name;
	}
	/**
	 * 复制html内的image
	 *
	 * @param unknown $htmlContent        	
	 * @param unknown $itemId        	
	 * @return multitype:Ambigous <multitype:, string> \Common\Utils\simple_html_dom
	 */
	public static function copyHtmlImg($content, $itemId) {
		if(empty($content)){
			return "";
		}
		$contentHtml = new simple_html_dom ( $content );
		$frontImage = array (); // 封面图
		$pathArray = array ();
		$image = array (); // 图像新数据结构
		$imagePath = array ();
		$uploadPath = ""; // 图像原上传路径
		$imageName = array ();
		$imageString = array ();
		$imageExt = ""; // 图像扩展名
		foreach ( $contentHtml->find ( "img" ) as $item ) {
			$pathArray = explode ( "/", $item->src );
			// 最后一个元素修改名称
			$srcImg = C ( 'UPLOAD_PATH.UEDITOR' ) . $pathArray [count ( $pathArray ) - 2] . '/' . $pathArray [count ( $pathArray ) - 1];
			$toImg = C ( 'UPLOAD_PATH.UEDITOR' ) . $pathArray [count ( $pathArray ) - 2] . '/' . $itemId . $pathArray [count ( $pathArray ) - 1];
			$copyRet = copy ( $srcImg, $toImg );
			if (! $copyRet) {
				return false;
			}
			$relativeName = $itemId . $pathArray [count ( $pathArray ) - 1];
			$pathArray [count ( $pathArray ) - 1] = $relativeName;
			$imageName = implode ( '/', $pathArray );
			$imagePath[] = $imageName;
		}
		// 更新路径
		$list = $contentHtml->find ( "img" );
		for($i = 0; $i < count ( $list ); $i ++) {
			$contentHtml->find("img",$i)->src = $imagePath [$i];
		}
		$str = $contentHtml->save();
		return $str;
	}
	/**
	 * 解析html&删除图像文件夹
	 *
	 * @param string $content
	 *        	html内容
	 * @return int $delCount 删除数量
	 */
	public static function deleteHtmlImageList($content) {
		if(empty($content)){
			return true;
		}
		$contentHtml = new simple_html_dom ( $content );
		
		$delCount = 0;
		foreach ( $contentHtml->find ( "img" ) as $item ) {
			$pathArray = explode ( "/", $item->src );
			$uploadDir = $pathArray [count ( $pathArray ) - 1]; // 图像文件
			                                                    
			// 删除图像数据文件
			@unlink ( C ( 'UPLOAD_PATH.UEDITOR' ) . $pathArray [count ( $pathArray ) - 2] . '/' . $pathArray [count ( $pathArray ) - 1] );
		}
	}
}