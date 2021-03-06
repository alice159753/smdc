<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modelproductgift extends MY_Model
{
    //表名
    public $_table_name = 'smdc_product_gift';

    //表别名
    public $_table_byname = 'smdc_product_gift';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','user_id','num','is_online','sale_num'),
        'string' => array('title','price','img_url'),
        'time' => array('add_time','update_time'),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array('title');
    public $_unique = array();

    public $deaf_fields_str = 'id,user_id,title,num,is_online,price,img_url,add_time,update_time,sale_num';

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

    function deduct($product_id, $field, $num)
	{
		$sql = "UPDATE {$this->_table_name} SET `{$field}`=`{$field}`- ?, `sale_num`=`sale_num`+ ? WHERE id = ?";
		$this->_query($sql, array($num,$num,$product_id));
    }
    
}