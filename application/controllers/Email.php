
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Email extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('url');
		$this->load->model("email_model");
    }
	
	function emailtest($para1='', $para2=''){
		
		 $this->email_model->demoemail();	
			
		}
		
    function thankyouregister($para1='', $para2=''){
		 $username  = 'balaji';
		 $useremail = 'balaji30nsit@gmail.com';
		 $this->email_model->thankyouregister($username,$useremail);	
			
		}
		
   function notification($para1='', $para2='', $para3=''){
	   header("Access-Control-Allow-Origin: *");

	     $data2['sale_code'] = $para2;
		 $data2['status'] = $para3;
	     $this->load->view('pushnotification',$data2);
		 
   }
}

?>
 
