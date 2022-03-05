<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');



class Email_model extends CI_Model
{
 
    
    function __construct()
    {
        parent::__construct();
    }
    
    

   
    
	function otp_page($username,$useremail, $otp){
	
      $from   = 'admin@optimaprotech.com';
        
       $to         =  $useremail;
        $subject    = 'OTP';
		$page_data['username'] = $username;
		$page_data['useremail'] = $useremail;
		$page_data['optdata'] = $otp;
		$msg        =  $this->load->view('email/otp_page',$page_data,true);
		$from_name =  $this->Api_model->get_type_name_by_id('general_settings','3','value');
		$this->do_email($from, $from_name, $to, $subject, $msg);
	}

    function do_email($from = '', $from_name = '', $to = '', $sub ='', $msg ='')
    {   
        $this->load->library('email');
        $this->email->set_newline("\r\n");
        $this->email->from($from, $from_name);
        $this->email->to($to);        
        $this->email->subject($sub);
        $this->email->message($msg);
        
        if($this->email->send()){
            return true;
        }else{
          echo $this->email->print_debugger();
            return false;
        }
        //echo $this->email->print_debugger();
    }
    
    
    
}