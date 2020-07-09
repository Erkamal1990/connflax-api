<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_api extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('crud_model');
		$this->load->model('email_model');
		$this->load->model('common_model');
		$this->load->library('session');
		$this->load->library('email');

		$postJson = file_get_contents("php://input");
		if($this->common_model->checkjson($postJson)){
		     $_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST['from_app'] == "true") { 

			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
			}
		}
		/* Getting Access Token */
		//YzMxYjMyMzY0Y2UxOWNhOGZjZDE1MGE0MTdlY2NlNTg=
		$accessToken = base64_encode(md5("android"));
		$accessKey = $this->input->post("apiId");

		if (empty($accessKey)) {
			$response['success'] = 0;
			$response['message'] = "Failed to authenticate request.";
			echo json_encode($response);
			exit;
		} else {
			if ($accessKey != $accessToken) {
				$response['success'] = 0;
				$response['message'] = "Failed to authenticate request.";
				echo json_encode($response);
				exit;
			}
		}

		header("Access-Control-Allow-Headers: Authorization, Content-Type");
		header("Access-Control-Allow-Origin: *");
		header('content-type: application/json; charset=utf-8');
		
		date_default_timezone_set('Asia/Kolkata');
		$this->db->query('SET SESSION time_zone = "+05:30"');
		$this->db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
	}
	public function signUp(){
	
		$post = $this->input->post();
		if(isset($post['name']) && $post['name'] != ""){
			if(isset($post['email']) && $post['email'] != ""){
				if(isset($post['mobile']) && $post['mobile'] != ""){
					if(isset($post['password']) && $post['password'] != ""){

						$where = "mobile=".$post['mobile']." or email='".$post['email']."'";
						$this->db->where($where);
						$checkAccount = $this->db->get_where("user")->row_array();

						if(empty($checkAccount)){

							$checkEmail = $this->db->get_where("user", array("email" => $post['email']))->row_array();

							if(empty($checkEmail)){

								$user = array(
									"name" => $post['name'],
									"email" => $post['email'],
									"mobile" => $post['mobile'],
									"password" => sha1($post['password']),
									"timestamp" => time(),
									"status"  => 0,
									"type"  => 0,
									"account_token" => ''
								);

								$insert = $this->db->insert("user", $user);

								if($insert){

									$user_id = $this->db->insert_id();
									$result = $this->crud_model->validate_user($user_id);
									
									//$email  = $post['email'];
									//$result = $this->email_model->send_verification_email($user);
									$mobile  = $post['mobile'];
									$otp     = mt_rand(100000, 999999);
									$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

									if($result){
										$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to validate your account.";
										$sendToUser = $this->common_model->sendMessage($mobile, $text);

										$response['success'] = $result['success'];
										$response['user_id'] = $user_id;
										$response['message'] = "You have registed successfully. Please verify OTP code";
									}
									else{
										$response['success'] = $result['success'];
										$response['message'] = "Opps.. Something went wrong. Please try again.";
									}
								}
								else{
									$response['success'] = 0; 
									$response['message'] = "Opps.. Something went wrong. Please try again.";
								}

							}
							else{
								$response['success'] = 0;
								$response['message'] = "This email is already used.";
							}
						}else{
							$response['success'] = 0;
							$response['message'] = "This mobile or email is already associate with another account.";	
						}

					}
					else{
						$response['success'] = 0;
						$response['message'] = "Password can not be blank.";
					}
				}		
				else{
					$response['success'] = 0;
					$response['message'] = "Mobile can not be blank.";		
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Email can not be blank.";	
			}
		}
		else{
			$response['success'] = 0;
			$response['message'] = "Name can not be blank.";
		}

		echo json_encode($response);
	}
	public function verifyAccount(){

		$post = $this->input->post();

		if($post['account_token'] != ""){

			$this->db->where("account_token" , $post['account_token']);
			$user = $this->db->get("user")->row_array();

			if(!empty($user)){

				$this->db->where("account_token" , $post['account_token']);
				$update = $this->db->update("user", array("account_token" => "", "status" => 1));

				if($update){
					$response['success'] = 1;
					$response['message'] = "Your account has been verified successfully.";
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Opps.. Something went wrong. Please try again later.";	
				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Invalid or expired link";	
			}

		}else{
			$response['success'] = 0;
			$response['message'] = "Invalid or expired link";
		}

		echo json_encode($response);
	}
	public function loginWithOtp(){
		$post = $this->input->post();

		if($post['mobile'] != ""){
			
			$checkMobile = $this->db->query("select mobile,user_id from user where mobile=".$post['mobile']."")->row_array();

			if(empty($checkMobile)){
				
				$user = array(
					"name" => "",
					"email" => "",
					"mobile" => $post['mobile'],
					"password" => "",
					"timestamp" => time(),
					"status"  => 0
				);

				$insert = $this->db->insert("user", $user);

				if($insert){

					$user_id = $this->db->insert_id();
					$mobile  = $post['mobile'];
					$otp     = mt_rand(100000, 999999);

					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

					if($result['success'] == 1){

						$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
						$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);

						$response['success'] = $result['success'];
						$response['user_id'] = $user_id;
						$response['message'] = "Please verify OTP code.";
					}
					else{
						$response['success'] = $result['success'];
						$response['message'] = "Opps.. Something went wrong. Please try again.";
					}
				}
				else{
					$response['success'] = 0; 
					$response['message'] = "Opps.. Something went wrong. Please try again.";
				}
			}
			else{

				$user_id = $checkMobile['user_id'];
				$mobile  = $checkMobile['mobile'];
				$otp     = mt_rand(100000, 999999);

				$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

				if($result['success'] == 1){

					$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
					$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);

					$response['success'] = 1;
					$response['user_id'] = $checkMobile['user_id'];
					$response['message'] = "Please verify OTP code.";
				}else{
					$response['success'] = 0;
					$response['message'] = "Opps.. Something went wrong. Please try again.";
				}
			}
		}else{
			$response['message'] = "Please provide mobile number.";
			$response['success'] = 0;
		}

		echo json_encode($response);			
	}

	public function verifyOtp(){
		$post = $this->input->post();
			if(isset($post['otp']) && $post['otp'] != ""){

				$this->db->where("otp_code", $post['otp']);
				$this->db->where("is_verified", 1);
				$checkOtp = $this->db->get_where("user_otp")->row_array();
				
				if(!empty($checkOtp)){

						$this->db->where("otp_id", $checkOtp['otp_id']);
						$update = $this->db->update("user_otp", array("is_verified" => 0));
						if($update){
							
							$user = $this->common_model->getSingleUserById($checkOtp['user_id']);
							
							if($user['status'] != 1){
								$this->email_model->send_welcome_email($user);
							}

							$this->db->where("user_id", $checkOtp['user_id']);
							$update = $this->db->update("user", array("status" => 1));


							$userupdated = $this->common_model->getSingleUserById($checkOtp['user_id']);

							$response['success'] = 1;
							$response['message'] = "Thank you. Your account has been verified successfully.";
							$response['user'] = $userupdated;
 
						}
						else{
							$response['success'] = 0;
							$response['message'] = "Opps.. Something went wrong. Please try again.";
 						}
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Incorrect or expired OTP code.";
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Otp code can not be blank.";
			}

		echo json_encode($response);
	}
	public function userById(){
		$post = $this->input->post();

		if($post['user_id'] != ''){
			$response['user'] = $this->common_model->getSingleUserById($post['user_id']);
			$response['success'] = 1;
		}

		echo json_encode($response);
	}

	public function login(){

		$post = $this->input->post();
		if(isset($post['email']) && $post['email'] != ""){
			if(isset($post['password']) && $post['password'] != ""){

				$checkUser = $this->db->get_where("user", array("email" => $post['email'], "password" => sha1($post['password']),"status"=> 1))->row_array();
				if(!empty($checkUser)){

					$user = $this->common_model->getSingleUserById($checkUser['user_id']);

					$response['success'] = 1;
					$response['message'] = "You have logged in successfully.";
					$response['user']    = $user;

				}
				else{
					$response['success'] = 0;
					$response['message'] = "Incorrect email or password. Please try again.";
				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Password can not be blank.";	
			}
		}
		else{
			$response['success'] = 0;
			$response['message'] = "Email can not be blank.";
		}

		echo json_encode($response);
	}

	public function resendOtp(){

		$post = $this->input->post();

			if(isset($post['mobile']) && $post['mobile'] != ""){
				$checkMobile = $this->db->get_where("user", array("mobile" => $post['mobile']))->row_array();

				if(!empty($checkMobile)){

					$user_id = $checkMobile['user_id'];
					$mobile  = $checkMobile['mobile'];
					$otp     = mt_rand(100000, 999999);

					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

					if($result['success'] == 1){
						$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
						$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);
						$response['success'] = 1;
						$response['message'] = "Otp has been sent.";

					}
					else{
						$response['success'] = 0;
						$response['message'] = "Opps.. Something went wrong. Please try again.";
					}

				}
				else{

					$response['success'] = 0;
					$response['message'] = "Mobile can not be blank.";

				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Mobile can not be blank.";
			}

		echo json_encode($response);
	}
	public function profileUpdate(){

		$post = $this->input->post();	

		if(isset($post['user_id']) && $post['user_id'] != ""){

			if(isset($post['name']) && $post['name'] != ""){
				if(isset($post['email']) && $post['email'] != ""){
					
					$checkUser = $this->common_model->getSingleUserById($post['user_id']);

					if($checkUser){

						$this->db->where("user_id != ", $post['user_id']);
						$checkEmail = $this->db->get_where("user", array("email" => $post['email']))->row_array();

						if(empty($checkEmail)){

							$mobile = ($post['mobile']) ? $post['mobile'] : "";
							
							$user = array(
								"name" => $post['name'],
								"email" => $post['email'],
								"mobile" => $mobile,
							);

							$this->db->where("user_id", $post['user_id']);
							$update = $this->db->update("user", $user);

							if($update){

								$user = $this->common_model->getSingleUserById($post['user_id']);

								$response['success'] = 1;
								$response['message'] = "Details have been updated.";
								$response['user'] = $user;

							}
							else{
								$response['success'] = 0; 
								$response['message'] = "Opps.. Something went wrong. Please try again.";
							}

						}
						else{
							$response['success'] = 0;
							$response['message'] = "This email is already used.";
						}

					}
					else{
						$response['success'] = 0;
						$response['message'] = "User not found";
					}

				}
				else{
					$response['success'] = 0;
					$response['message'] = "Email can not be blank.";	
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Name can not be blank.";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "User ID can not be blank";
		}

		echo json_encode($response);
	}
	public function changePassword(){

		$post = $this->input->post();
		if($post['user_id'] != ""){
			if($post['old_password'] != ""){
				if($post['new_password'] != ""){
					$this->db->where("user_id" , $post['user_id']);
					$user = $this->db->get("user")->row_array();

					if(!empty($user)){
						if($user['password'] == sha1($post['old_password'])){
							if($user['password'] != sha1($post['new_password'])){

								$this->db->where("user_id" , $post['user_id']);
								$update = $this->db->update("user", array("password" => sha1($post['new_password'])));

								if($update){
									$response['success'] = 1;
									$response['message'] = "Your account password has been changed.";
								}
								else{
									$response['success'] = 0;
									$response['message'] = "Opps.. Something went wrong. Please try again later.";	
								}
							}
							else{
								$response['success'] = 0;
								$response['message'] = "Current password and new password is same please try another";	
							}
						}
						else{
							$response['success'] = 0;
							$response['message'] = "Please provide old password";	
						}
					}
					else{
						$response['success'] = 0;
						$response['message'] = "user not found";	
					}

				}else{
					$response['success'] = 0;
					$response['message'] = "Please Enter New Password";
				}
			}else{
				$response['success'] = 0;
				$response['message'] = "Please Enter Old Password";
			}
		}else{
			$response['success'] = 0;
			$response['message'] = "Please provide user id";
		}

		echo json_encode($response);
	}

	public function forgotPassword(){

		$post = $this->input->post();
		if($post['email']!=''){
			    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			    $charactersLength = strlen($characters);
			    $randomString = '';
			    $length = 10;
			    for ($i = 0; $i < $length; $i++) {
			        $randomString .= $characters[rand(0, $charactersLength - 1)];
			    }
			$email  = $post['email'];
			$password  = $randomString;
			$passdata = array(
				'email' => $email,
				'password' => $password,
			 );
			$this->db->where("email" , $post['email']);
			$update = $this->db->update("user", array("password" => sha1($password)));

			$result = $this->email_model->send_forgot_password_email($passdata);
			if($result){
				$response['success'] = 1;
				$response['message'] = "Your password has been sent registed  email address. Please check your email";
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Opps.. Something went wrong. Please try again.";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "Email can not be blank.";
		}
	}
	public function socialLogin(){

		/** Login type **/

		/**
		0 : Regular
		1 : Facebook
		2 : Google
		**/

		$post = $this->input->post();

		if(isset($post['email']) && $post['email'] != ""){
			if(isset($post['name']) && $post['name'] != ""){

				if(isset($post['login_type']) && $post['login_type'] != ""){

					$checkUser = $this->db->get_where("users", array("email" => $post['email']))->row_array();

					if(!empty($checkUser)){

						if($checkUser['mobile'] == ""){
							
							$data['mobile'] = $post['mobile'];

							$this->db->where("user_id",$checkUser['user_id']);
							$insert = $this->db->update("users",$data);
						}

						$user = $this->common_model->getSingleUserById($checkUser['user_id']);

						$response['success'] = 1;
						$response['message'] = "";
						$response['user']    = $user;
					}
					else{

						$data['name'] = $post['name'];
						$data['email'] = $post['email'];
						$data['mobile'] = $post['mobile'];
						$data['timestamp'] = $post['timestamp'];
						$data['is_active'] = 1;
						$data['login_type'] = $post['login_type'];
						$data['profile_pic'] = ($post['profile_pic']) ? $post['profile_pic'] : "";

						$insert = $this->db->insert("users", $data);

						if($insert){	

							$user_id = $this->db->insert_id();

							$user = $this->common_model->getSingleUserById($user_id);

							$response['success'] = 1;
							$response['message'] = "";
							$response['user'] = $user;

						}
						else{
							$response['success'] = 0;
							$response['message'] = "Opps.. Something went wrong. Please try again.";
						}
					}
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Login type can not be blank.";
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Name can not be blank.";
			}
		}
		else{	
			$response['success'] = 0;
			$response['message'] = "Email can not be blank.";
		}

		echo json_encode($response);
	}
	public function users_list(){

		$post = $this->input->post();

		if($post['page'] || !empty($post['page']) || $post['limit'] || !empty($post['limit'])){
			$curpage = $post['page'];
			$limit = $post['limit'];
			$search  = $post['q'];
		}else{
			$curpage = 1;
			$search  = '';
			$limit = 30;
		}

		$start      = ($curpage * $limit) - $limit;
		$users   	= $this->db->get('users');
		$totlerec   = $users->num_rows();
		$endpage    = ceil($totlerec/$limit);
		$startpage  = 1;
		$nextpage   = $curpage + 1;
		$prevpage   = $curpage - 1;

		$search_string = $this->input->post('search_string');
		$search_date = $this->input->post('search_date');

		if($search_string == ""){
			$DisplayLimit = " limit ".$start.",".$limit;
		}

		$cond = "";

		if($post['status'] != ""){
			$cond .= " and is_active=".$post['status']."";
		}

		if($search_string != ""){
			$cond .= " and (name like '%".$search_string."%' or email like '%".$search_string."%' or mobile like '%".$search_string."%' or waptag like '%".$search_string."%') ";
		}

		if($search_date != ""){
				
			$explode_date = explode(" - ",$search_date);

			if(!empty($explode_date)){
				$start_date = $explode_date[0];
				$end_date = $explode_date[1];
			}else{
				$start_date = "";
				$end_date = "";
			}

			$cond .= " AND ( timestamp >= ".strtotime($start_date)." AND timestamp <= ".strtotime($end_date)." )";
		}

		$users = $this->db->query("select SQL_CALC_FOUND_ROWS * from users where 1=1 ".$cond." order by user_id DESC".$DisplayLimit)->result_array();

		if($search_string == ""){
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;
		}

		$count = 0;
		if(!empty($users)){
			foreach($users as $user){

				$response['users'][$count]['user_id'] = ($user['user_id']) ? $user['user_id'] : "";
				$response['users'][$count]['name'] = ($user['name']) ? $user['name'] : "";
				$response['users'][$count]['email'] = ($user['email']) ? $user['email'] : "";
				$response['users'][$count]['mobile'] = ($user['mobile']) ? $user['mobile'] : "";
				$response['users'][$count]['waptag'] = ($user['waptag']) ? $user['waptag'] : "";

				if($user['is_active'] == 1){
					$status = "Approved";
				}else{
					$status = "Block";
				}

				$response['users'][$count]['status'] = ($status) ? $status : "";

				$response['users'][$count]['profile_pic'] = ($user['profile_pic']) ? base_url()."assets/uploads/users_pic/".$user['profile_pic'] : base_url()."assets/images/placeholder.png";
				$response['users'][$count]['company'] = ($user['company']) ? $user['company'] : "";
				$response['users'][$count]['timestamp'] = ($user['timestamp']) ? date("d M Y",$user['timestamp']) : "";

				$count++;
			}

			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No any users data found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function profile(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'] != ""){

			$check = $this->db->get_where("user", array("user_id" => $post['user_id']))->row_array();

			if(!empty($check)){

				$user = $this->common_model->getSingleUserById($post['user_id']);

				$response['success'] = 1;
				$response['message'] = "";
				$response['user']    = $user;

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Account not found. Please contact administration.";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "User ID can not be blank.";
		}

		echo json_encode($response);
	}

	public function changeMobile(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'] != ""){

			if(isset($post['mobile']) && $post['mobile'] != ""){

				$this->db->where("user_id", $post['user_id']);
				$update = $this->db->update("users", array("mobile" => $post['mobile']));

				if($update){

					$user = $this->common_model->getSingleUserById($post['user_id']);

					$response['success'] = 1;
					$response['message'] = "";
					$response['user'] = $user;

				}
				else{
					$response['success'] = 0;
					$response['message'] = "Opps.. Something went wrong. Please try again.";
				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Mobile Can not be blank";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "User ID can not be blank.";
		}
		
		echo json_encode($response);
	}
}
