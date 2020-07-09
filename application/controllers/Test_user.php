<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Test_user extends CI_Controller {

    function __construct(){
      parent::__construct();
      $this->load->library('email');
    }

   function do_email(){

      $this->load->library('email');
      $config['protocol'] = "smtp";
      $config['smtp_host'] = "ssl://mail.digitalfriend.co.in";
      $config['smtp_port'] = 465;
      $config['smtp_user'] = "noreply@digitalfriend.co.in";
      $config['smtp_pass'] = "admin@123";
      $config['charset'] = "utf-8";
      $config['mailtype'] = "html";
      $config['newline'] = "\r\n";
      $config['smtp_debug'] = 4;
      $this->email->initialize($config);
      
      $from = "noreply@digitalfriend.co.in";
      $this->email->from($from, "fd");
      $this->email->to("spthakkar17@gmail.com");
      
      $this->email->subject("test mail");
      $this->email->message("test msg");

      $sent = $this->email->send();
      
      if ($sent) {
         echo "Done";
      } else {
         echo "Erorr";
      }
   }

   
}
