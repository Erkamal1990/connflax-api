<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Email_model extends CI_Model {

    function __construct(){
        parent::__construct();
        $this->load->library('email');
    }
	
	function master_template($content){

      $html = "";
      $html .= '<div style="background-color:#EEEEEE; padding:50px 0;">';
      $html .= '<div style="text-align: center">';
      $html .= '</div>';
      $html .= '<div style="max-width: 600px; border: solid 1px #ccc; background-color: #fff; margin: 0 auto; padding:25px 0">';
      $html .= $content;
      $html .= '</div>';
      $html .= '</div>';

      return $html;
   }

   function ordermail_to_customer($user_id, $order_group_id){

      $user = $this->db->get_where("users", array("user_id" => $user_id))->row_array();
      //$orders = $this->db->get_where("orders", array("order_group_id" => $order_group_id))->row_array();

      $orders = $this->db->query("select * from orders inner join order_product on (orders.product_id = order_product.id) where order_group_id = '".$order_group_id."'")->result_array();

      $orderPayment = $this->db->query("select * from order_payment where order_group_id = '".$order_group_id."'")->row_array();

      $shipping = $this->db->get_where("shipments", array("shipping_id" => $orderPayment['shipping_id']))->row_array();

      $payment = $this->db->get_where("payments", array("payment_id" => $orderPayment['payment_id']))->row_array();

      $content = '';
      $content .= '<div style="text-align:center; padding:25px 0">';
      $content .= '<a href="'.FRONT_URL.'">';
      $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
      $content .= '</a>';
      $content .= '<h2 style="font-size: 24px;">Thank you! '.$user['name'].'</h2>';
      $content .= '<p style="color: #999; font-size: 18px;">Your order #'.$order_group_id.' has been successfully placed with '.SYSTEM_NAME.'.</p>';
      $content .= '<div style="text-center; padding: 15px;">';

      $content .= "<table style='width: 100%; font-size: 12px;' cellpadding='10px' cellspacing='0'>";

      $content .= "<thead>";
      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; background-color: #ccc; text-align: left;'>Item</th>";
      $content .= "<th style='border: solid 1px #eee; background-color: #ccc; width: 50px; text-align: right;'>Price</th>";
      $content .= "<th style='border: solid 1px #eee; background-color: #ccc; width: 50px; text-align: right;'>Qty</th>";
      $content .= "<th style='border: solid 1px #eee; background-color: #ccc; width: 100px; text-align: right;'>Total</th>";
      $content .= "</tr>";
      $content .= "</thead>";

      $total    = 0;

      $content .= "<tbody>";

      foreach ($orders as $order) {
            
         $content .= "<tr>";
         $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: left;'>".$order['product_name']." (Pack of ".$order['pack_size'].") </td>";
         $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>".$this->cart_model->numberToCurrency($order['price'])."</td>";
         $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>".$order['quantity']."</td>";
         $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>".$this->cart_model->numberToCurrency($order['price']*$order['quantity'])."</td>";
         $content .= "</tr>";

         $total += $order['price']*$order['quantity'];

      }

      $content .= "<tr>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;' colspan='3'>Subtotal</td>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>".$this->cart_model->numberToCurrency($total)."</td>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;' colspan='3'>Shipping & Handling</td>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>0</td>";
      $content .= "</tr>";

      // $content .= "<tr>";
      // $content .= "<td style='border: solid 1px #ccc; text-align: left;' colspan='3'>COD Charges</td>";
      // $content .= "<td style='border: solid 1px #ccc; text-align: right;'>0</td>";
      // $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;' colspan='3'>Discount</td>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>0</td>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;' colspan='3'>Grand Total</td>";
      $content .= "<td style='border: solid 1px #ccc; background-color: #eee; text-align: right;'>".$this->cart_model->numberToCurrency($total)."</td>";
      $content .= "</tr>";

      $content .= "</tbody>";
         
      $content .= "</table>"; 

      $address = $this->db->query("select * from order_buyer_address where id = ".$orders[0]['destination_address_id'])->row_array();

      $content .= "<div>";
      $content .= "<div style='width: 50%; text-align: left; font-size: 12px; display: inline-block; margin: 0 -2px; vertical-align: top;'>";
      $content .= "<div style='padding: 0 15px;'>";
      $content .= "<h3 style='font-size: 14px;'>Shipping To</h3>";   
      $content .= "<p style='margin:5px 0;'>".$address['name']."</p>";
      $content .= "<p style='margin:5px 0;'>".$address['address_line_1'].",</p>";
      if($address['address_line_2'] != ""){
         $content .= "<p style='margin:5px 0;'>".$address['address_line_2'].",</p>";
      }
      if($address['landmark'] != ""){
         $content .= "<p style='margin:5px 0;'>".$address['landmark'].",</p>";
      }
      $content .= "<p style='margin:5px 0;'>".$address['city']." - ".$address['pin'].",</p>";
      $content .= "<p style='margin:5px 0;'>".$address['state']." INDIA</p>";
      $content .= "<p style='margin:5px 0;'>Mobile : +91 ".$address['mobile']."</p>";
      $content .= "</div>";
      $content .= "</div>";   
      $content .= "<div style='width: 50%; text-align: left; font-size: 12px; display: inline-block; margin: 0 -2px; vertical-align: top;'>";
      $content .= "<div style='padding: 0 15px;'>";
      $content .= "<h3 style='font-size: 14px;'>Shipping Method</h3>";   
      $content .= "<p style='margin:5px 0;'>".$shipping['shipment']." (".$shipping['shipping_type'].")</p>";

      $content .= "<br />";

      $content .= "<h3 style='font-size: 14px;'>Payment Method</h3>";   
      $content .= "<p style='margin:5px 0;'>".$payment['payment']."</p>";

      $content .= "</div>"; 
      $content .= "</div>";   
      $content .= "</div>";

      $content .= '</div>';
      $content .= '</div>';

      $htmlTemplate = $this->master_template($content);

      $subject = SYSTEM_NAME." Order #".$order_group_id;
      $to      = $user['email'];
      $from    = SYSTEM_EMAIL;

      $this->do_email($htmlTemplate, $subject, $to, $from);
   }

   function send_welcome_email($user){
      
      $content = '';
      $content .= '<div style="text-align:center; padding:25px 0">';
      $content .= '<a href="'.FRONT_URL.'">';
      $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
      $content .= '</a>';
      $content .= '<h2 style="font-size: 24px;">Welcome '.$user['name'].'</h2>';
      $content .= '<p style="color: #999; font-size: 18px;">You have successfully registered on <a href="'.FRONT_URL.'" style="color:#35b3e7">'.SYSTEM_NAME.'</a>.</p>';
      $content .= '<p style="color: #999; font-size: 18px;">Explore our trending categories now!</p>';
      $content .= '<p><a href="'.FRONT_URL.'" style="margin:20px; display:inline-block; color:#fff; background:#35b3e7; padding:10px 15px; border-radius:5px; text-decoration:none;">Explore Now</a></p>';
      $content .= '</div>   ';

      $htmlTemplate = $this->master_template($content);

      $subject = "Welcome to ".SYSTEM_NAME;
      $to      = $user['email'];
      $from    = SYSTEM_EMAIL;

      $this->do_email($htmlTemplate, $subject, $to, $from);
   }

   function ask_the_expert_to_admin($data){

      $content = '';
      $content .= '<div style="text-align:center; padding:25px 15px">';
      $content .= '<a href="'.FRONT_URL.'">';
      $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
      $content .= '</a>';
      $content .= '<p style="text-align:left;font-size: 16px;">Dear Admin,</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">'.$data['full_name'].' has asked for,</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">'.$data['comments'].'</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">&nbsp;</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">Below are more details!</p>';
       $content .= "<table style='width: 100%; font-size: 12px;' cellpadding='10px' cellspacing='0'>";

      

      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; width: 30%; text-align: left; font-size: 12px;'>Category</th>";
      $content .= "<th style='border: solid 1px #eee; width: 70%; text-align: left; font-size: 12px;'>".$data['category']."</th>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; width: 30%; text-align: left; font-size: 12px;'>Product</th>";
      $content .= "<th style='border: solid 1px #eee; width: 70%; text-align: left; font-size: 12px;'>".$data['product']."</th>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; width: 30%; text-align: left; font-size: 12px;'>Company</th>";
      $content .= "<th style='border: solid 1px #eee; width: 70%; text-align: left; font-size: 12px;'>".$data['company']."</th>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; width: 30%; text-align: left; font-size: 12px;'>Contact</th>";
      $content .= "<th style='border: solid 1px #eee; width: 70%; text-align: left; font-size: 12px;'>".$data['contact_no']."</th>";
      $content .= "</tr>";

      $content .= "<tr>";
      $content .= "<th style='border: solid 1px #eee; width: 30%; text-align: left; font-size: 12px;'>Email</th>";
      $content .= "<th style='border: solid 1px #eee; width: 70%; text-align: left; font-size: 12px;'>".$data['email']."</th>";
      $content .= "</tr>";
      
      $content .= '</table>   '; 
      
      $content .= '</div>   ';


      $files = array();


      if($data['attachments'] != ""){
         $attachments = explode(",", $data['attachments']);
         foreach ($attachments as $attachment) {
            $file = FCPATH."assets/uploads/inquiries/".$attachment;
            array_push($files, $file);
         }
      }

      $htmlTemplate = $this->master_template($content);

      $subject = "New Advice Request";
      $to      = SYSTEM_EMAIL;
      $from    = SYSTEM_EMAIL;

      $this->do_email($htmlTemplate, $subject, $to, $from, $files);
   }

   function ask_the_expert_to_user($data){

      $content = '';
      $content .= '<div style="text-align:center; padding:25px 15px">';
      $content .= '<a href="'.FRONT_URL.'">';
      $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
      $content .= '</a>';
      $content .= '<p style="text-align:left;font-size: 14px;">Dear '.$data['full_name'].',</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">We have received your quries about '.$data['product'].' as <br /><br /> '.$data['comments'].'</p>';
      $content .= '<p style="text-align:left;font-size: 12px;">Our experts will be in touch with you sortly!</p>';
      $content .= '</div>   ';

      $files = array();

      if($data['attachments'] != ""){
         $attachments = explode(",", $data['attachments']);
         foreach ($attachments as $attachment) {
            $file = FCPATH."assets/uploads/inquiries/".$attachment;
            array_push($files, $file);
         }
      }

      $htmlTemplate = $this->master_template($content);

      $subject = SYSTEM_NAME." | Advice Request";
      $to      = $data['email'];
      $from    = SYSTEM_EMAIL;

      $this->do_email($htmlTemplate, $subject, $to, $from, $files);
   }

   function send_verification_email_seller($user){

         $content = '';
         $content .= '<div style="text-align: center;">';
         $content .= '<a href="'.FRONT_URL.'">';
         $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
         $content .= '</a>';
         $content .= '<h2 style="font-size: 24px;">Welcome '.$user['first_name'].' '.$user['last_name'].'</h2>';
         $content .= '<p style="color: #999; font-size: 18px;">You have sucsessfully registered on <a href="'.BASE_URL.'" style="color: #5e72e4">'.SYSTEM_NAME.'</a>.</p>';
         $content .= '<p style="color: #999; font-size: 18px;">Click on below link to activate your account.</p>';
         $content .= '<p><a href="'.BASE_URL.'vendor/verify_account/'.$user['account_token'].'" style="margin: 20px; display: inline-block; color: #fff; background: #5e72e4; padding: 10px 15px; border-radius: 5px;">Activate Now</a></p>';
         $content .= '</div>   ';
         $htmlTemplate = $this->master_template($content);
         $subject = "Verify Account - ".SYSTEM_NAME;
         $to      = $user['email'];
         $from    = SYSTEM_EMAIL;

         $this->do_email($htmlTemplate, $subject, $to, $from);
   }

   function send_verification_email($user){

         $content = '';
         $content .= '<div style="text-align: center;">';
         $content .= '<a href="'.FRONT_URL.'">';
         $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
         $content .= '</a>';
         $content .= '<h2 style="font-size: 24px;">Welcome '.$user['name'].'</h2>';
         $content .= '<p style="color: #999; font-size: 18px;">You have sucsessfully registered on <a href="'.BASE_URL.'" style="color: #5e72e4">'.SYSTEM_NAME.'</a>.</p>';
         $content .= '<p style="color: #999; font-size: 18px;">Click on below link to activate your account.</p>';
         $content .= '<p><a href="'.BASE_URL.'users_api/verify_account/'.$user['account_token'].'" style="margin: 20px; display: inline-block; color: #fff; background: #5e72e4; padding: 10px 15px; border-radius: 5px;">Activate Now</a></p>';
         $content .= '</div>   ';
         $htmlTemplate = $this->master_template($content);
         $subject = "Verify Account - ".SYSTEM_NAME;
         $to      = $user['email'];
         $from    = SYSTEM_EMAIL;

         $this->do_email($htmlTemplate, $subject, $to, $from);
         
         return true;  
   }
     
   function send_forgot_password_email($data){

         $content = '';
         $content .= '<div style="text-align: center;">';
         $content .= '<a href="'.FRONT_URL.'">';
         $content .= '<img style="width:250px;" src="'.SYSTEM_LOGO_EMAIL.'">';
         $content .= '</a>';
         $content .= '<h2 style="font-size: 24px;">Hello User, </h2>';
         $content .= '<p style="color: #999; font-size: 18px;">Your password resat request has been successfully proceed.  Please use below password to login account.</p>';
         $content .= '<p style="color: #999; font-size: 18px;">Password: '.$data['password'].'</p>';
         $content .= '<p><b>Note : Please change password after login</b></p>';
         $content .= '</div>   ';
         $htmlTemplate = $this->master_template($content);
         $subject = "Forgot Password - ".SYSTEM_NAME;
         $to      = $data['email'];
         $from    = SYSTEM_EMAIL;

         $this->do_email($htmlTemplate, $subject, $to, $from);
         
         return true;  
   }

   function do_email($msg = NULL, $sub = NULL, $to = NULL, $from = NULL, $attachments = NULL){
      
      $this->load->library('email');
      
      $config['protocol'] = "smtp";
      $config['smtp_host'] = "mail.digitalfriend.co.in";
      $config['smtp_port'] = 465;
      $config['smtp_user'] = SYSTEM_EMAIL;
      $config['smtp_pass'] = "admin@123";
      $config['charset'] = "utf-8";
      $config['mailtype'] = "html";
      $config['newline'] = "\r\n";
      $config['smtp_debug'] = 4;
      $this->email->initialize($config);
      
      $from = SYSTEM_EMAIL;
      $this->email->from($from, SYSTEM_NAME);
      $this->email->to($to);
      
      $this->email->subject($sub);
      $this->email->message($msg);

      if($attachments != null) {
         foreach ($attachments as $attachment) {
            $this->email->attach($attachment);
         }
      }

      $sent = $this->email->send();
      
      if($sent){
         return 1;
      } else {
         return 0;
      }
   }

}
