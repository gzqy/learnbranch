<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\log\driver;

/**
 * 模拟测试输出
 */
class Test
{
    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log = [])
    {
        $a = explode('.',$oldFileName);

        //判断上传的类型 如果是amr 格式*（手机播放格式 ） 需要程序转换成mp3格式
        if(false && 'amr' == $a[1]){
            $type = 'mp3';
        }else{
            $type = $a[count($a)-1];
        }
    }

}
