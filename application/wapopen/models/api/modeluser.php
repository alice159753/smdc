<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Modeluser extends MY_Model
{
    //表名
    public $_table_name = 'smdc_user';

    //表别名
    public $_table_byname = 'smdc_user';

    //数据库表对应的字段类型
    public $_fields = array(
        'int' => array('id','is_delete'),
        'string' => array('account','account','password','salt','shop_name','shop_address','contact_phone','shop_owner_name','shop_owner_phone','business_hours','shop_img_url','shop_note','other_note','role','special_note','show_password'),
        'time' => array('add_time','update_time'),
        'unformat' => array(),
        'lists_nosearch' => array()
    );

    public $_exist = array('account', 'password');
    public $_unique = array('account');

    public $deaf_fields_str = 'id,account,password,salt,shop_name,shop_address,contact_phone,shop_owner_name,shop_owner_phone,business_hours,shop_img_url,shop_note,other_note,role,is_delete,add_time,update_time,special_note,show_password';

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

    function checkPassword($password, $db_password, $salt)
    {
        return md5($password.$salt) == $db_password ? true : false;
    }

    function makeUserToken($user_id)
    {
        $rand = rand(1000, 9999);
        return Common::encrypt($user_id.$rand);
    }

    function getUserId($user_token)
    {
        $user_id = Common::decrypt($user_token);
        return substr($user_id, 0, -4);
    }

}