<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Init {

    static private $links = array();//数据库连接

    public function __construct() 
    {

    }

    public static function getDbConnect($group_name, $db_object = FALSE, $params = array(), $re_connect = FALSE) 
    {
        $CI = & get_instance();
        $group_name = strtoupper($group_name);
    
        //修改数据库连接属性
        do {
            if(empty($params))
            {
                continue;
            }
                
            if ( ! file_exists($file_path = APPPATH.'config/database.php'))
            {
                show_error('The configuration file database.php does not exist.');
            }
                
            include($file_path);
                
            $group_name = isset($db[$group_name]) ? $db[$group_name] : array();
            $group_name = array_merge($group_name, $params);
          
        } 
        while (0);

        Common::debug($group_name, 'group_name');
    
        //数据库连接 
        if ($db_object == TRUE)
        {
            //数据库连接key
            if( is_string($group_name) )
            {
                $db_key = md5($group_name);
            }
            else
            {
                $db_key = md5(implode("-", $group_name));
            }
                
            //查看对象是否存在或者是否强制重新连接
            if(!isset(self::$links[$db_key]) || $re_connect)
            {
                self::$links[$db_key] = $CI->load->database($group_name, TRUE);
            }
                
            return self::$links[$db_key];
        } 
        else
        {
            $CI->load->database($group_name);
        }
    }
}
