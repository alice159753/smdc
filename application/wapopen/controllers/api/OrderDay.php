<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class OrderDay extends MY_Controller 
{	
	private $model = "modelorderday";
	function __construct() 
	{
		parent::__construct();

		$this->load->model('api/'.$this->model, $this->model);
	}
	
	/**
	 * 列表
	 */
	function lists()
	{
		$data = $this->req_param('GET');
		$model = $this->model;

		// 翻页处理
		$page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
		// 每页显示的条数
		$num = isset($data['num']) ? min(max(1, intval($data['num'])), 300) : 20;
		
        // 排序，默认都是创建时间的倒叙
		$order_by = isset($data['order_by']) ? $data['order_by'] : "id DESC";
		
        $this->load->library("sqltools");
        $this->sqltools->dealData($data);

        // 需要查询的字段
        $fields_str = $this->$model->deaf_fields_str;
		if(isset($data['fields_str']) && !empty($data['fields_str']))
		{
        	$fields_str = $data['fields_str'];
		}

		$search = array();
		$search['user_id'] = $this->_user_id;

		if ( isset($data['day_min']) && !empty($data['day_min']) ) 
		{
			$search['>=day'] = $data['day_min'];
		}
		
		if ( isset($data['day_max']) && !empty($data['day_max']) ) 
		{
			$search['<=day'] = $data['day_max'];
		}

		$lists_all = $this->$model->lists($search, $page, $num, $order_by,$fields_str);
		   
		if(! is_array($lists_all))
		{
			$error_code = 20000 + abs($lists_all);
			KsMessage::showError('get lists fail!','', $error_code);
		}
		   
       	$lists = $lists_all['lists'];
		$others_data = $lists_all['others_data'];
		   
       	KsMessage::showSucc('succ', $lists, $others_data);
	}

	function statistics()
	{	
		$data = $this->req_param('GET');
		$model = $this->model;

		$one = $this->$model->statistics($this->_user_id);

		KsMessage::showSucc('succ', $one);
	}
	
	
}
