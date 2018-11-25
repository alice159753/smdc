<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
class KsMessage 
{
	private static $fieldsArr = array ();

	public static function showSucc($msg, $data = array(), $otherData = array()) 
	{
		$oe = isset ( $_REQUEST ['oe'] ) ? $_REQUEST ['oe'] : 'UTF-8';
		self::message ( 0, $msg, $data, $otherData, 'UTF-8', $oe );
	}

	public static function showError($msg, $data = array(), $code = 11) 
	{
		$oe = isset ( $_REQUEST ['oe'] ) ? $_REQUEST ['oe'] : 'UTF-8';
		self::message ( $code, $msg, $data, array (), 'UTF-8', $oe );  
	}
	
	/**
	 * 输出json/xml/html格式的消息。该函数参数很多，还读取$_REQUSET的format、fileds参数，很凶残呐
	 *
	 * @param int $code
	 *        	错误号， 0表示没有错误发生
	 * @param string $msg
	 *        	结果描述
	 * @param array $data
	 *        	数据，可以是一维数组，也可以是二维数组， 仅在输出json/xml数据时有用
	 * @param array $otherData
	 *        	消息的补充字段， 仅在输出json/xml数据时有用
	 * @param string $ie
	 *        	输入数据的编码，默认为gbk
	 * @param string $oe
	 *        	输出数据的编码，默认为utf8
	 */
	private static function message($code, $msg, $data, $otherData = array(), $ie = '', $oe = 'UTF-8') 
	{
		$format = empty ( $_REQUEST ['format'] ) ? 'json' : strtolower ( $_REQUEST ['format'] );
		$oe = $format === 'json' ? 'UTF-8' : $oe; // 标准的json只支持utf8中文
		$code = intval ( $code );
		// 转码
		if (! empty ( $ie ) && strcasecmp ( $ie, $oe ) !== 0) {
			$msg = Common::convertEncoding ( $msg, $oe, $ie );
			$data = Common::convertEncoding ( $data, $oe, $ie );
			$otherData = Common::convertEncoding ( $otherData, $oe, $ie );
		}
		
		// 如果传入了fields字段，返回结果只显示指定字段，fields内容使用半角逗号隔开
		// 传fileds参数 && 返回的data是数组 && data不为空 && (data的第一维存在字符串key,对该key进行筛选 ||
		// (存在数字0的键，并且0的值为数组，则对下一维数组进行逐个筛选a))
		if (! empty ( $_GET ['fields'] ) && is_array ( $data ) && ! empty ( $data ) && (! isset ( $data ['0'] ) || (! empty ( $data ['0'] ) && is_array ( $data ['0'] )))) {
			$data = self::_checkFields( $data );
		}
		
		// 依据不同格式选择性输出
		switch ($format) {
			case 'xml' :
				header ( "Content-Type: text/xml; charset=" . strtoupper ( $oe ) );
				$outArr = array ();
				if (! is_array ( $msg )) {
					$outArr ['result'] ['status'] ['code'] = $code;
					$outArr ['result'] ['status'] ['msg'] = $msg;
					if (is_array ( $otherData )) {
						foreach ( $otherData as $k => $v ) {
							if (! in_array ( $k, array (
									'status',
									'data' 
							), true )) {
								$outArr ['result'] [$k] = $v;
							}
						}
					}
					
					$outArr ['result'] ['data'] = $data;
				} else {
					$outArr = $msg;
				}
				
				
				$CI = & get_instance ();
				$xml = $CI->load->library ( 'Xml' );
				$CI->xml->setSerializerOption ( XML_SERIALIZER_OPTION_ENCODING, $oe );
			
				echo $CI->xml->encode ( $outArr );
				break;
			case 'json' :
			default :
				$outArr = array ();
				if (! is_array ( $msg )) {
					$outArr ['result'] ['status'] ['code'] = $code;
					$outArr ['result'] ['status'] ['msg'] = $msg;
					if (is_array ( $otherData )) {
						foreach ( $otherData as $k => $v ) {
							if (! in_array ( $k, array (
									'status',
									'data' 
							), true )) {
								$outArr ['result'] [$k] = $v;
							}
						}
					}
					
					$outArr ['result'] ['data'] = $data;
				} else {
					$outArr = $msg;
				}
				$json = json_encode ( $outArr );
				$callback = isset ( $_GET ['callback'] ) ? $_GET ['callback'] : '';
				if (preg_match ( "/^[a-zA-Z][a-zA-Z0-9_\.]+$/", $callback )) {
					if (isset ( $_SERVER ['REQUEST_METHOD'] ) && strtoupper ( $_SERVER ['REQUEST_METHOD'] ) === 'POST') { // POST
						header ( "Content-Type: text/html" );
						$refer = isset ( $_SERVER ['HTTP_REFERER'] ) ? parse_url ( $_SERVER ['HTTP_REFERER'] ) : array ();
						$result = '<script>document.domain="kanshu.com";';
						$result .= "parent.{$callback}({$json});</script>";
						echo $result;
					} else {
						if (isset ( $_SERVER ['HTTP_USER_AGENT'] ) && stripos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' )) {
							header ( 'Content-Type: text/javascript; charset=UTF-8' );
						} else {
							header ( 'Content-Type: application/javascript; charset=UTF-8' );
						}
						echo "{$callback}({$json});";
					}
				} elseif ($callback) {
					header ( 'Content-Type: text/html; charset=UTF-8' );
					echo 'callback参数包含非法字符！';
				} else {
					if (isset ( $_SERVER ['HTTP_USER_AGENT'] ) && stripos ( $_SERVER ['HTTP_USER_AGENT'], 'MSIE' )) {
						header ( 'Content-Type: text/plain; charset=UTF-8' );
					} else {
						header ( 'Content-Type: application/json; charset=UTF-8' );
					}
					echo $json;
				}
		}
		
		exit ();
	}
	
