<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model
{
	//主库句柄
	public $_db_m = NULL;
	
	//从库句柄
	public $_db_s = NULL;
	
	//数据库配置
	public $_db_params = array();
	//是否需要mc打开缓存
	public $_mc_cache_flag = TRUE;
	//是否强制重连
	public $_re_connect = FALSE;
	
	//MC缓存时间(秒)
	public $_mc_time = 300;
	
	//当前表
	public $_table_name = '';
	
	//当前表主键
	public $_primary = 'id';

	//批量添加
	public $_lists = array(); 

	//当前表的字段类型
	public $_fields = array('int' => array(), 'string' => array(), 'time' => array(), 'unformat' => array(), 'lists_nosearch' => array());
	
	public $_unset_fields = array();

	function __construct()
	{
		parent::__construct();
		
		$this->load->model('common/modelsqlcom', 'modelsqlcom');
	}

	
	/**
	 * 数据列表获取
	 *
	 * @param array  $search         查询条件
	 * @param num    $page           页码
	 * @param num    $num            每页数量
	 * @param array  $order_by       排序条件 
	 * @retrun array
	 */
	public function lists($search = array(), $page = 1, $num = 20, $order_by = array(), $need_fields = "")
	{
		//搜索条件处理
		$search_deal = $this->modelsqlcom->_fieldsFormat($search, $this->_fields);
		empty($need_fields) && $need_fields = $this->modelsqlcom->_getSearchFields($this->_fields);
		$where_str = $this->modelsqlcom->_getSearchSql($search_deal);
		
		//其他条件处理
		if ( isset($search['where_str_others']) )
		{
			$where_str_others = self::_dealWhereStrOthers($search['where_str_others']);
			$where_str = $this->modelsqlcom->_connectStr(array($where_str, $where_str_others), " AND ");
		}
		
		//其他后续条件处理
		$order_by_str = self::_getOrderBy($order_by, $this->_fields);
		if (!empty($order_by_str)) { $order_by_str = "ORDER BY {$order_by_str}"; }

		//多获取一个，用于更多类型的分页信息判断
		if (isset($search['page_num_more']) && $search['page_num_more']==1) 
		{
			$num += 1;
		}
		 
		$limit = self::_getLimit($num, $page);

		//数据查询
		$action = $this->uri->uri_string();
		$sql = "SELECT {$need_fields} FROM `{$this->_table_name}` WHERE {$where_str} {$order_by_str} {$limit} -- {$action}";
		$query = self::_query($sql, $search_deal);

		if (!$query) 
		{ 
			return 0; 
		}

		$lists = $query->result_array();
	
		//是否需要统计总数
		$only_sel_list = false;
		if (isset($search['only_sel_list']) && $search['only_sel_list'] == 1) {
			$only_sel_list = true;
		}

		//初始化数据
		$others_data = array(
				'num' => $num,
				'cur_page' => $page,
				'total_num' => 0,
				'total_page' => 0,
		);

		if( !$only_sel_list ) 
		{
			//数据总数
			$sql = "SELECT COUNT(1) AS `total_num` FROM `{$this->_table_name}` WHERE {$where_str}";
			$query = self::_query($sql, $search_deal);
			if (!$query) { return 0; }
			$row = $query->row_array();
			$total_num = $row['total_num'];
			
			$others_data = array(
					'num' => $num,
					'cur_page' => $page,
					'total_num' => $total_num,
					'total_page' => ceil($total_num / $num),
			);
		}

		$lists_all = array('others_data' => $others_data,'lists' => $lists);
		
		return $lists_all;
	}
	
	/**
	 * 数据列表获取
	 *
	 * @param array  $search         查询条件
	 * @param num    $page           页码
	 * @param num    $num            每页数量
	 * @param array  $order_by       排序条件
	 * @retrun array
	 */
	public function pLists($search = array(), $page = 1, $num = 20, $order_by = array(), $need_fields = "")
	{
		return self::lists($search, $page, $num, $order_by, $need_fields);
	}
	/**
	 * 获取符合条件的总条数
	 * @param  [type] $search [description]
	 * @return [type]         [description]
	 */
	public function getCount($search, $need_fields = "")
	{
		//搜索条件处理
		$search_deal = $this->modelsqlcom->_fieldsFormat($search, $this->_fields);
		empty($need_fields) && $need_fields = $this->modelsqlcom->_getSearchFields($this->_fields);
		$where_str = $this->modelsqlcom->_getSearchSql($search_deal);
		
		//其他条件处理
		if ( isset($search['where_str_others']) )
		{
			$where_str_others = self::_dealWhereStrOthers($search['where_str_others']);
			$where_str = $this->modelsqlcom->_connectStr(array($where_str, $where_str_others), " AND ");
		}

		//数据总数
		$sql = "SELECT COUNT(*) AS `total_num` FROM `{$this->_table_name}` WHERE {$where_str}";
		$query = self::_query($sql, $search_deal);
		if (!$query) { return 0; }
		$row = $query->row_array();
		$total_num = isset($row['total_num'])? $row['total_num'] : 0;
		
		return $total_num;
	}
	/**
	 * 返回一条信息
	 * 
	 * @param array   $search          搜索条件
	 * @param string  $db_select       数据库连接类型
	 * @retrun array
	 */
	public function one($search = array(), $need_fields = '', $db_select = 'slave')
	{
		$search_deal = $this->modelsqlcom->_fieldsFormat($search, $this->_fields);
		$where_str = $this->modelsqlcom->_getSearchSql($search_deal);
		if(empty($need_fields))
		{
			$need_fields = '*' ;
		}

		//其他条件处理
		if( isset($search['where_str_others']) )
		{
			$where_str_others = self::_dealWhereStrOthers($search['where_str_others']);
			$where_str = $this->modelsqlcom->_connectStr(array($where_str, $where_str_others), " AND ");
		}
		
		if (!empty($where_str))
		{
			$where_str = " WHERE {$where_str}";
		}
		
		$order_by = "ORDER BY `{$this->_primary}` DESC";
		
		$action = $this->uri->uri_string();
		$sql = "SELECT {$need_fields} FROM `{$this->_table_name}` {$where_str} {$order_by} LIMIT 1 -- {$action}";
		$query = self::_query($sql, $search_deal, $db_select);
		if (!$query) { return 0; }
		$one = $query->row_array();

		return $one;
	}
	
	/**
	 * 插入数据
	 * 
	 * @param array   $data         数据
	 * @retrun num
	 */
	public function insert($data = array())
	{
		$data_deal = $this->modelsqlcom->_fieldsFormat($data, $this->_fields);
		$action = $this->uri->uri_string();
		$_db = self::_getDbHandle("master");
		$sql = $_db->insert_string($this->_table_name, $data_deal);
		$query = self::_query($sql ."-- {$action}", array(), "master");
		if (!$query) { return 0; }
		$insert_id = $_db->insert_id();
		
		return $insert_id;
	}
	
	/**
	 * 记录不存在则插入数据，存在则更新数据
	 * 
	 * @param array   $data         数据
	 * @retrun num
	 */
	public function insertOnDuplicate($data = array())
	{
		$data_deal = $this->modelsqlcom->_fieldsFormat($data, $this->_fields);
		$action = $this->uri->uri_string();
		$_db = self::_getDbHandle("master");
		$sql = $_db->insert_string($this->_table_name, $data_deal);
		
		//更新字段合成
		foreach ($this->_unset_fields as $fields)
		{
			unset($data_deal[$fields]);
		}
		$set_str = $this->modelsqlcom->_getUpdateSql($data_deal);
		$set_str = $_db->compile_binds($set_str, $data_deal);
		$sql .= " ON DUPLICATE KEY UPDATE {$set_str}";
		
		$query = self::_query($sql."-- {$action}", array(), "master");
		if (!$query) { return 0; }
		$insert_id = $_db->insert_id();
		
		return $insert_id;
	}
	
	/**
	 * 修改数据
	 * 
	 * @param num     $id          ID
	 * @param array   $data        数据
	 * @retrun num
	 */
	public function update($id = 0, $data = array())
	{
		$data_deal = $this->modelsqlcom->_fieldsFormat($data, $this->_fields);
		$set_str = $this->modelsqlcom->_getUpdateSql($data_deal);
		$action = $this->uri->uri_string();
		
		if (empty($set_str)) { return 1; }

		$where_str = "`{$this->_primary}` = ?";
		$where_arr[] = $id;
		
		if( $this->_role == 'user' && !in_array($this->_table_name, array('smdc_user', 'smdc_system')) )
		{
			$where_str .= ' AND user_id = '. $this->_user_id;
		}

		$sql = "UPDATE `{$this->_table_name}` SET {$set_str} WHERE {$where_str} -- {$action}";
		$where_arr = array_merge(array_values($data_deal), $where_arr);
		$query = self::_query($sql, $where_arr, "master");
		if (!$query) { return 0; }
		
		return $query;
	}

	/**
	 * 是否开启缓存
	 *
	 * @retrun bool
	 */
	public function _ClearMC()
	{
		$sel_db = false;	//是否需要直接从数据库查询; true,直接查库；false:如果有缓存将查询缓存
		 
		if (isset($_REQUEST['__flush_cache']) && $_REQUEST['__flush_cache'] == 1) 
		{
			
			$sel_db = true;
		}
		
		if (!$this->_mc_cache_flag) {
				
			$sel_db = true;
		}
		
		Common::debug($sel_db, 'SELECT_DB');
		
		return $sel_db;
		
	}
	
	/**
	 * 修改数据
	 * 
	 * @param array     $condition     修改条件
	 * @param array     $data          数据
	 * @retrun num
	 */
	public function updateByCondition($condition = array(), $data = array())
	{
		$data_deal = $this->modelsqlcom->_fieldsFormat($data, $this->_fields);
		$set_str = $this->modelsqlcom->_getUpdateSql($data_deal);
		
		$condition_deal = $this->modelsqlcom->_fieldsFormat($condition, $this->_fields);
		if (empty($condition_deal)) { return 0; }
		
		$where_str = $this->modelsqlcom->_getSearchSql($condition_deal);
		$where_arr = array_values($condition_deal);
		
		//其他条件处理
		if (isset($condition['where_str_others']))
		{
			$where_str_others = self::_dealWhereStrOthers($condition['where_str_others']);
			$where_str = $this->modelsqlcom->_connectStr(array($where_str, $where_str_others), " AND ");
		}

		$action = $this->uri->uri_string();
		$sql = "UPDATE `{$this->_table_name}` SET {$set_str} WHERE {$where_str} -- {$action}";
		$where_arr = array_merge(array_values($data_deal), $where_arr);
		$query = self::_query($sql, $where_arr, "master");
		if (!$query) { return 0; }
		
		return $query;
	}
	
	/**
	 * 返回一条特定字段的信息
	 * 
	 * @param array   $search          搜索条件
	 * @param string  $need_str        需要的保留的字段
	 * @param string  $db_select       数据库连接类型
	 * @retrun array
	 */
	public function getOneFields($search = array(), $need_str = '*', $db_select = 'slave')
	{
		$search_deal = $this->modelsqlcom->_fieldsFormat($search, $this->_fields);
		$where_str = $this->modelsqlcom->_getSearchSql($search_deal);
		if (!empty($where_str))
		{
			$where_str = " WHERE {$where_str}";
		}
		
		$order_by = "ORDER BY `{$this->_primary}` DESC";
		
		$action = $this->uri->uri_string();
		$sql = "SELECT {$need_str} FROM `{$this->_table_name}` {$where_str} {$order_by} LIMIT 1 -- {$action}";
		$query = self::_query($sql, $search_deal, $db_select);
		if (!$query) { return 0; }
		$one = $query->row_array();
		
		return $one;
	}
	
	/**
	 * 返回ID
	 * 
	 * @param array   $search          搜索条件
	 * @param string  $db_select       数据库连接类型
	 * @retrun array
	 */
	public function getOneID($search = array(), $db_select = 'slave')
	{
		$search_deal = $this->modelsqlcom->_fieldsFormat($search, $this->_fields);
		$where_str = $this->modelsqlcom->_getSearchSql($search_deal);
		
		//其他条件处理
		if ( isset($search['where_str_others']) )
		{
			$where_str_others = self::_dealWhereStrOthers($search['where_str_others']);
			$where_str = $this->modelsqlcom->_connectStr(array($where_str, $where_str_others), " AND ");
		}
		
		if (!empty($where_str))
		{
			$where_str = " WHERE {$where_str}";
		}
		
		$action = $this->uri->uri_string();
		$sql = "SELECT `{$this->_primary}` FROM `{$this->_table_name}` {$where_str} LIMIT 1 -- {$action}";
		$query = self::_query($sql, $search_deal, $db_select);
		
		if (!$query) { return 0; }
	
		$one_arr = $query->row_array();
		if (!isset($one_arr[$this->_primary])) { return -1; }
	
		$oneID = $one_arr[$this->_primary];
		
		return $oneID;
	}
	
	/**
	 * 逻辑删除数据
	 *
	 * @param num     $id          ID
	 * @param array   $data        数据
	 * @retrun array
	 */
	public function logicDelete($id = 0, &$data = array())
	{
		$data = array_merge($data, array("is_delete" => 1));
		return $this->update($id, $data);
	}
	
	/**
	 * 物理删除数据
	 *
	 * @param num     $id          ID
	 * @param array   $data        数据
	 * @retrun array
	 */
	public function delete($id = 0, $data = array())
	{
		$where_str = "`{$this->_primary}` = ?";
		$where_arr[] = $id;

		if( $this->_role == 'user' && !in_array($this->_table_name, array('smdc_user', 'smdc_system')) )
		{
			$where_str .= ' AND user_id = '. $this->_user_id;
		}

		$action = $this->uri->uri_string();
		$sql = "DELETE FROM `{$this->_table_name}` WHERE {$where_str} -- {$action}";
		$query = self::_query($sql, $where_arr, "master");
		if (!$query) { return 0; }
		
		return $query;
	}
	
	/**
	 * 数据库统一连接入口
	 * @param string  $db_select   数据库主从选择
	 * @retrun object
	 */
	public function dbConnect($db_select = 'slave')
	{
		//确认数据库选取类型
		if (isset($_REQUEST['db_select']) && $_REQUEST['db_select'] == 'master')
		{
			$db_select = "master";
			Common::debug("slave to master", 'group_name_1');
		}
		
		$_db = NULL;
		if ($db_select == "master")
		{
			if( $this->_db_m === NULL)
			{
				$this->_db_m = Init::getDbConnect('master', TRUE, $this->_db_params, $this->_re_connect);
			}
			
			$_db = $this->_db_m;
		}
		else
		{
			if($this->_db_s === NULL)
			{
				$this->_db_s = Init::getDbConnect('slave', TRUE, $this->_db_params, $this->_re_connect);
			}
			
			$_db = $this->_db_s;
		}
		
		return $_db;
	}
	
	/**
	 * sql语句统一执行入口
	 * 便于firedebug的输出
	 * 
	 * @param array  $sql         sql语句，问号替换形式
	 * @param array  $arr         问号对应的值
	 * @param array  $db_select   数据库选择类型(主从)
	 * @retrun array
	 */
	public function _query($sql = '', $arr = array(), $db_select = 'slave')
	{
		$_db = self::dbConnect($db_select);
		if (!empty($arr))
		{
			$sql = $_db->compile_binds($sql, $arr);
		}

		$query = $_db->query($sql);

		//firedebug 输出
		$data = is_bool($query) ? intval($query) : $query->result_array();
		$query_times = self::_getQueryTime($_db);
		Common::debug($sql, 'db_sql_master');
		Common::debug($db_select, 'db_select');
		Common::debug(array('data' => $data, 'db_query_times' => $query_times), 'db_result');
		
		return $query;
	}
	
	/**
	 * 拼接LIMIT
	 * @param num  $num  每页获取数据条数
	 * @param num  $page 页码
	 * @retrun string
	 */
	function _getLimit(& $num, & $page)
	{
		$num    = max(0, intval($num));
		$page   = max(1, intval($page));
		$offset = $num * ($page - 1);
		$limit  = "LIMIT {$offset}, {$num}";
	
		return $limit;
	}
	
	/**
	 * 拼接ORDER_BY
	 * @param array   $order_by         排序数组
	 * @param array   $fields           当前表字段
	 * @param string  $table_alias      当前表别名
	 * @retrun string
	 */
	function _getOrderBy(& $order_by = array(), $fields = array(), $table_alias = '')
	{
		if (empty($order_by) || empty($fields))
		{
			return '';
		}

		$order_by = explode(',', $order_by);
		
		$all_fields = array_merge($fields['int'], $fields['string'], $fields['time'], $fields['unformat']);
		$order_by_deal = array();

		foreach ($order_by as $k=>$item)
		{
			if (strpos($item, " ") === FALSE) { continue; }
			list($tmp_fields, $tmp_order) = explode(" ", $item);
			if (!in_array($tmp_fields, $all_fields)) { continue; }
			//搜索指定某个表里面的对应的字段，需要去相应的model里面添加
			if(strpos($tmp_fields, "::") !== FALSE)
			{
				list($table_name, $tmp_fields) = explode("::", $tmp_fields);
			}
			$tmp_order = (strtolower($tmp_order) == "desc") ? "DESC" : "ASC";
			$order_by_deal[] = !empty($table_alias) ? "{$table_alias}.`$tmp_fields` {$tmp_order}" : "`$tmp_fields` {$tmp_order}";
			unset($order_by[$k]);
		}
		
		return implode($order_by_deal, ",");
	}
	
	/**
	 * 获取数据库连接句柄
	 * 
	 * @param array  $db_select   数据库选择类型(主从)
	 * @retrun array
	 */
	public function _getDbHandle($db_select = 'auto')
	{
		$_db = NULL;
		switch($db_select)
		{
			case "master":
				$_db = self::dbConnect("master");
				break;
			case "slave":
				$_db = self::dbConnect("slave");
				break;
			default:
				if ($this->_db_s !== NULL)
				{
					$_db = $this->_db_s;
				} 
				elseif ($this->_db_m !== NULL)
				{
					$_db = $this->_db_m;
				} 
				else 
				{
					$_db = self::dbConnect("slave");
				}
		}
		
		return $_db;
	}
	
	/**
	 * 处理特殊的查询条件
	 * 
	 * @param array  $where_str_others   特殊的查询条件
	 * @retrun str
	 */
	public function _dealWhereStrOthers($where_str_others)
	{
		if (!isset($where_str_others['condition']) || !isset($where_str_others['val_arr']))
		{
			return '';
		}
		
		$_db = self::_getDbHandle();
		$where_str_others_arr = explode("?", $where_str_others['condition']);
		$i = 0;
		$tmp_str = $where_str_others_arr[$i];
		foreach ($where_str_others['val_arr'] as $k=>$item)
		{
			$i++;
			$item = (strpos($k, "%") === 0) ? $_db->escape_like_str($item) : $_db->escape($item);
			$tmp_str .= $item;
			if (isset($where_str_others_arr[$i]))
			{
				$tmp_str .= $where_str_others_arr[$i];
			}
		}
		
		return $tmp_str;
	}
	
	/**
	 * 获取sql执行的时间(ms)
	 * 并将清除CI底层缓存的sql语句及其执行时间
	 *
	 * @param array  $db_handle   数据库选择类型(主从)
	 * @retrun array
	 */
	public function _getQueryTime($db_handle)
	{
		$query_times = $db_handle->query_times; //获取sql执行时间
		$db_handle->query_times = array(); //重置sql执行时间
		$db_handle->queries = array(); //重置sql
		$times = isset($query_times[0]) ? $query_times[0]*1000 : 0;
		$times = intval($times * 100) / 100 . "ms";
		
		return $times;
	}
	/**
	 * 获取IN数据
	 * @param array   $search       搜索的所有数据， 如：查询作品信息; $search = array(123, 345, 12881);
	 * @param string  $field   		搜索的字段;  $field = 'book_id';
	 * @param string  $fields_str   需要返回的数据; $fields_str = 'book_title,book_intro'
	 * @retrun array
	 */
	function inlist(array $search, $field, $fields_str)
	{
		if (empty($search)) {
			return array();
		}
		
		
		$action = $this->uri->uri_string();
		$select = implode(',', $this->_select($fields_str));	//字段过滤
	
		//分组
		$num = 50;	//每次最多查询50个
				
		//默认问号数量
		$def_char = '';
		$def_char = str_pad($def_char, ($num * 2 - 1), "?,");
				
		$tmp_arr = array_chunk($search, $num);
		$max_index = $num - 1;	//每个小数组最大的索引
		$lists = array();
				
		foreach ($tmp_arr as $k=>$v) 
		{
			if (!isset($v[$max_index])) 
			{
				$w = '';
				$num = count($v) * 2 - 1;
				$w = str_pad($w, $num, "?,");
				$sql = "SELECT {$select},`{$field}` FROM `{$this->_table_name}` as {$this->_table_byname} WHERE 5=5 AND `{$field}` IN($w) -- {$action}";
				
			} 
			else 
			{
				$sql = "SELECT {$select},`{$field}` FROM `{$this->_table_name}` as {$this->_table_byname} WHERE 5=5 AND `{$field}` IN($def_char) -- {$action}";
			}
	
			Common::debug($sql, 'db_sql_master');
			Common::debug($v, 'db_sql_arr');
			
			$query = $this->_query($sql, $v);
			
			$query_times = $this->_getQueryTime($this->_db_s);
			
			Common::debug(array('db_query_times'=>$query_times), 'l_sql');
			
			$rd = $query->result_array(); 
	 
			if (!empty($rd)) {
				$lists = $lists + Common::arrChangeKey($rd, $field);
			}
		}
			
		return $lists;
	}
	/**
	 * 返回需要获取的字段信息
	 */
	function _select($fields_str, $join_model_info = array())
	{
		$select = array();
		
		array_unshift($join_model_info, '');
		
		$s_arr = array();

		if (!empty($fields_str)){
			$s_arr = explode(',', $fields_str);
		}
		
		foreach ($join_model_info as $key=>$value) {
		
			if(!empty($s_arr)) {
				
				if (!empty($value)) {
					$all_fields = $this->$value->_getAllFields();
					foreach ($s_arr as $v) {
						if(in_array($v, $all_fields)) {
							$select[] = "`{$this->$value->_table_byname}`.`{$v}`";
						}
					}
				}else{
					$all_fields = $this->_getAllFields();
					foreach ($s_arr as $v) {
						if(in_array($v, $all_fields)) {
							$select[] = "`{$this->_table_byname}`.`{$v}`";
						}
					}
				}
			}else{
				if (!empty($value)) {
					$select[] = "`{$this->$value->_table_byname}`.*";
				}else{
					$select[] = "`{$this->_table_byname}`.*";
				}
			}
			
			if(empty($select)) {
				if (!empty($value)) {
					$select[] = "`{$this->$value->_table_byname}`.*";
				}else{
					$select[] = "`{$this->_table_byname}`.*";
				}
			}
			
		}
		
		return $select;
	}

	/**
	 * 获取所有字段
	 * @return array:
	 */
	function _getAllFields()
	{
		return array_merge($this->_fields['int'], $this->_fields['string'], $this->_fields['time']);
	}

	
    function addRow($dataRow)
    {
        $this->_lists[] = $dataRow;

        if (count($this->_lists) >= 100) 
        {
            $this->_batch($this->_table_name);    //批量写入
        }
        
        return true;
    }

	//批量写入
    function batch()
    {
        if ( empty($this->_lists) ) 
        {
            return true;
        }
		
		$this->_db_m = self::dbConnect('master');

        $n = count($this->_lists);
        $this->_db_m->insert_batch($this->_table_name, $this->_lists);
        $r = $this->_db_m->affected_rows();
      
        $this->_lists = array();   //清空数据

        $this->_db_m->queries = array ();
        $this->_db_m->query_times = array ();
    }
}


