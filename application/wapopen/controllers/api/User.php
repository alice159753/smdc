<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller 
{	
	private $model = "modeluser";
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
        $fields_str = 'id,account,shop_name,shop_address,contact_phone,shop_owner_name,shop_owner_phone,business_hours,shop_img_url,shop_note,other_note,role,is_delete,add_time,update_time';

		$search = array();
		$search['is_delete'] = 0;
		$search['role'] = 'user';

		if ( isset($data['shop_name']) && !empty($data['shop_name']) ) 
		{
			$search['%%shop_name'] = $data['shop_name'];
		}

		if ( isset($data['shop_owner_name']) && !empty($data['shop_owner_name']) ) 
		{
			$search['%%shop_owner_name'] = $data['shop_owner_name'];
		}

		if ( isset($data['contact_phone']) && !empty($data['contact_phone']) ) 
		{
			$search['%%contact_phone'] = $data['contact_phone'];
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
	
	/**
	 * 单个查询
	 */
	function one()
	{
		$data = $this->req_param('GET');
		$model = $this->model;

		// 必需传递的参数
		if(! isset($data[$this->$model->_primary] ) || empty($data[$this->$model->_primary]))
		{
			KsMessage::errorMessageFields('10104', $this->$model->_primary, 'api');
		}

		// 需要查询的字段
		$fields_str = 'id,account,shop_name,shop_address,contact_phone,shop_owner_name,shop_owner_phone,business_hours,shop_img_url,shop_note,other_note,role,is_delete,add_time,update_time';

		$one = $this->$model->one(array($this->$model->_primary=>$data[$this->$model->_primary]), $fields_str);

		if(!is_array($one))
		{
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
		$model = $this->model;
		$mc_key = md5(serialize($data)."#".__CLASS__."#".__FUNCTION__);

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
				$search = array($va => $data[$va]);
				$one = $this->$model->one($search);
				if( !empty($one) )
				{
					KsMessage::errorMessage('20006');
				}
			}
		}

		$data['add_time'] = date('Y-m-d H:i:s');

		// 防止重复提交
		$this->checkRepeat($mc_key, 3);

		//生成密码
		$data['salt'] = Common::myRandom(3, 32);
		$data['password'] = md5($data['password'] . $data['salt']);

		$insert_id = $this->$model ->insert($data);
		if( $insert_id <= 0 )
		{
			KsMessage::errorMessage('10109');
		}

		$insert_info = array('id' => intval($insert_id));

		KsMessage::showSucc('succ',$insert_info);
	}
	/**
	 * 数据修改
	 */
	function edit()
	{
		$model = $this->model;
		$data = $this->req_param();

		$data['update_time'] = date('Y-m-d H:i:s');

		unset($data['account']);

		if( isset($data['password']) && !empty($data['password']) )
		{
			//生成密码
			$data['salt'] = Common::myRandom(3, 32);
			$data['password'] = md5($data['password'] . $data['salt']);
		}

		$rs = $this->$model->update(array($this->$model->_primary=>$this->_user_id), $data);

		if( !$rs )
		{
			KsMessage::errorMessage('10110');
		}
		
		$one = $this->$model->one(array($this->$model->_primary=>$this->_user_id));

		unset($one['password'], $one['salt'], $one['id']);

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
			KsMessage::errorMessageFields('10104', $this->$model->_primary, 'api');
		}

		$rs = $this->$model->delete(array($this->$model->_primary=>$data[$this->$model->_primary]));

		if(!$rs)
		{
			$error_code = 20000 +abs ($rs);
			KsMessage::showError('edit fail!', '', $error_code);
		}

		$info = array('id' => intval($data[$this->$model->_primary]));

		KsMessage::showSucc("succ", $info);
	}
	
	/**
	 * 登录
	 */
	function login()
	{
		$data = $this->req_param('POST');
		$model = $this->model;

		$search = array();
		if ( !isset($data['account']) || empty($data['account']) ) 
		{
			KsMessage::errorMessage('20001');
		}

		if ( !isset($data['password']) || empty($data['password']) ) 
		{
			KsMessage::errorMessage('20002');
		}

		$search['account'] = $data['account'];

		$one = $this->$model->one($search);

		if( empty($one) )
		{
			KsMessage::errorMessage('20003');
		}

		if( $one['is_delete'] == 1 )
		{
			KsMessage::errorMessage('20005');
		}

		if( !$this->$model->checkPassword($data['password'], $one['password'], $one['salt']) )
		{
			KsMessage::errorMessage('20004');
		}

		$user_token = $this->$model->makeUserToken($one['id']);

		$one['user_token'] = $user_token;

		unset($one['password'], $one['salt'], $one['id']);

		KsMessage::showSucc("succ", $one);
	}
	

}
