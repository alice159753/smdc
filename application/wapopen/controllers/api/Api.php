<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller 
{	
	public function index()
    {
        $this->load->view('templates/api.html');
    }
}


?>