<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modelorder extends MY_Model
{
    //表名
    public $_table_name = 'smdc_order';

    //表别名
    public $_table_byname = 'smdc_order';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','user_id','user_num','status'),
        'string' => array('order_num','price','note','table_title'),
        'time' => array('add_time','update_time'),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array();
    public $_unique = array();

    public $deaf_fields_str = 'id,user_id,order_num,user_num,table_title,price,status,note,add_time,update_time';

    //不需要处理的字段的数组
    public $_unformat = array();

    public $_primary = 'id';

    public $_status = array(0=>'待结算', 1 =>'已结算');

    function __construct()
    {
        parent::__construct();

        $this->_class_path = __CLASS__;
        $this->_change_cache = TRUE;
        $this->_cache_key = 'MC_KEY#'.$this->_table_name;
    }

    function make_order_num()
    {
		return date('YmdHis').common::myRandom(3, 7);
    }

}