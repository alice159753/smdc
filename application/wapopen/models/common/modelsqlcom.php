<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


class ModelSqlCom extends MY_Model
{
	//左侧匹配（数据库字段命名时不允许包含的字符）
	public $_spec_fields_left = "#!&<=>";
	
	//左侧匹配或右匹配（数据库字段命名时不允许包含的字符）
	public $_spec_fields_both = "%";
	
	/**
	 * 获取与当前数据库表相关的数据
	 * @param array  $data       待处理的数据
	 * @param array  $fields     数据库表对应的字段
	 * @retrun array
	 */
	function _fieldsFormat($data = array(), $fields = array())
	{
		$format_data = array();
		
		if (!is_array($data))
		{
			return $format_data;
		}

		$left = $this->_spec_fields_left;
		$both = $this->_spec_fields_both;
		$all_fields = array_merge($fields['int'], $fields['string'], $fields['time']);
		foreach ($data as $k=>$v)
		{	
			//去掉键值两侧的特殊符号
			$k_deal = trim($k, $both);
			
			//去掉键值左侧的特殊符号
			if ($k_deal == $k) 
			{
				$k_deal = ltrim($k, $left);
			}
			
			//不需要处理的字段
			if (in_array($k_deal, $fields['unformat']))
			{
				$format_data[$k] = $v;
				continue;
			}
			
			if (!in_array($k_deal, $all_fields))
			{
				continue;
			}

			//in 语句
			if ($k{0} == "&")
			{
				$format_data[$k] = $v;
				continue;
			}

			//转换为整数
			if (in_array($k_deal, $fields['int']) && is_numeric($v))
			{
				$format_data[$k] = intval($v);
				continue;
			}

			//去掉前后空格
			if (in_array($k_deal, $fields['string']))
			{
				$format_data[$k] = trim($v);
				continue;
			}

			//强制转化为时间格式
			if (in_array($k_deal, $fields['time']) && !empty($v))
			{
				$format_data[$k] = ($v != '0000-00-00 00:00:00') ? date('Y-m-d H:i:s', strtotime($v)) : $v;
				$format_data[$k] = ($format_data[$k] > '1971-01-01 08:00:00') ? $format_data[$k] : '0000-00-00 00:00:00';
				continue;
			}
		}
		
		return $format_data;
	}
	
	/**
	 * 拼接搜索条件
	 * @param array  $search        搜索数据
	 * @param string $table_alias   表别名
	 * @retrun string
	 */
	function _getSearchSql(& $search, $table_alias = '')
	{
		$str_arr = array();
	
		if (!is_array($search)) { return ' 1 = 1'; }
	
		$left = $this->_spec_fields_left;
		$both = $this->_spec_fields_both;
		foreach ($search as $k=>$v)
		{
			$fields = $k;
			if (empty($fields)) { continue; }

			//搜索指定某个表里面的对应的字段，需要去相应的model里面添加
			if(strpos($fields, "::") !== FALSE)
			{
				list($table_name, $fields) = explode("::", $fields);
			
				//处理字段的搜索条件
				$fields = preg_replace("/[^{$left}{$both}]/", "", $table_name) . $fields;
			}
			
			//优先查找左匹配或右匹配，然后查找左匹配
			preg_match("/^(?<pre>[{$both}]+)?(?<fields>[^{$both}]*)(?<suff>[{$both}]+)?$/", $fields, $match);
			$fields = $match['fields'];
			$char_tag = !empty($match['pre']) ? $match['pre'] . "_L" : '';
			if(!empty($match['suff']))
			{
				$char_tag = !empty($char_tag) ? $char_tag : $match['suff'];
				$char_tag .= "_R";
			}
			
			if(empty($char_tag))
			{
				preg_match("/^(?<pre>[{$left}]+)?(?<fields>.*)/", $fields, $match);
				$fields = $match['fields'];
				$char_tag = !empty($match['pre']) ? $match['pre'] . "_L" : '';
			}
			
			$fields = empty($table_alias) ? "`$fields`" : $table_alias . ".`$fields`";
			switch($char_tag)
			{
				case "!_L";
					$str_arr[] = "$fields <> ?";
					break;
					
				case "&_L";
					$v = self::_getDbHandle()->escape_str($v);
					$str_arr[] = "$fields IN ({$v})";
					unset($search[$k]);
					break;
				case "&!_L";
					$v = self::_getDbHandle()->escape_str($v);
					$str_arr[] = "$fields NOT IN ({$v})";
					unset($search[$k]);
					break;
					
				case "<_L";
					$str_arr[] = "$fields < ?";
					break;
					
				case ">_L";
					$str_arr[] = "$fields > ?";
					break;
					
				case "<=_L";
					$str_arr[] = "$fields <= ?";
					break;
					
				case ">=_L";
					$str_arr[] = "$fields >= ?";
					break;
					
				case "%_L";
					$v = self::_getDbHandle()->escape_like_str($v);
					$str_arr[] = "$fields LIKE ?";
					$search[$k] = "%{$v}";
					break;
					
				case "%_R";
					$v = self::_getDbHandle()->escape_like_str($v);
					$str_arr[] = "$fields LIKE ?";
					$search[$k] = "{$v}%";
					break;
					
				case "%_L_R";
					$v = self::_getDbHandle()->escape_like_str($v);
					$str_arr[] = "$fields LIKE ?";
					$search[$k] = "%{$v}%";
					break;
					
				default:
					$str_arr[] = "$fields = ?";
			}
		}
	
		return empty($str_arr) ? '1 = 1' : implode(' AND ', $str_arr);
	}
	
