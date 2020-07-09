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
						if(isset($post['country_code']) && $post['country_code'] != ""){

							$where = "mobile=".$post['mobile']." or email='".$post['email']."'";
							$this->db->where($where);
							$checkAccount = $this->db->get_where("user")->row_array();
							if(empty($checkAccount)){
									$user = array(
										"name" => $post['name'],
										"email" => $post['email'],
										"mobile" => $post['mobile'],
										"password" => sha1($post['password']),
										"country_code" => $post['country_code'],
										"timestamp" => time(),
										"status"  => 0,
										"type"  => 0,
										"account_token" => ''
									);
									$insert = $this->db->insert("user", $user);
									if($insert){
										$user_id = $this->db->insert_id();
										$result = $this->crud_model->validate_user($user_id);
										$mobile  = $post['mobile'];
										//$otp     = mt_rand(100000, 999999);
										$otp     = '999999';
										$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

										if($result){
											//$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to validate your account.";
											//$sendToUser = $this->common_model->sendMessage($mobile, $text);

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
							}else{
								$response['success'] = 0;
								$response['message'] = "This mobile or email is already associate with another account.";	
							}
						}
						else{
							$response['success'] = 0;
							$response['message'] = "Country code can not be blank.";
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
	public function login(){

		$post = $this->input->post();
		if(isset($post['mobile']) && $post['mobile'] != ""){
			if(isset($post['password']) && $post['password'] != ""){
				if(isset($post['country_code']) && $post['country_code'] != ""){

					$checkUser = $this->db->get_where("user", array("mobile" => $post['mobile'], "password" => sha1($post['password']),"status"=> 1))->row_array();
					if(!empty($checkUser)){

						$user = $this->common_model->getSingleUserById($checkUser['user_id']);

						$response['success'] = 1;
						$response['message'] = "You have logged in successfully.";
						$response['user']    = $user;

					}
					else{
						$response['success'] = 0;
						$response['message'] = "Incorrect mobile or password. Please try again.";
					}
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Country code can not be blank.";	
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

		echo json_encode($response);
	}
	public function forgotPassword(){

		$post = $this->input->post();
		if($post['mobile']!=''){
			if($post['country_code'] != ""){
				$getuser = $this->db->get_where("user", array("mobile" => $post['mobile']))->row_array();
				if(!empty($getuser)){
					$user_id = $getuser['user_id'];
					$mobile  = $getuser['mobile'];
					//$otp     = mt_rand(100000, 999999);
					$otp     = '999999';
					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);
					if($result){
						//$text = "The OTP for your connflix account is ".$otp;
						//$sendToUser = $this->common_model->sendMessage($mobile, $text);
						$response['success'] = $result['success'];
						$response['message'] = "OTP has been sent registed  mobile number.";
					}
					else{
						$response['success'] = 0;
						$response['message'] = "Opps.. Something went wrong. Please try again.";
					}
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Mobile number not found.";
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Country code can not be blank.";
			}
		}
		else{
			$response['success'] = 0;
			$response['message'] = "Mobile number can not be blank.";
		}
		echo json_encode($response);
	}

	public function changePassword (){

		$post = $this->input->post();
		if($post['user_id'] != ""){
				if($post['password'] != ""){
					$this->db->where("user_id" , $post['user_id']);
					$user = $this->db->get("user")->row_array();

					if(!empty($user)){
							if($user['password'] != sha1($post['password'])){

								$this->db->where("user_id" , $post['user_id']);
								$update = $this->db->update("user", array("password" => sha1($post['password'])));

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
						$response['message'] = "user not found";	
					}

				}else{
					$response['success'] = 0;
					$response['message'] = "Please Enter New Password";
				}
		}else{
			$response['success'] = 0;
			$response['message'] = "Please provide user id";
		}

		echo json_encode($response);
	}
	// public function resendOtp(){
	// 	$post = $this->input->post();

	// 		if(isset($post['mobile']) && $post['mobile'] != ""){
	// 			$checkMobile = $this->db->get_where("user", array("mobile" => $post['mobile']))->row_array();

	// 			if(!empty($checkMobile)){

	// 				$user_id = $checkMobile['user_id'];
	// 				$mobile  = $checkMobile['mobile'];
	// 				//$otp     = mt_rand(100000, 999999);
	// 				$otp     = '999999';

	// 				$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

	// 				if($result['success'] == 1){
	// 					//$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
	// 					//$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);
	// 					$response['success'] = 1;
	// 					$response['message'] = "Otp has been sent.";

	// 				}
	// 				else{
	// 					$response['success'] = 0;
	// 					$response['message'] = "Opps.. Something went wrong. Please try again.";
	// 				}

	// 			}
	// 			else{

	// 				$response['success'] = 0;
	// 				$response['message'] = "Mobile number not found.";

	// 			}

	// 		}
	// 		else{
	// 			$response['success'] = 0;
	// 			$response['message'] = "Mobile can not be blank.";
	// 		}

	// 	echo json_encode($response);
	// }
	public function resendOtp(){
		$post = $this->input->post();

			if(isset($post['mobile']) && $post['mobile'] != ""){
				if($post['country_code'] != ""){
					$checkMobile = $this->db->get_where("user", array("mobile" => $post['mobile']))->row_array();

						if(!empty($checkMobile)){

							$user_id = $checkMobile['user_id'];
							$mobile  = $checkMobile['mobile'];
							//$otp     = mt_rand(100000, 999999);
							$otp     = '999999';

							$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

							if($result['success'] == 1){
								//$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
								//$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);
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
							$response['message'] = "Mobile number not found.";

						}
					}
				else{
					$response['success'] = 0;
					$response['message'] = "Country code can not be blank.";
				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Mobile can not be blank.";
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
					//$otp     = mt_rand(100000, 999999);
					$otp      =  '999999';

					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

					if($result['success'] == 1){

						//$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
						//$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);

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
				//$otp     = mt_rand(100000, 999999);
				$otp     = '999999';

				$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

				if($result['success'] == 1){

					//$text = "The OTP for your connflix account is ".$otp.". Please enter the OTP to login into your account.";
					//$sendToUser = $this->common_model->sendMessage($post['mobile'], $text);

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
	public function country_list(){
		$post = $this->input->post();
		if($post['page'] || !empty($post['page']) || $post['limit'] || !empty($post['limit'])){
			$curpage = $post['page'];
			$limit = $post['limit'];
			$search  = $post['q'];
		}else{
			$curpage = 1;
			$search  = $post['q'];
			$limit = 20;
		}

		$start      = ($curpage * $limit) - $limit;
		$users   	= $this->db->get('country');
		$totlerec   = $users->num_rows();
		$endpage    = ceil($totlerec/$limit);
		$startpage  = 1;
		$nextpage   = $curpage + 1;
		$prevpage   = $curpage - 1;

		if($search == ""){
			$DisplayLimit = " limit ".$start.",".$limit;
		}

		$cond = "";

		if($search != ""){
			$cond .= " and (name like '%".$search."%' or iso like '%".$search."%' or phonecode like '%".$search."%')";
		}

		$countries = $this->db->query("select SQL_CALC_FOUND_ROWS * from country where 1=1 ".$cond." order by id ASC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($countries)){
			foreach($countries as $country){

				$response['country'][$count]['id'] 		=  $country['id'];
				$response['country'][$count]['name'] 	= $country['nicename'];
				$response['country'][$count]['iso'] 	= $country['iso3']?$country['iso3']:'';
				$response['country'][$count]['code'] 	= $country['phonecode']?$country['phonecode']:'';
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No country found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
}
