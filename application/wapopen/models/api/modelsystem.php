<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modelsystem extends MY_Model
{
    //表名
    public $_table_name = 'smdc_system';

    //表别名
    public $_table_byname = 'smdc_system';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id'),
        'string' => array('app_use_info','contact_us','user_use_info','about_us','system_info'),
        'time' => array(),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array();
    public $_unique = array();

    public $deaf_fields_str = 'id,app_use_info,contact_us,user_use_info,about_us,system_info';

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