	/**
	 *
	 * @method errorMessage 处理错误信息
	 * @param $code 错误编码        	
	 * @param $pack 选择语言包        	
	 */
	static public function errorMessage($code, $pack = 'api') 
	{
		$error = self::selectLang ( '_' . $code, $pack);
		self::showError($error, '', $code);
	}

	static public function errorMessageFields($code, $fields , $pack = 'api') 
	{
		$error = self::selectLang('_' . $code, $pack);
		$error = '('.$fields.')'.$error;
		self::showError ( $error, '', $code );
	}

	
	/**
	 * @selectLang 选择语言包中的代码
	 *
	 * @param $error_code 错误代码        	
	 * @param $pack 选择语言包        	
	 * @version 1.0 2013-11-18
	 * @author 文明
	 *        
	 */
	static public function selectLang($error_code, $pack = 'api') 
	{
		$CI = &get_instance();
		$CI->load->helper('language');
		$CI->lang->load($pack, 'chinese');
		return $CI->lang->line($error_code);
	}

	private static function _checkFields($data) 
	{
		empty ( self::$fieldsArr ) && self::$fieldsArr = array_flip ( explode ( ',', $_GET ['fields'] ) );
		$newData = array ();
		if (! isset ( $data ['0'] )) {
			// 一维数组
			$newData = array_intersect_key ( $data, self::$fieldsArr );
		} else {
			// 多维数组
			if (isset ( $data ['0'] ['data'] ) && is_array ( $data ['0'] ['data'] ) && count ( $data ['0'] ['data'] > 0 )) {
				foreach ( $data as $kk => $vv ) {
					$data [$kk] ['data'] = array_map ( array (
							'self',
							'_checkFields' 
					), $vv ['data'] );
				}
				$newData = $data;
			} else {
				// 二维数组,要求数组里每一个一维数组的键名都是一样的
				foreach ( $data as $kk => $vv ) {
					$newData [$kk] = array_intersect_key ( $vv, self::$fieldsArr );
				}
			}
		}
		return $newData;
	}
}
