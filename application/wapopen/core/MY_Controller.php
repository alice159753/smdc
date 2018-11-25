<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class MY_Controller extends CI_Controller 
{
	public $_api_key = 0;
    public $_api_secret = '';
    public $_api_code = '';
	public $_ie = 'UTF-8';
	public $_user_id = '0';

    public function __construct()
	{
		ob_start();
		parent::__construct();

        $this->_ie = isset($_REQUEST['ie']) ? strtoupper($_REQUEST['ie']) : 'UTF-8'; //接受编码
        
        $this->checkApiKey();

        $this->checkUserToken();
    }
    

    /**
	 * 检验api_key
	 * 
	 */
    function checkApiKey($data = array())
    {
        $this->load->config('config/config', true);
        $key_info = $this->config->item('config/config');
        $key_info = Common::arrChangeKey($key_info['key_info'], 'api_key');

        if ( !isset($key_info[ $_REQUEST['api_key'] ]) )
		{
			KsMessage::showError('api_key error!', '', '1003');
        }

        $api_info = $key_info[ $_REQUEST['api_key'] ];

        //不用缓存数据
		if ( isset($api_info['code']) && $api_info['code'] == 'test_key' )
		{
			$_REQUEST['__flush_cache'] = 1;
        }

        //如果不是测试Key则验证api_token
        if( $api_info['code'] != 'test_key' )
        {
            if ( !isset($_REQUEST['api_key']) || empty($_REQUEST['api_key']) )
            {
                KsMessage::showError('api_key error!', '', '1000');
            }

            if ( !isset($_REQUEST['api_token']) || empty($_REQUEST['api_token']) )
            {
                KsMessage::showError('api_token error!', '', '1001');
            }

            if ( !isset($_REQUEST['req_time']) || empty($_REQUEST['req_time']) )
            {
                KsMessage::showError('req_time error!', '', '1002');
            }

            $api_token = Common::getToken($_REQUEST, $api_info['api_secret']);

            if( $api_token != $_REQUEST['api_token'] )
            {
                KsMessage::showError('api_token error!', '', '1004');
            }
        }

		$this->_api_key = $api_info['api_key'];
		$this->_api_secret = $api_info['api_secret'];
		$this->_api_code = $api_info['code'];
    }

    function checkRepeat($mc_key, $t = 30)
    {

    }

    function checkUserToken()
    {
        $controlll = $this->router->fetch_class();
		$function = $this->router->fetch_method();

        if( $controlll == 'user' && $function == 'login' )
        {
            return '';
        }

        //验证user_token
        if( !isset($_REQUEST['user_token']) || empty($_REQUEST['user_token']) )
        {
			KsMessage::errorMessage('10112');
        }

        if( strlen($_REQUEST['user_token']) != 32 )
        {
			KsMessage::errorMessage('10113');
        }

		$this->load->model('api/modeluser', 'modeluser');
        $user_id = $this->modeluser->getUserId($_REQUEST['user_token']);
        if( !is_numeric($user_id) || empty($user_id) )
        {
			KsMessage::errorMessage('10114');
        }

        $one = $this->modeluser->one(array('id' => $user_id));
        if( empty($one) )
        {
			KsMessage::errorMessage('10115');
        }

        $this->_user_id = $user_id;
        $this->_role = $one['role'];

        Common::debug("user_id=".$this->_user_id);
        Common::debug("role=".$this->_role);

    }
    
	/**
	 * 获取请求参数
	 * @return array
	 */
	function req_param($method = 'POST')
	{
        if ($method == 'POST') 
        {
            if(isset($_GET['post_change_get']) && $_GET['post_change_get']==1) 
            {
				$data = ($this->_ie != 'UTF-8') ? Common::convertEncoding($_GET, 'UTF-8', $this->_ie) : $_GET;
            }
            else
            {
				$data = ($this->_ie != 'UTF-8') ? Common::convertEncoding($_POST, 'UTF-8', $this->_ie) : $_POST;
            }
        }
        elseif ($method == 'REQUEST') 
        {
			$data = ($this->_ie != 'UTF-8') ? Common::convertEncoding($_REQUEST, 'UTF-8', $this->_ie) : $_REQUEST;
        } 
        else 
        {
			$data = ($this->_ie != 'UTF-8') ? Common::convertEncoding($_GET, 'UTF-8', $this->_ie) : $_GET;
		}

		return $data;
	}
	
}




?>