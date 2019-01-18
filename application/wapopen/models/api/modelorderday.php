<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modelorderday extends MY_Model
{
    //表名
    public $_table_name = 'smdc_order_day';

    //表别名
    public $_table_byname = 'smdc_order_day';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','user_id','day','order_count'),
        'string' => array('order_price'),
        'time' => array(),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array('title');
    public $_unique = array('title');

    public $deaf_fields_str = 'id,user_id,day,order_count,order_price';

    //不需要处理的字段的数组
    public $_unformat = array();

    public $_primary = 'id';

    function __construct()
    {
        parent::__construct();

        $this->_class_path = __CLASS__;
        $this->_change_cache = TRUE;
        $this->_cache_key = 'MC_KEY#'.$this->_table_name;
    }


    function statistics($user_id)
    {
        $result = array();
        //总金额
        //总订单
        $sql = "SELECT sum(order_count) as order_count, sum(order_price) as order_price FROM {$this->_table_name} WHERE user_id = ?";
        $query = $this->_query($sql, array($user_id));
        $one = $query->row_array();
        $result['total_order_count'] = $one['order_count'];
        $result['total_order_price'] = $one['order_price'];

        //今日营收额
        //今日订单数
        $sql = "SELECT sum(order_count) as order_count, sum(order_price) as order_price FROM {$this->_table_name} WHERE user_id = ? and day =". date('Ymd');
        $query = $this->_query($sql, array($user_id));
        $one = $query->row_array();
        $result['day_order_count'] = $one['order_count'];
        $result['day_order_price'] = $one['order_price'];

        //菜品分类
        $sql = "SELECT count(*) as category_count FROM smdc_category WHERE user_id = ?";
        $query = $this->_query($sql, array($user_id));
        $one = $query->row_array();
        $result['category_count'] = $one['category_count'];

        //菜品数量
        $sql = "SELECT count(*) as product_count FROM smdc_product WHERE user_id = ?";
        $query = $this->_query($sql, array($user_id));
        $one = $query->row_array();
        $result['product_count'] = $one['product_count'];

        return $result;
    }

    function addnum($user_id, $num1, $num2)
	{
        $dataArray = array();
        $dataArray['day']         = date('Ymd');
        $dataArray['user_id']     = $user_id;
        $dataArray['order_count'] = empty($num1) ? 0 : $num1;
        $dataArray['order_price'] = empty($num2) ? 0 : $num2;

        $db = $this->dbConnect('master');
        $sql = $db->insert_string($this->_table_name, $dataArray);
        $sql .= " on duplicate key update order_count = {$dataArray['order_count']} , order_price = {$dataArray['order_price']}";
		$this->_query($sql);
    }
    

}