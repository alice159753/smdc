<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modeltable extends MY_Model
{
    //表名
    public $_table_name = 'smdc_table';

    //表别名
    public $_table_byname = 'smdc_table';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','user_id'),
        'string' => array('title'),
        'time' => array('add_time','update_time'),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array('title');
    public $_unique = array('title');

    public $deaf_fields_str = 'id,user_id,title,add_time,update_time';

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

}