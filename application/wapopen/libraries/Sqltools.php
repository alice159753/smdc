<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sqltools {
	/**
	 * 处理选项数据
	 *
	 * @param array    $data          数据
	 * @param string   $fields        需要处理的字段
	 * @param num      $default       此字段的默认值  
	 * @param string   $set           默认获取类型
	 */
	public function dealOption(& $data = array(), $fields = '', $default = 0, $set = array())
	{
		if (isset($data[$fields]) && $data[$fields] == 'all')
		{
			unset($data[$fields]);
		}
		else 
		{
			$data[$fields] = isset($data[$fields]) ? $data[$fields] : $default;
			
			if (in_array("in", $set))
			{
				$data["&{$fields}"] = $data[$fields];
				unset($data[$fields]);
			}
		}
	}
	
	/**
	 * 处理数据，state,hide,reject,draft,delete
	 *
	 * @param array    $data          数据
	 */
	public function dealStats(& $data = array())
	{
		$tmp_arr = array(
					"state" => 0,
					"delete" => 0,
					"hide" => 0,
					"reject" => 0,
					"draft" => 0,
					"timing" => 0
				);
		
		$array_merge = FALSE;
		foreach ($tmp_arr as $k=>$v)
		{
			if (isset($data[$k]) && $data[$k] == 1)
			{
				if ($k == 'draft')
				{
					if ((isset($data['versions']) && $data['versions'] == 0) || (isset($data['last_update']['versions']) && $data['last_update']['versions'] == 0))
					{
						$array_merge = TRUE;
						$tmp_arr[$k] = 1;
						break;
					}
				} 
				else 
				{
					$array_merge = TRUE;
					$tmp_arr[$k] = 1;
					break;
				}
			}
		}
		
		if ($array_merge)
		{
			$data = array_merge($data, $tmp_arr);
		}
	}
	
	/**
	 * 处理获取的数据
	 *
	 * @param array    $data          数据
	 */
	public function dealData(& $data = array())
	{
		$this->load->model('common/modelsqlcom', 'modelsqlcom');

		$left = $this->modelsqlcom->_spec_fields_left;
		$both = $this->modelsqlcom->_spec_fields_both;
		$ori_data = $data;
		foreach ($ori_data as $k=>$v)
		{
			if (is_array($v)) { continue; }
			
			//处理IN条件
			if (!preg_match("/[^0-9,]/", $v) && strpos($v, ',') !== FALSE)
			{
				unset($data[$k]);
				$data["&{$k}"] = trim($v, ",");
			}
			
			//处理左侧匹配或右匹配
			preg_match("/(?<pre>{$both}{0,3})(?<need>.*)/", $v, $need_arr);

			$pre = $need_arr['pre'];

			if ($pre == "%")
			{
				unset($data[$k]);
				$data["%{$k}"] = $need_arr['need'];
			}

			elseif ($pre == "%%")
			{
				unset($data[$k]);
				$data["%{$k}%"] = $need_arr['need'];
			}
			elseif ($pre == "%%%")
			{
				unset($data[$k]);
				$data["{$k}%"] = $need_arr['need'];
			}
			else 
			{
				//处理左侧匹配，只处理数字与时间值类型的数据
				$only_match = "0-9:\-\s\/";
				if (preg_match("/[^{$only_match}{$left}]/", $v)) { continue; }
				
	    		preg_match("/(?<pre_1>[$left]{0,2})(?<need_1>[{$only_match}]*)(?<pre_2>[$left]{0,2})?(?<need_2>[{$only_match}]*)?/", $v, $need_arr);
	    		$pre_1 = $need_arr['pre_1'];
	    		if(!empty($pre_1))
	    		{
	    			unset($data[$k]);
	    			$pre_1 = ltrim($pre_1, "=");
	    			$data["{$pre_1}{$k}"] = $need_arr['need_1'];
	    		
		    		$pre_2 = $need_arr['pre_2'];
		    		if(!empty($pre_2))
		    		{
		    			$pre_2 = ltrim($pre_2, "=");
		    			$data["{$pre_2}{$k}"] = $need_arr['need_2'];
		    		}
	    		}
			}
		}
	}
	
	
	
	/**
	 * urldecode数据项
	 *
	 * @param array    $data          数据
	 * @param string   $fields        需要处理的字段
	 * @param boolean  $default       是否添加默认值
	 */
	public function dealDecode(& $data = array(), $fields_arr = array(), $default = FALSE)
	{
		foreach ($fields_arr as $item)
		{
			if (isset($data[$item]) && !empty($data[$item]))
			{
				$data[$item] = urldecode($data[$item]);
			}
			elseif ($default)
			{
				$data[$item] = '';
			}
		}
	}
	
	
	
	/**
	 * 粗边检测字符串处理
	 *
	 * @param array    $data          数据
	 */
	function dealCheckStr($str)
	{
		$check_arr = array(); //检测串内容ID数组
		$check_str_arr = explode(":", $str);
		foreach ($check_str_arr as $item)
		{
			if (strpos($item, "-") > 0)
			{
				list($content_id, $order) = explode("-", $item);
				$check_arr[$order] = $content_id;
			}
		}
		
		return $check_arr;
	}

	/**
	 * 设置输出头信息
	 * 
	 * @param array   $filename          输出文件名
	 */
	public function setHeader($filename)
	{
		header("Content-type: text/plain");
		header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$filename);
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header("Pragma: no-cache" );
		header("Expires: 0" );
	}
	
	function __get($key)
	{
		$CI = &get_instance();
		return $CI->$key;
	}
}