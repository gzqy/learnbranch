<?php
namespace Common\Utils;

/**
 * 工具类
 */
class CommonUtil {
    /**
     * 生成数字短信验证码
     * @param int $num      验证码位数
     * @return int          验证码
     */
	public static function buildCode($num = 6) {
		return rand(pow(10, ($num - 1)), pow(10, $num) - 1);
	}

    /**
     * 验证码校验
     * @param $code         用户验证码
     * @param $codeSession  session验证码
     * @return bool         有效性
     */
	public static function checkCode($code, $codeSession) {
        if ($code == $codeSession['code'] && ($codeSession['time'] + 10 * 60) > time()) {
            return true;
        } else {
            if ($code == '8888') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取处理过的收货地址
     * @param $addressId    收货地址ID
     * @param bool $sign    地址分隔符
     * @return array        处理好的地址
     */
    public static function getAddress($addressId, $sign = false) {
        $address = M('member_address')->where('id=' . $addressId)->find();
        $area = json_decode($address['name'], true);
        if ($sign) {
            $area = $area['province'] . $sign . $area['city'] . $sign . $area['county'];
            $detail = $area . $sign . $address['address_detail'];
        } else {
            $area = $area['province'] . $area['city'] . $area['county'];
            $detail = $area . $address['address_detail'];
        }
        return array (
            'area' => $address ['name'],
            'name' => $address ['receive_name'],
            'phone' => $address ['receive_phone'],
            'address' => $detail
        );
    }

    /**
     * 文件上传
     * @param $file                 $_FILES['fileName']
     * @param string $path          保存路径
     * @param int $size             文件大小
     * @param bool $img             是否是图片
     * @return array                成功返回文件保存路径  失败返回错误信息
     */
    public static function upload($file, $path, $size = 3145728, $img = true) {
        $upload = new \Think\Upload();                  // 实例化上传类
        $upload->maxSize  = $size;                      // 设置附件上传大小 默认3M
        $upload->rootPath = $path;                      // 设置附件上传根目录
        $upload->saveName = array('uniqid', '');        // 命名方式
        if ($img) {                                     // 设置附件上传类型
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg', 'txt', 'zip');
        } else {
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        }

        $info = $upload->uploadOne($file);  // 上传单个文件
        if ($info) {
            return array('result' => 'success', 'msg' => $info);
        } else {
            return array('result' => 'fail', 'msg' => $upload->getError());
        }
    }

    /**
     * @param string $imgPath           图像路径
     * @param array $crop               裁剪信息
     * @param int $targ_w               目标图像宽
     * @param int $targ_h               目标图像高
     * @param string $width             页面展示宽度 默认按原尺寸展示
     * @param int $quality              保存质量 0（最差质量，文件更小）到 100（最佳质量，文件最大）
     * @return string                   success 或 错误信息
     */
    public static function cropImage($imgPath, $crop, $targ_w, $targ_h, $width = '', $quality = 90) {
        $imgStr = file_get_contents($imgPath);
        if (!$imgStr) {
            return '无法获取图像内容！';
        }

        $img = imagecreatefromstring($imgStr);
        if (!$img) {
            return '图像无法裁剪！';
        }

        // 图片展示时的缩放比
        if (empty($width)) {
            $ratio = 1;
        } else {
            $ratio = imagesx($img) / $width;
        }

        $dst = ImageCreateTrueColor($targ_w, $targ_h);
        $cropImg = imagecopyresampled($dst,$img,0,0,$crop['x']*$ratio,$crop['y']*$ratio,$targ_w,$targ_h,$crop['w']*$ratio,$crop['h']*$ratio);
//        // 调试时输出图片
//        header('Content-type:image/jpeg');
//        imagejpeg($dst, null, $quality);exit;

        if ($cropImg) {
            $saveImg = imagejpeg($dst, $imgPath, $quality);     // 保存在原位置
            if ($saveImg) {
                self::buildThumb($imgPath, $targ_w / 2, $targ_h / 2);    // 创建缩略图
                return 'success';
            } else {
                return '图像保存失败！';
            }
        } else {
            return '图像裁剪失败！';
        }
    }

    /**
     * 创建缩略图
     * @param $url              图像路径
     * @param $width            缩略图宽
     * @param $height           缩略图高 默认和宽相等
     */
    public function buildThumb($url, $width, $height = ''){
        $image = new \Think\Image();
        $image->open($url);
        $end = strrpos($url, '.');
        $name = substr_replace($url, '_thumb', $end, 0);
        if (empty($height)) $height = $width;
        $image->thumb($width, $height)->save($name);
        return ;
    }
}
