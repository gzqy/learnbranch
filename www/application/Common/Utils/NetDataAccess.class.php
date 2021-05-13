<?php
/**
 * 
 * 网络数据接收与发送的工具类
 * @author gavin
 *
 */
namespace  Common\Utils;
class NetDataAccess
{
	/**
	 * 获取网络请求数据函数，检测和过滤参数
	 * @param 无
	 * @return array $request 请求数据数组
	 */
	public static function getRequestData()
	{
		$request=array();
		foreach($_REQUEST as $key=>$value)
		{
			if($key!="r")
			{
				$request[$key]=$value;
			}
		}
		
		return $request;
	}
	
	/**
	 * 发送结果数据函数，打包成json数组
	 * @param array $content 结果数据数组
	 * @param int $result 结果标示
	 * @param string $resultDesp 结果描述
	 * @return 无
	 */
	public static function sendResultData($content, $result, $resultDesp)
	{
		$jsonObject=array();
		//结果内容
		if(count($content)>0)
		{
			$jsonObject['data']=$content;
		}else {
			$jsonObject['data']=array();
		}
		//结果标示
		if(is_int($result)==true)
		{
			$jsonObject['result']=$result;
		}else{
			$jsonObject['result']=1;
		}
		//结果描述
		if(is_string($resultDesp) && isset($resultDesp))
		{
			$jsonObject['msg']=$resultDesp;
		}else{
			$jsonObject['msg']='操作成功';
		}
		//打成json格式
		$jsonString=json_encode($jsonObject);
		
		echo $jsonString;
	}
	/**
	 * 发送结果数据函数，打包成json数组
	 *
	 * @param array $content
	 *        	结果数据数组
	 * @param int $result
	 *        	结果标示
	 * @param string $resultDesp
	 *        	结果描述
	 * @return 无
	 */
	public static function showMsg($result, $resultDesp, $content) {
		header ( 'Content-Type:text/json;charset=utf-8' );
		$jsonObject = array ();
		$jsonObject ['data'] = '';
		if (! empty ( $content ) && is_array ( $content )) {
			$jsonObject ['data'] = $content;
		}
		$jsonObject ['result'] = 1;
		if (is_int ( $result ) == true) {
			$jsonObject ['result'] = $result;
		}
		$jsonObject ['msg'] = '操作成功';
		if (is_string ( $resultDesp ) && isset ( $resultDesp )) {
			$jsonObject ['msg'] = $resultDesp;
		}
		echo json_encode ($jsonObject, JSON_UNESCAPED_UNICODE);
		exit ();
	}
	public static function showMsg1($result, $resultDesp, $content) {
		header ( 'Content-Type:text/json;charset=utf-8' );
		$jsonObject = array ();
		$jsonObject ['data'] = '';
		if (! empty ( $content ) && is_array ( $content )) {
			$jsonObject ['data'] = $content;
		}
		$jsonObject ['result'] = 1;
		if (is_int ( $result ) == true) {
			$jsonObject ['result'] = $result;
		}
		$jsonObject ['msg'] = '操作成功';
		if (is_string ( $resultDesp ) && isset ( $resultDesp )) {
			$jsonObject ['msg'] = $resultDesp;
		}
		// echo json_encode ( $jsonObject,JSON_UNESCAPED_SLASHES);
		echo str_replace('\\','',json_encode($jsonObject));
		exit ();
	}
}