	/**
	 * 拼接更新数据
	 * @param array  $data 更新数据
	 * @param string $table_alias 搜索那表名
	 * @retrun string
	 */
	function _getUpdateSql($data, $table_alias = '')
	{
		if(!is_array($data))
		{
			return "";
		}
		
		$str_arr = array();
		foreach ($data as $fields=>$v)
		{
			switch($fields{0})
			{
				case "#";
					$fields = substr($fields, 1);
					$fields = empty($table_alias) ? "`$fields`" : $table_alias . ".`$fields`";
					$str_arr[] = "{$fields} = {$fields} + ?";
					break;
				default:
					$fields = empty($table_alias) ? "`$fields`" : $table_alias . ".`$fields`";
					$str_arr[] = "$fields = ?";
			}
		}
	
		return empty($str_arr) ? '' : implode(',', $str_arr);
	}
	
	/**
	 * 拼接字符串
	 * @param array  $data 需要连接的字符串数组
	 * @param string $link 连接符
	 * @retrun string
	 */
	function _connectStr($data, $link = ', ')
	{
		foreach ($data as $k=>$item)
		{
			if (empty($item) || $item == '1 = 1')  
			{
				unset($data[$k]);
			}
		}
	
		return implode($link, $data);
	}
	
	/**
	 * 拼接数据列表时可以查询的字段名称
	 * @param array  $fields        数据库表对应的字段
	 * @param string $table_alias   表别名
	 * @retrun string
	 */
	function _getSearchFields($fields, $table_alias = '')
	{
		if (empty($fields['lists_nosearch']))
		{
			return empty($table_alias) ? "*" : $table_alias . ".*";
		}
		
		$all_fields = array_merge($fields['int'], $fields['string'], $fields['time'], $fields['unformat']);
		$need_fields = array_diff($all_fields, $fields['lists_nosearch']);
		foreach ($need_fields as $k=>$item)
		{
			if (strpos($item, '::')) 
			{
				unset($need_fields[$k]);
			}
		}
		
		if (empty($table_alias))
		{
			$fields_str = implode("`,`", $need_fields);
			$fields_str = "`{$fields_str}`";
		}
		else
		{
			$fields_str =  implode("`,{$table_alias}.`", $need_fields);
			$fields_str = "{$table_alias}.`{$fields_str}`";
		}
		
		
		return $fields_str; 
	}
	
	/**
	 * 根据书名，表数获取分表符
	 * @param num   $book_id            书ID
	 * @param num   $table_num          总表数
	 * @retrun mix
	 */
	function _getBookMod($book_id = 0, $table_num = 50)
	{
		$mod = $book_id % $table_num;
		return $mod ? $mod : $table_num;
	}
	
	/**
	 * 字段过滤
	 * @param array   $data            需要过滤的信息数组
	 * @param string  $fields_str      需要保留的字段
	 * @retrun mix
	 */
	function _filterFields($data = array(), $fields_str = '')
	{
		if(empty($fields_str))
		{
			return $data;
		}
			
		$rs = array();
		$fields_str = trim($fields_str, ',');
		$fields = explode(',', $fields_str);
		foreach($fields as $item)
		{
			$item = trim($item);
			if(isset($data[$item]))
			{
				$rs[$item] = $data[$item];
			}
			else
			{
				$rs[$item] = "";
			}
		}
	
		return $rs;
	}
}