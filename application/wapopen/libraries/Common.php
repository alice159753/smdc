<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Common 
{
    
    /**
     * 记录基本的调试信息
     */
    static private $debugInfo = array(
        'title' => array('type', 'count', 'time'),
        'mc' => array('mc', 'count' => 0, 'time' => 0),
        'db' => array('db', 'count' => 0, 'time' => 0),
        'request' => array('request', 'count' => 0, 'time' => 0),
    );


    /*
     * ------------------------------------------------------------
     * @function        debug          		    调试信息打印
     * @param           mixed $value             需要打印的调试信息
     * @param           string $type             需要打印的调试信息的类型，默认为：DEBUG
     * @param           string $encoding         指定传入编码
     * @return void
     * ------------------------------------------------------------
     */
    static public function debug($value, $type = 'DEBUG', $encoding = 'UTF-8') 
    {
        if (defined('FIREPHP_DEBUG') && FIREPHP_DEBUG)
        {
            //调试时正则匹配需要输出的内容
            $debugTypeFilter = isset($_GET[DEBUG_ARG_NAME]) ? $_GET[DEBUG_ARG_NAME] : '';
            $debugArgs = array_filter(explode(';', $debugTypeFilter));
            if (empty($debugArgs))
            {
                $output = true;
            } 
            else
            {
                foreach ($debugArgs as $arg) 
                {
                    $output = ($arg{0} === '!') && $arg = ltrim($arg, '!');
                    if (preg_match("/^{$arg}/", $type))
                    {
                        $output = !$output;
                        break;
                    }
                }
            }

            // mc 信息统计
            if (strpos($type, 'mc_') === 0)
            {
                $type === 'mc_connect' || self::$debugInfo['mc']['count']++;

                //数据太太
                if (empty($debugArgs) && is_array($value) && !isset($_REQUEST['__firebug_show']))
                {
                    $value = "array…………";
                }
            }
            // db 信息统计
            else if (strpos($type, 'db_') === 0)
            {
                in_array($type, array('db_sql', 'db_sql_master'), true) && self::$debugInfo['db']['count']++;
                if ($type === 'db_result' && isset($value['db_query_times']))
                {
                    self::$debugInfo['db']['time'] += $value['db_query_times'];
                }

                //数据太太
                if (empty($debugArgs) && is_array($value) && !isset($_REQUEST['__firebug_show']))
                {
                    $value['data'] = "array…………";
                }
            }
            // request 信息统计
            else if (strpos($type, 'request_') === 0)
            {
                in_array($type, array('request_url'), true) && self::$debugInfo['request']['count']++;
                $type === 'request_return' && self::$debugInfo['request']['time'] += $value[1][0];
            }

            if ($output)
            {
                if ( isset($_SERVER['HTTP_USER_AGENT']) && 
                    strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') !== false)
                {
                    if ($type === 'db_sql_master' || $type === 'sql')
                    {
                        FirePHP::getInstance(true)->warn($value, 'db_sql');
                    } elseif (in_array($type, array('db_sql_result', 'request_return', 'all_info', 'dagger_error_trace'), true))
                    {
                        FirePHP::getInstance(true)->table($type, $value);
                    } elseif (substr($type, -5) === 'trace')
                    {
                        FirePHP::getInstance(true)->trace($value);
                    } elseif (substr($type, -5) === 'error')
                    {
                        FirePHP::getInstance(true)->error($value);
                    } elseif (substr($type, -4) === 'info')
                    {
                        FirePHP::getInstance(true)->info($value);
                    } elseif (substr($type, -4) === 'warn')
                    {
                        FirePHP::getInstance(true)->warn($value);
                    } else
                    {
                        FirePHP::getInstance(true)->log($value, $type);
                    }
                } else
                {
                    // 预留debug信息输出
                }
            }
        }
    }


    /*
     * ------------------------------------------------------------
     * @function        convertEncoding		    转码函数
     * @param           Mixed $data              需要转码的数组
     * @param           String $dstEncoding      输出编码
     * @param           String $srcEncoding      传入编码
     * @param           bool $toArray            是否将stdObject转为数组输出
     * @return Mixed
     * ------------------------------------------------------------
     */
    static public function convertEncoding($data, $dstEncoding, $srcEncoding, $toArray = false) 
    {
        if ($toArray && is_object($data))
        {
            $data = (array) $data;
        }

        if (!is_array($data) && !is_object($data))
        {
            $data = mb_convert_encoding($data, $dstEncoding, $srcEncoding);
        } else
        {
            if (is_array($data))
            {
                foreach ($data as $key => $value) {
                    if (is_numeric($value))
                    {
                        continue;
                    }

                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data[$key]);
                    $data[$keyDstEncoding] = $valueDstEncoding;
                }
            } else if (is_object($data))
            {
                $dataVars = get_object_vars($data);
                foreach ($dataVars as $key => $value) {
                    if (is_numeric($value))
                    {
                        continue;
                    }

                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data->$key);
                    $data->$keyDstEncoding = $valueDstEncoding;
                }
            }
        }

        return $data;
    }

    /**
     * 更改数组的key
     * @param array $arr	需要转换的数组
     * @param string $max
     * @return array
     */
    static function arrChangeKey($arr, $key)
    {
        if( empty($arr) || !is_array($arr)) 
        {
    		return $arr;
    	}
    	
    	$change = array();
    	
        foreach ($arr as $k=>$v) 
        {
            if( !isset($v[$key]) ) 
            {
    			continue;
            }
        
    		$change[$v[$key]] = $v;
    	}
    	
    	unset($arr);
    	
    	return $change;
    }

    static public function getToken($para, $secret)
    {
    	$req_uri = '';
        if (isset($_SERVER['REQUEST_URI'])) 
        {
    		$tmp = explode('?', $_SERVER['REQUEST_URI']);
    		$req_uri = trim($tmp[0], '/');
    	}
    
    	$para['api_secret'] = $secret;
    
        if ( empty($para) ) 
        {
    		return '';
    	}
    
        foreach ($para as $key=>$val) 
        {
            if($key == "api_key" || $key == "post_change_get" || $key == "__flush_cache" || $key == "api_token" || is_array($val) || $key == $req_uri) 
            {
    			unset($para[$key]);
    		}
    	}
    
    	//排序
    	ksort($para);
    	reset($para);
    
    	$arg  = "";
        foreach($para as $key=>$val) 
        {
    		$arg .= $key."=".$val."&";
    	}
    
    	//去掉最后一个&字符
    	$arg = substr($arg, 0, -1);
    
    	//去掉转义字符
        if( get_magic_quotes_gpc() )
        {
    		$arg = stripslashes($arg);
    	}
    
    	Common::debug($arg, 'str');
    
    	return md5($arg);
    }

	/**
	 * 加密数据
	 */
	static function encrypt($data, $secrect_key = '')
	{
        if (empty($secrect_key)) 
        {
			$secrect_key = '53aec778eef5a140a69717568098d91e';
        }
        
		$CI = &get_instance();

		$aes = $CI->load->library('aes');
		
		return $CI->aes->encrypt(base64_encode(json_encode($data)), $secrect_key);
	}
	
	/**
	 * 解密数据
	 */
	static function decrypt($data, $secrect_key = '')
	{
        if (empty($secrect_key)) 
        {
			$secrect_key = '53aec778eef5a140a69717568098d91e';
        }
        
		$CI = &get_instance();
		$aes = $CI->load->library('aes');
		$data = $CI->aes->decrypt($data, $secrect_key);
		
		return json_decode(base64_decode($data), true);
    }

    /**
     * 生成随机数
     * @param int $type	1:全数字；2：全字母；3：字母+数字
     * @param int $max 位数
     * @return string
     */
    static function myRandom($type, $max)
    {

    	if(!is_numeric($max) || $max < 1) {
    		return '';
    	}
    	
    	$s[1] = '0123456789';	//全数字
    	
    	$s[2] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ'; //全字母
    	
    	$s[3] = $s[1].$s[2];
    	
    	if(!isset($s[$type])) {
    		return '';
    	}
    	
    	$rand = '';
    	
    	$n = strlen($s[$type]) - 1;
    	
    	for ($i=0; $i<$max; $i++) {
    		$rand .= $s[$type]{mt_rand(0, $n)};
    	}
    	
    	return $rand;
    }
    
}


?>