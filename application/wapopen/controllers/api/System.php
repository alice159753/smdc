<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class System extends MY_Controller 
{	
	private $model = "modelsystem";
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

		$lists_all = $this->$model->lists($search, $page, $num, $order_by,$fields_str);
		   
		if( !is_array($lists_all) )
		{
			$error_code = 20000 + abs($lists_all);
			KsMessage::showError('get lists fail!','', $error_code);
		}
		   
       	$lists = $lists_all['lists'];
		$others_data = $lists_all['others_data'];
		   
       	KsMessage::showSucc('succ', $lists, $others_data);
	}
	
	/**
	 * 单个查询
	 */
	function one()
	{
		$data = $this->req_param('GET');
		$model = $this->model;

		$lists_all = $this->$model->lists(array(), 1, 1);
		$one = $lists_all['lists'][0];

		if(!is_array($one)){
			$error_code = 20000 + abs($one);
			KsMessage::showError('get one','',$error_code);
		}

		KsMessage::showSucc('succ',$one);
	}
	/**
	 * 新增
	 */
	function add()
	{
		$data = $this->req_param();
		$mc_key = md5(serialize($data)."#".__CLASS__."#".__FUNCTION__);
		$model = $this->model;

		unset($data[$this->$model->_primary]);

		// 防止重复提交
		$this->checkRepeat($mc_key, 3);

		//判断是否有添加，有则更新否则则添加
		$lists_all = $this->$model->lists(array(), 1, 1);
		$one = $lists_all['lists'][0];

		if( empty($one) )
		{
			$insert_id = $this->$model->insert($data);
			if( $insert_id <= 0 )
			{
				$error_code = 20000 + abs ( $insert_id);
				KsMessage::showError('insert fail', '', $error_code);
			}

			$insert_info = array('id' => intval($insert_id));

			KsMessage::showSucc('succ', $insert_info);
		}
		else 
		{
			$rs = $this->$model->update($one['id'], $data);
			if(!$rs)
			{
				$error_code = 20000 + abs($rs);
				KsMessage::showError('edit fail!','',$error_code);
			}

			$one = $this->$model->one(array($this->$model->_primary=>$one['id']));

			KsMessage::showSucc("succ", $one);
		}

	}
	/**
	 * 数据修改
	 */
	function edit()
	{
		$model = $this->model;
		$data = $this->req_param();

		// 修改必须带有主键id
		if(! isset($data[$this->$model->_primary]) || empty($data[$this->$model->_primary])) 
		{
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		// 必须传递的参数
		$_exist = $this->$model->_exist;
		foreach ($_exist as $key => $va) 
		{
			if(! isset($data[$va]) && empty($data[$va]) )
			{
				KsMessage::errorMessageFields('10104', $va, 'api');
			}
		}

		//判断唯一
		$_unique = $this->$model->_unique;
		foreach ($_unique as $key => $va) 
		{
			if( isset($data[$va]) && !empty($data[$va]) )
			{
				$search = array($va => $data[$va], 'user_id' => $this->_user_id);
				$search['!'.$this->$model->_primary] = $data[$this->$model->_primary];

				$one = $this->$model->one($search);
				if( !empty($one) )
				{
					KsMessage::errorMessage('20010');
				}
			}
		}

		$data['update_time'] = date('Y-m-d H:i:s');

		$id = $data[$this->$model->_primary];
		unset($data[$this->$model->_primary]);

		$rs = $this->$model->update($id, $data);

		if(!$rs)
		{
			$error_code = 20000 + abs($rs);
			KsMessage::showError('edit fail!','',$error_code);
		}

		$one = $this->$model->one(array($this->$model->_primary=>$id));

		KsMessage::showSucc("succ", $one);
	}
	/**
	 * 物理删除
	 */
	function del()
	{
		$model = $this->model;
		$data = $this->req_param();

		// 修改必须带有主键id
		if(! isset($data[$this->$model->_primary]) || empty($data[$this->$model->_primary])) 
		{
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		$rs = $this->$model->delete($data[$this->$model->_primary]);

		if(!$rs)
		{
			$error_code = 20000 +abs ($rs);
			KsMessage::showError('edit fail!', '', $error_code);
		}

		$info = array('id' => intval($data[$this->$model->_primary]));

		KsMessage::showSucc("succ", $info);
	}
	
	
}
