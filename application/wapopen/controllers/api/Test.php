<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller 
{	
	public function image()
    {
        $this->load->view('templates/test.html');
    }
}


?>