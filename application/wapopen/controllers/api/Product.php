<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller 
{	
	private $model = "modelproduct";
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

		if ( isset($data['title']) && !empty($data['title']) ) 
		{
			$search['%title%'] = $data['title'];
		}

		if ( isset($data['category_id']) && is_numeric($data['category_id']) ) 
		{
			$search['category_id'] = $data['category_id'];
		}

		if ( isset($data['label_id']) && is_numeric($data['label_id']) ) 
		{
			$search['label_id'] = $data['label_id'];
		}

		if ( isset($data['extend_label_id']) && is_numeric($data['extend_label_id']) ) 
		{
			$search['extend_label_id'] = $data['extend_label_id'];
		}

		if ( isset($data['is_online']) && is_numeric($data['is_online']) ) 
		{
			$search['is_online'] = $data['is_online'];
		}

		if ( isset($data['!num']) && is_numeric($data['!num']) ) 
		{
			$search['!num'] = $data['!num'];
		}

		$lists_all = $this->$model->lists($search, $page, $num, $order_by,$fields_str);
		   
		if(! is_array($lists_all))
		{
			$error_code = 20000 + abs($lists_all);
			KsMessage::showError('get lists fail!','', $error_code);
		}
		   
       	$lists = $lists_all['lists'];
		$others_data = $lists_all['others_data'];

		//获取分类
		$this->load->model('api/modelcategory', 'modelcategory');
		$category = $this->modelcategory->lists(array('user_id' => $this->_user_id), 1, 1000);
		$categoryMap = Common::arrChangeKey($category['lists'], 'id');

		//获取标签
		$this->load->model('api/modellabel', 'modellabel');
		$label = $this->modellabel->lists(array('user_id' => $this->_user_id), 1, 1000);
		$labelMap = Common::arrChangeKey($label['lists'], 'id');

		foreach ($lists as $key => $value) 
		{
			$lists[$key]['category_title'] = isset($categoryMap[$value['category_id']]) ? $categoryMap[$value['category_id']]['title'] : '';

			$lists[$key]['label_title'] = isset($labelMap[$value['label_id']]) ? $labelMap[$value['label_id']]['title'] : '';
		}

       	KsMessage::showSucc('succ', $lists, $others_data);
	}

	//前端展示菜品
	function li()
	{
		$data = $this->req_param('GET');
		$model = $this->model;

		$_GET['is_online'] = 1;
		$_GET['!num'] = 0;

		$this->lists();
	}
	
	/**
	 * 单个查询
	 */
	function one()
	{
		$data = $this->req_param('GET');
		$model = $this->model;

		// 必需传递的参数
		if(! isset($data[$this->$model->_primary] ) || empty($data[$this->$model->_primary])){
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		// 需要查询的字段
		$fields_str = $this->$model->deaf_fields_str;
		if(isset($data['fields_str']) && !empty($data['fields_str'])){
			$fields_str = $data['fields_str'];
		}
		
		$one = $this->$model->one(array($this->$model->_primary=>$data[$this->$model->_primary]), $fields_str);

		//获取赠品信息
		$this->load->model('api/modelproductgift', 'modelproductgift');
		if( !empty($one['gift_ids']) )
		{
			$gift_ids = explode(",", $one['gift_ids']);
			$giftlists = $this->modelproductgift->inlist($gift_ids, 'id', '*');
			$one['gift_id_lists'] = array_values($giftlists);
		}

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
				$one = $this->$model->one($search);
				if( !empty($one) )
				{
					KsMessage::errorMessage('20010');
				}
			}
		}

		unset($data[$this->$model->_primary]);

		//判断销售数量不能超过9999 sale_num
		$data['num'] = isset($data['num']) ? $data['num'] : 0;
		$data['num'] = isset($data['num']) && $data['num'] > 9999 ?  9999 : $data['num'];
		$data['user_id'] = $this->_user_id;
		$data['add_time'] = date('Y-m-d H:i:s');

		// 防止重复提交
		$this->checkRepeat($mc_key, 3);

		$insert_id = $this->$model->insert($data);
		if($insert_id <=0)
		{
			$error_code = 20000 + abs ( $insert_id);
			KsMessage::showError('insert fail', '', $error_code);
		}

		$insert_info = array('id' => intval($insert_id));

		KsMessage::showSucc('succ', $insert_info);
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

		$data['num'] = isset($data['num']) ? $data['num'] : 0;
		$data['num'] = isset($data['num']) && $data['num'] > 9999 ?  9999 : $data['num'];
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
