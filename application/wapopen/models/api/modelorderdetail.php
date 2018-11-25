<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modelorderdetail extends MY_Model
{
    //表名
    public $_table_name = 'smdc_order_detail';

    //表别名
    public $_table_byname = 'smdc_order_detail';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','user_id','order_id','product_id','num'),
        'string' => array('price'),
        'time' => array('add_time'),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array();
    public $_unique = array();

    public $deaf_fields_str = 'id,user_id,order_id,product_id,num,price,add_time';

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

    function delete_order($user_id, $order_id)
    {
        $sql = "DELETE FROM {$this->_table_name} WHERE user_id = ? AND order_id = ?";
        
		$this->_query($sql, array($user_id, $order_id));
    }

}