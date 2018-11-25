<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Upload extends MY_Controller 
{	

	public function image()
    {
        echo header("Access-Control-Allow-Origin:*");

        $file_name = 'image';
        
        if (!isset($_FILES[$file_name])) 
        {
            KsMessage::errorMessage('10116');
        }
        
        $img_mimes = array(
                'gif'=>array('image/gif'), 
                'png'=>array('image/png',  'image/x-png'),
                'jpeg'=>array('image/jpeg', 'image/pjpeg'),
                'jpg'=>array('image/jpeg', 'image/pjpeg'),
                'apk'=>array('application/octet-stream','application/vnd.android.package-archive'),
        );
        
        //默认配置
        if (empty($config)) 
        {
            $config['base_dir'] = FCPATH;
            $config['upload_path'] =  FCPATH.'/upload/'.date('Y-m-d').'/';
            $config['allowed_types'] = array('png', 'jpg', 'jpeg', 'apk');
            $config['max_size'] = 1024 * 1024* 10;//文件大小
            $config['domain_name'] = IMG_HOST; //域名
        }
            
        if( $_FILES[$file_name]['error'] != 0 ) 
        {
            KsMessage::errorMessage('10117');
        }
            
        if($_FILES[$file_name]['size'] > $config['max_size']) 
        {
            KsMessage::errorMessage('10118');
        }
            
        $info = explode('.', $_FILES[$file_name]['name']);
        $info = pathinfo($_FILES[$file_name]['name']);
        $ext  = $info['extension'];

        //检查文件的后缀文件名称
        if (!in_array($ext, $config['allowed_types'])) 
        {
            KsMessage::errorMessage('10119');
        }
        
        //检查文件的类型
        $file_type_flag = false;
        foreach ($img_mimes as $c_ext=>$c_mime) 
        {
            $index = array_search($_FILES[$file_name]['type'], $c_mime);
            if ($index !== false) {
                $file_type_flag = true;
            }
        }
        
        if(!$file_type_flag) 
        {
            KsMessage::errorMessage('10119');
        }
        
        //创建文件夹
        if(!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $cur_time = microtime(TRUE);
        
        $file = $config['upload_path'].md5($cur_time).'.'.$ext;
        if(move_uploaded_file($_FILES[$file_name]['tmp_name'], $file))
        {
            $rd['file'] = $file;            //文件绝对路径
            $rd['size'] = $_FILES[$file_name]['size'];
            $rd['url'] = str_replace($config['base_dir'], $config['domain_name'], $file);        //文件访问路径
            $rd['file_names'] = basename($file);

            KsMessage::showSucc("succ", $rd);
        }
        
        KsMessage::errorMessage('10120');
    }
}


?>