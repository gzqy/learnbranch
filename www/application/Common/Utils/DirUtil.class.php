<?php

namespace Common\Utils;
class DirUtil{
	public function deldir($dir) {  
	    //先删除目录下的文件：  
	    $dh = opendir($dir);  
	    while ($file = readdir($dh)) {  
	        if($file != "." && $file!="..") {  
	        $fullpath = $dir."/".$file;  
	        if(!is_dir($fullpath)) {  
	            unlink($fullpath);  
	        } else {  
	            self::deldir($fullpath);  
	        }  
	        }  
	    }  
	    closedir($dh);  
	       
	    //删除当前文件夹：  
	    if(rmdir($dir)) {  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}
}