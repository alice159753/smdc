<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Order extends MY_Controller 
{	
	private $model = "modelorder";
	function __construct() 
	{
		parent::__construct();

		$this->load->model('api/'.$this->model, $this->model);

		$this->load->model('api/modelorderdetail', 'modelorderdetail');
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

		if ( isset($data['table_title']) && !empty($data['table_title']) ) 
		{
			$search['%%table_title'] = $data['table_title'];
		}

		if ( isset($data['status']) && is_numeric($data['status']) ) 
		{
			$search['status'] = $data['status'];
		}
		
		$lists_all = $this->$model->lists($search, $page, $num, $order_by,$fields_str);
		   
		if(! is_array($lists_all))
		{
			$error_code = 20000 + abs($lists_all);
			KsMessage::showError('get lists fail!','', $error_code);
		}
		   
       	$lists = $lists_all['lists'];
		$others_data = $lists_all['others_data'];
		   
		foreach ($lists as $key => $value) 
		{
			$lists[$key]['status_title'] = isset($this->$model->_status[$value['status']]) ? $this->$model->_status[$value['status']] : '';
		}

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
		if(! isset($data[$this->$model->_primary] ) || empty($data[$this->$model->_primary])){
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		// 需要查询的字段
		$fields_str = $this->$model->deaf_fields_str;
		if(isset($data['fields_str']) && !empty($data['fields_str'])){
			$fields_str = $data['fields_str'];
		}
		
		$one = $this->$model->one(array($this->$model->_primary=>$data[$this->$model->_primary]), $fields_str);

		if( !is_array($one) )
		{
			KsMessage::errorMessage('10106');
		}

		//当前等待人数
		$one['wait_user'] = $this->$model->getCount(array('user_id' => $this->_user_id, 'status' => 0, '!id' => $one['id']));

		//订单状态
		$one['status_title'] = isset($this->$model->_status[$one['status']]) ? $this->$model->_status[$one['status']] : '';

		//获得所有的菜品
		$this->load->model('api/modelproduct', 'modelproduct');
		$product = $this->modelproduct->lists(array('user_id' => $this->_user_id, 'is_online' => 1), 1, 100);
		$productMap = Common::arrChangeKey($product['lists'], 'id');

		$this->load->model('api/modelorderdetail', 'modelorderdetail');
		$order_detail = $this->modelorderdetail->lists(array('user_id' => $this->_user_id, 'order_id' => $one['id']), 1, 100);
		$order_detail = $order_detail['lists'];

		foreach ($order_detail as $key => $value)
		{
			$value['title'] = isset($productMap[$value['product_id']]) ? $productMap[$value['product_id']]['title'] : '';

			$one['order_detail'][] = $value;
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

		if( !isset($data['product_ids']) || empty($data['product_ids']) )
		{
			KsMessage::errorMessage('20011');
		}

		if( !isset($data['nums']) || empty($data['nums']) )
		{
			KsMessage::errorMessage('20012');
		}

		if( !isset($data['user_num']) || empty($data['user_num']) )
		{
			KsMessage::errorMessage('20013');
		}

		if( !isset($data['table_title']) || empty($data['table_title']) )
		{
			KsMessage::errorMessage('20015');
		}

		// 防止重复提交
		$this->checkRepeat($mc_key, 3);

		//检查这个桌子当前是否已经有人下单了
		$search = array();
		$search['user_id'] = $this->_user_id;
		$search['table_title'] = $data['table_title'];
		$search['status'] = 0;

		//如果已经有一个桌子已经处于点餐的状态，继续点餐的话，则往订单里面加菜
		$li = $this->$model->lists($search,1,1);
		if( count($li['lists']) > 0 )
		{
			$this->append_add($data, $li['lists'][0]);
			exit;
		}

		$order_num = $this->$model->make_order_num();
		$data['order_num'] = $order_num;

		$data['user_id'] = $this->_user_id;
		$data['add_time'] = date('Y-m-d H:i:s');
		$data['status'] = 0;

		$insert_id = $this->$model->insert($data);
		if( $insert_id <= 0 )
		{
			$error_code = 20000 + abs($insert_id);
			KsMessage::showError('insert fail', '', $error_code);
		}

		//添加订单详情
		$product_ids = explode(",", $data['product_ids']);
		$nums = explode(",", $data['nums']);

		//获得所有的菜品信息
		$this->load->model('api/modelproduct', 'modelproduct');
		$product = $this->modelproduct->lists(array('user_id' => $this->_user_id, 'is_online' => 1), 1, 100);
		$productMap = Common::arrChangeKey($product['lists'], 'id');

		$total_price = 0;
		$total_num = 0;

		for($i = 0; isset($product_ids[$i]); $i++)
		{
			//判断菜品是否存在
			if( !isset($productMap[ $product_ids[$i] ]) )
			{
				KsMessage::errorMessage('20014');
			}

			//检查菜品数量是否充足
			if( $productMap[ $product_ids[$i] ]['num'] < $nums[$i] )
			{
				KsMessage::errorMessage('20016');
			}

			$dataArray = array();
			$dataArray['user_id']    = $this->_user_id;
			$dataArray['order_id']   = $insert_id;
			$dataArray['product_id'] = $product_ids[$i];
			$dataArray['num']        = $nums[$i];
			$dataArray['price']      = isset($productMap[ $product_ids[$i] ]) ? $productMap[ $product_ids[$i] ]['price'] * $nums[$i] : 0;
			$dataArray['add_time']   = date('Y-m-d H:i:s');

			$this->modelorderdetail->addRow($dataArray);

			$total_price += $dataArray['price'];
			$total_num += $dataArray['num'];

			//扣除菜品的数量
			$this->modelproduct->deduct($product_ids[$i], 'num', $nums[$i]);
		}

		$this->modelorderdetail->batch();

		$insert_info = array();
		$insert_info['id'] = $insert_id;
		$insert_info['order_num'] = $order_num;

		$this->$model->update($insert_id, array('price' => $total_price, 'update_time' => date('Y-m-d H:i:s')));

		//更新统计表
		$this->load->model('api/modelorderday', 'modelorderday');
		$this->modelorderday->addnum($this->_user_id, 1, $total_price);

		KsMessage::showSucc('succ', $insert_info);
	}

	function append_add($data, $order)
	{
		$model = $this->model;

		$order_num = $order['order_num'];
		$order_id = $order['id'];

		//获得所有的菜品信息
		$this->load->model('api/modelproduct', 'modelproduct');
		$product = $this->modelproduct->lists(array('user_id' => $this->_user_id, 'is_online' => 1), 1, 100);
		$productMap = Common::arrChangeKey($product['lists'], 'id');

		//添加订单详情
		$product_ids = explode(",", $data['product_ids']);
		$nums = explode(",", $data['nums']);

		//获取订单商品详情
		$this->load->model('api/modelorderdetail', 'modelorderdetail');
		$orderdetail = $this->modelorderdetail->lists(array('user_id' => $this->_user_id, 'order_id' => $order_id), 1, 1000);
		$orderdetail = $orderdetail['lists'];
		$orderdetail = Common::arrChangeKey($orderdetail, 'product_id');

		$total_price = 0;
		$total_num = 0;

		for($i = 0; isset($product_ids[$i]); $i++)
		{
			//判断菜品是否存在
			if( !isset($productMap[ $product_ids[$i] ]) )
			{
				KsMessage::errorMessage('20014');
			}

			//检查菜品数量是否充足
			if( $productMap[ $product_ids[$i] ]['num'] < $nums[$i] )
			{
				KsMessage::errorMessage('20016');
			}

			//判断是否是已添加商品,已添加则更新商品详情
			if( isset($orderdetail[ $product_ids[$i] ] ) )
			{
				$dataArray = array();
				$dataArray['num']        = $nums[$i] + $orderdetail[ $product_ids[$i] ]['num'];
				$dataArray['price']      = isset($productMap[ $product_ids[$i] ]) ? $productMap[ $product_ids[$i] ]['price'] * $nums[$i] : 0;
				$dataArray['price']      =  $dataArray['price'] + $orderdetail[ $product_ids[$i] ]['price'];

				$this->modelorderdetail->update($orderdetail[ $product_ids[$i] ]['id'], $dataArray);

				$total_price += $dataArray['price'];
				$total_num += $dataArray['num'];

				//扣除菜品的数量
				$this->modelproduct->deduct($product_ids[$i], 'num', $nums[$i]);

				continue;
			}

			$dataArray = array();
			$dataArray['user_id']    = $this->_user_id;
			$dataArray['order_id']   = $order_id;
			$dataArray['product_id'] = $product_ids[$i];
			$dataArray['num']        = $nums[$i];
			$dataArray['price']      = isset($productMap[ $product_ids[$i] ]) ? $productMap[ $product_ids[$i] ]['price'] * $nums[$i] : 0;
			$dataArray['add_time']   = date('Y-m-d H:i:s');

			$this->modelorderdetail->addRow($dataArray);

			$total_price += $dataArray['price'];
			$total_num += $dataArray['num'];

			//扣除菜品的数量
			$this->modelproduct->deduct($product_ids[$i], 'num', $nums[$i]);
		}

		$this->modelorderdetail->batch();

		$this->$model->update($order_id, array('price' => $total_price, 'update_time' => date('Y-m-d H:i:s')));

		$order['price'] = $total_price;
		$order['update_time'] = date('Y-m-d H:i:s');

		//更新统计表
		$this->load->model('api/modelorderday', 'modelorderday');
		$this->modelorderday->addnum($this->_user_id, 0, $total_price);

		KsMessage::showSucc('succ', $order);
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
					KsMessage::errorMessage('20007');
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

		//删除订单详细
		$this->load->model('api/modelorderdetail', 'modelorderdetail');
		$this->modelorderdetail->delete_order($this->_user_id, $data[$this->$model->_primary]);

		$info = array('id' => intval($data[$this->$model->_primary]));

		KsMessage::showSucc("succ", $info);
	}
	
	function save()
	{
		$model = $this->model;
		$data = $this->req_param();

		// 修改必须带有主键id
		if(! isset($data[$this->$model->_primary]) || empty($data[$this->$model->_primary])) 
		{
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		$id = $data[$this->$model->_primary];
		unset($data[$this->$model->_primary]);

		$update = array();
		$update['update_time'] = date('Y-m-d H:i:s');
		$update['note'] = isset($data['note']) ? $data['note'] : '';

		$rs = $this->$model->update($id, $update);

		if(!$rs)
		{
			$error_code = 20000 + abs($rs);
			KsMessage::showError('edit fail!','',$error_code);
		}

		KsMessage::showSucc("succ", array('id' => $id));
	}

	/**
	 * 打单
	 */
	function printing()
	{
		$model = $this->model;
		$data = $this->req_param();

		// 修改必须带有主键id
		if(! isset($data[$this->$model->_primary]) || empty($data[$this->$model->_primary])) 
		{
			KsMessage::showError($this->$model->_primary.'不能为空哦','',10140);
		}

		$id = $data[$this->$model->_primary];
		unset($data[$this->$model->_primary]);

		$update = array();
		$update['update_time'] = date('Y-m-d H:i:s');
		$update['status'] = 1;

		$rs = $this->$model->update($id, $update);

		if(!$rs)
		{
			$error_code = 20000 + abs($rs);
			KsMessage::showError('edit fail!','',$error_code);
		}

		KsMessage::showSucc("succ", array('id' => $id));
	}

}
