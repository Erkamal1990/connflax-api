<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_api extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('crud_model');
		$this->load->model('email_model');
		$this->load->library('session');
		$this->load->library('email');
	}


	public function updateToken(){

		$post = $this->input->post();

		if(isset($post['user_id'])  && $post['user_id'] != ""){

			if(isset($post['token'])  && $post['token'] != ""){

				$check = $this->db->get_where("fcm_tokens", array("user_id" => $post['user_id']))->row_array();

				$data = array(
					"token" => $post['token'],
					"user_id" => $post['user_id'],
					"type" => $post['type'],
					"timestamp" => time()
				);

				if(!empty($check)){
					$this->db->where("token_id",$check['token_id']);
					$save = $this->db->update("fcm_tokens", $data);
				}
				else{
					$save = $this->db->insert("fcm_tokens", $data);
				}
				if($save){
					$response['success'] = 1;
					$response['message'] = "Token successfully updated";
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Opps.. Something went wrong.";
				}

			}	
			else{

				$response['success'] = 0;
				$response['message'] = "Invalid Token"; 

			}

		}
		else{

			$response['success'] = 0;
			$response['message'] = "Invalid User ID";

		}

		echo json_encode($response);
	}

	public function verifyOtp(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'] != ""){
			if(isset($post['otp']) && $post['otp'] != ""){

				$this->db->where("timestamp >", strtotime("-15 minutes"));
				$this->db->where("user_id", $post['user_id']);
				$this->db->order_by("otp_id");
				$this->db->where("is_verified != ", 1);
				$checkOtp = $this->db->get_where("user_otp")->row_array();

				if(!empty($checkOtp)){
					// if($checkOtp['otp_code'] == $post['otp'] || $post['otp'] == "027952"){
					if($checkOtp['otp_code'] == $post['otp']){

						$this->db->where("otp_id", $checkOtp['otp_id']);
						$update = $this->db->update("user_otp", array("is_verified" => 1));

						if($update){
							
							$user = $this->common_model->getSingleUserById($post['user_id']);
							
							if($user['is_active'] != 1){
								$this->email_model->send_welcome_email($user);
							}

							$this->db->where("user_id", $checkOtp['user_id']);
							$update = $this->db->update("users", array("is_active" => 1));

							$response['success'] = 1;
							$response['message'] = "Thank you.";
							$response['user'] = $user;
 
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
					$response['message'] = "Incorrect or expired OTP code.";
				}
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Otp code can not be blank.";
			}
		}
		else{
			$response['success'] = 0;
			$response['message'] = "User can not be blank.";
		}

		echo json_encode($response);
	}

	public function verify_account(){

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
	
	public function userById(){
		$post = $this->input->post();

		if($post['user_id'] != ''){
			$response['user'] = $this->common_model->getSingleUserById($post['user_id']);
			$response['success'] = 1;
		}

		echo json_encode($response);
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
									"account_token" => md5(time()."-".$post['email'])
								);

								//$insert = $this->db->insert("user", $user);
								$insert = 1;

								if($insert){

									$user_id = $this->db->insert_id();
									$result = $this->crud_model->validate_user($user_id);
									
									$email  = $post['email'];

									$result = $this->email_model->send_verification_email($user);

									if($result){

										$response['success'] = 1;
										$response['user_id'] = $user_id;
										$response['message'] = "You have registed successfully. Please verify your email";
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

	public function signInWithMobile(){
		$post = $this->input->post();

		if($post['mobile'] != ""){
			
			$checkMobile = $this->db->query("select mobile,user_id from users where mobile=".$post['mobile']."")->row_array();

			if(empty($checkMobile)){
				
				$user = array(
					"name" => "",
					"email" => "",
					"mobile" => $post['mobile'],
					"password" => "",
					"waptag" => "",
					"company" => "",
					"gst_no" => "",
					"timestamp" => "",
					"profile_pic"=>"",
					"notification_active"=>1,
					"is_active"  => 0
				);

				$insert = $this->db->insert("users", $user);

				if($insert){

					$user_id = $this->db->insert_id();
					$mobile  = $post['mobile'];
					$otp     = mt_rand(100000, 999999);

					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

					if($result['success'] == 1){

						$text = "The OTP for your Aqualogy account is ".$otp.". Please enter the OTP to login into your account.";
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

					$text = "The OTP for your Aqualogy account is ".$otp.". Please enter the OTP to login into your account.";
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

	public function login(){

		$post = $this->input->post();

		if(isset($post['email']) && $post['email'] != ""){
			if(isset($post['password']) && $post['password'] != ""){

				$checkUser = $this->db->get_where("users", array("email" => $post['email'], "password" => $post['password']))->row_array();

				if(!empty($checkUser)){

					$user = $this->common_model->getSingleUserById($checkUser['user_id']);

					$response['success'] = 1;
					$response['message'] = "";
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

	public function social_login(){

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

	public function profile(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'] != ""){

			$check = $this->db->get_where("users", array("user_id" => $post['user_id']))->row_array();

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

	public function profileUpdate(){

		$post = $this->input->post();	

		if(isset($post['user_id']) && $post['user_id'] != ""){

			if(isset($post['name']) && $post['name'] != ""){
				if(isset($post['email']) && $post['email'] != ""){
					
								
					$checkUser = $this->common_model->getSingleUserById($post['user_id']);

					if($checkUser){

						$this->db->where("user_id != ", $post['user_id']);
						$checkEmail = $this->db->get_where("users", array("email" => $post['email']))->row_array();

						if(empty($checkEmail)){

							$mobile = ($post['mobile']) ? $post['mobile'] : "";
							
							$user = array(
								"name" => $post['name'],
								"email" => $post['email'],
								"company" => $post['company'],
								"gst_no" => $post['gst_no'],
								"waptag" => $post['waptag'],
								"company" => $post['company'],
								"gst_no" => $post['gst_no'],
								"state_id" => $post['state_id'],
								"city_id" => $post['city_id'],
								"mobile" => $mobile,
							);


							$this->db->where("user_id", $post['user_id']);
							$update = $this->db->update("users", $user);

							if($update){

								$user = $this->common_model->getSingleUserById($post['user_id']);

								$response['success'] = 1;
								$response['message'] = "Details have been saved.";
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

	public function resendOtp(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'] != ""){

			if(isset($post['mobile']) && $post['mobile'] != ""){


				if(strtolower($post['checkMobile']) == "yes"){

					$this->db->where("user_id != ", $post['user_id']);
					$checkMobile = $this->db->get_where("users", array("mobile" => $post['mobile']))->row_array();

					if(empty($checkMobile)){

						$user_id = $checkMobile['user_id'];
						$mobile  = $checkMobile['mobile'];
						$otp     = mt_rand(100000, 999999);

						$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

						if($result['success'] == 1){

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
						$response['message'] = "This mobile is already used.";

					}

				}
				else{

					$user_id = $post['user_id'];
					$mobile  = $post['mobile'];
					$otp     = mt_rand(100000, 999999);

					$result = $this->common_model->generate_otp($user_id, $otp, $mobile);

					if($result['success'] == 1){

						$response['success'] = 1;
						$response['message'] = "Otp has been sent.";

					}
					else{
						$response['success'] = 0;
						$response['message'] = "Opps.. Something went wrong. Please try again.";
					}

				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Mobile can not be blank.";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "User ID can not be blank";
		}

		echo json_encode($response);
	}

	public function address($action = NULL){

		$post = $this->input->post();

		$actions = array("list","save","delete","set_as_default","details");

		if(in_array($action, $actions)){

			if($action == "list"){

				$response['address'] = array();

				if(isset($post['user_id']) && $post['user_id'] != ""){

					$this->db->where("user_id", $post['user_id']);
					$this->db->order_by("address_id", "desc");
					$userAddresses = $this->db->get("user_addresses")->result_array();

					if(!empty($userAddresses)){
						$i = 0;
						foreach ($userAddresses as $address) {


							if(is_numeric($address['city'])){
								$this->db->where("id",$address['city']);
							    $city = $this->db->get("cities")->row()->name;
							}else{
								$city = $address['city'];
							}					
							

							if(is_numeric($address['state'])){
								$this->db->where("id",$address['state']);
								$state = $this->db->get("states")->row()->name;
							}else{
								$state = $address['state'];
							}
								
							$response['address'][$i]['address_id'] = ($address['address_id']) ? $address['address_id'] : "";
							$response['address'][$i]['name'] 	   = ($address['name']) ? $address['name'] : "";
							$response['address'][$i]['mobile']     = ($address['mobile']) ? $address['mobile'] : "";
							$response['address'][$i]['address_1']  = ($address['address_1']) ? $address['address_1'] : "";
							$response['address'][$i]['address_2']  = ($address['address_2']) ? $address['address_2'] : "";
							$response['address'][$i]['landmark']   = ($address['landmark']) ? $address['landmark'] : "";
							$response['address'][$i]['city'] 	   = ($city) ? $city : "";
							$response['address'][$i]['state'] 	   = ($state) ? $state : "";

							$response['address'][$i]['city_id']    = ($address['city']) ? $address['city'] : "";
							$response['address'][$i]['state_id']   = ($address['state']) ? $address['state'] : "";

							$response['address'][$i]['pincode']    = ($address['pincode']) ? $address['pincode'] : "";
							$response['address'][$i]['timestamp']  = ($address['timestamp']) ? $address['timestamp'] : "";
							$response['address'][$i]['user_id']    = ($address['user_id']) ? $address['user_id'] : "";
							$response['address'][$i]['type']       = ($address['type']) ? $address['type'] : "";

							if(count($userAddresses) > 1){
								$response['address'][$i]['is_default'] = ($address['is_default']) ? $address['is_default'] : 0;
							}else{
								$response['address'][$i]['is_default'] = 1;
							}
							

							$i++;

						}

						$response['success'] = 1;
						$response['message'] = "";
 
					}
					else{
						$response['success'] = 0;
						$response['message'] = "No saved addresses.";	
					}

				}
				else{
					$response['success'] = 0;
					$response['message'] = "User ID can not be blank.";
 				}
			}

			if($action == "save"){
				if(isset($post['user_id']) && $post['user_id'] != ""){	
					if(isset($post['name']) && $post['name'] != ""){
						if(isset($post['mobile']) && $post['mobile'] != ""){
							if(isset($post['address_1']) && $post['address_1'] != ""){
								if(isset($post['address_2']) && $post['address_2'] != ""){
									if(isset($post['pincode']) && $post['pincode'] != ""){
										if(isset($post['city']) && $post['city'] != ""){
											if(isset($post['state']) && $post['state'] != ""){
												if(isset($post['type']) && $post['type'] != ""){

												    $this->db->where("id",$post['city']);
												    $city = $this->db->get("cities")->row()->name;

												    $this->db->where("id",$post['state']);
												    $state = $this->db->get("states")->row()->name;

													$address = array(
														"name" => $post['name'],
														"user_id" => $post['user_id'],
														"mobile" => $post['mobile'],
														"address_1" => $post['address_1'],
														"address_2" => $post['address_2'],
														"pincode"  => $post['pincode'],
														"city"	=> $city,
														"state" => $state,
														"landmark" => $post['landmark'],
														"type" => $post['type']
													);

													if(isset($post['address_id']) && $post['address_id'] != ""){
														$this->db->where("address_id", $post['address_id']);
														$save = $this->db->update("user_addresses", $address);
													}
													else{
														$address['timestamp'] = time();
														$save = $this->db->insert("user_addresses", $address);
													}

													if($save){
														$response['success'] = 1;
														$response['message'] = "Address has been saved.";	
													}
													else{
														$response['success'] = 0;
														$response['message'] = "Opps.. Something went wrong. Please try again.";
													}
												} 
												else{
													$response['success'] = 0;
													$response['message'] = "Type can not be blank.";
												}
											}
											else{
												$response['success'] = 0;
												$response['message'] = "State can not be blank.";
											}
										}	
										else{
											$response['success'] = 0;
											$response['message'] = "City can not be blank.";
										}
									}
									else{
										$response['success'] = 0;
										$response['message'] = "Pincode can not be blank.";
									}
								}
								else{
									$response['success'] = 0;
									$response['message'] = "Address 2 can not be blank.";
								}
							}	
							else{
								$response['success'] = 0;
								$response['message'] = "Address 1 can not be blank.";
							}
						}	
						else{
							$response['success'] = 0;
							$response['message'] = "Mobile can not be blank.";
						}
					}
					else{
						$response['success'] = 0;
						$response['message'] = "Name can not be blank.";
					}
				}
				else{
					$response['success'] = 0;
					$response['message'] = "user id can not be blank.";
				}
			}

			if($action == "delete"){

				if(isset($post['address_id']) && $post['address_id']){

					$this->db->where("address_id", $post['address_id']);
					$delete = $this->db->delete("user_addresses");

					if($delete){
						$response['success'] = 1;
						$response['message'] = "Address has been removed.";
					}
					else{
						$response['success'] = 0;
						$response['message'] = "Opps.. Something went wrong. Please try again.";
					}

				}
				else{
					$response['success'] = 0;
					$response['message'] = "Address ID can not be blank.";
				}
			}

			if($action == "set_as_default"){
				$post = $this->input->post();

				if($post['address_id'] != ""){
					if($post['user_id'] != ""){

					$this->db->where("user_id", $post['user_id']);
					$this->db->update("user_addresses", array("is_default"=>""));

					$this->db->where("address_id", $post['address_id']);
					$this->db->update("user_addresses", array("is_default"=>1));

					$response['success'] = 1;
					$response['message'] = "Address set as default";
					}else{
						$response['success'] = 0;
						$response['message'] = "Please provide user id";
					}
				}else{
					$response['success'] = 0;
					$response['message'] = "Please provide address id";
				}
			}

			if($action == "details"){
				$post = $this->input->post();

				if($post['address_id'] != ""){

					$address = $this->db->query("select * from user_addresses where address_id=".$post['address_id']."")->row_array();

					$response['address'] = $address;
					$response['address']['is_default'] = ($address['is_default']) ? $address['is_default'] : "";
					$response['address']['state'] = $this->db->query("select id from states where name='".$address['state']."'")->row()->id;
					$response['address']['city'] = $this->db->query("select id from cities where name='".$address['city']."'")->row()->id;

					$response['success'] = 1;
					$response['message'] = "";
					
				}else{
					$response['success'] = 0;
					$response['message'] = "Please provide address id";
				}

			}
			
		}
		else{
			$response['success'] = 0;
			$response['message'] = "Invalid Operation.";
		}

		echo json_encode($response);
	} 

	public function uploadProfilePic(){

		$data = array();

		$user_id = $this->input->post('user_id');

		$exist_user = $this->db->get_where('users',array('user_id'=>$user_id))->row_array();

		if($user_id != "" && !empty($exist_user)){
			$allowedExts = array("png", "jpg", "jpeg");
			$allowedType = array("image/jpeg", "image/png");
			$profile_pic_extension = end(explode(".", $_FILES["profile_pic"]["name"]));

			$file_type = $_FILES['profile_pic']['type'];

			if(!empty($_FILES["profile_pic"])){
				if(!empty($_FILES["profile_pic"]) && !in_array($profile_pic_extension, $allowedExts) && !in_array($file_type, $allowedType)){
					$response['status'] = '0';
					$response['message'] = 'Only jpg, jpeg or png images are allowed for profile pic.';
				}else{
					$path = FCPATH;
					$location = $path . "assets/uploads/users/";
					$profile_pic = time() . '_' . preg_replace('/\s+/', '_', $_FILES['profile_pic']['name']);
					move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $location . $profile_pic);

					$this->db->where("user_id", $user_id);
					$this->db->update("users", array("profile_pic"=>$profile_pic));

					$this->db->select('profile_pic');
					$this->db->from('users');
					$this->db->where('user_id', $user_id);

					$profile_pic = $this->db->get()->row()->profile_pic;

					if($profile_pic){
						$response['profile_pic'] = base_url() . "assets/uploads/users/" . $profile_pic . "";
					}else{
						$response['profile_pic'] = "";
					}

					$response['success'] = 1;
					$response['message'] = 'profile picture has been updated';
				}
			}else{
				$response['success'] = 0;
				$response['message'] = 'Please select your profile picture';
			}
		}else{
			$response['success'] = 0;
			$response['message'] = 'Please provide	 valid user id';
		}
		echo json_encode($response);
	}

	public function notification_status(){
		$post = $this->input->post();

		if($post['user_id'] != ""){
			if($post['status'] != ""){

				$this->db->where("user_id",$post['user_id']);
				$update = $this->db->update("users",array("notification_active"=>$post['status']));

				if($update){
					$response['status'] = $post['status'];
					$response['message'] = "Notification status has been updated.";
					$response['success'] = 1;
				}else{
					$response['message'] = "Opps...Something went wrong.";
					$response['success'] = 0;
				}

			}else{
				$response['message'] = "Please provide notification status (Enable/Disabled).";
				$response['success'] = 0;
			}
		}else{
			$response['message'] = "Please provide user id.";
			$response['success'] = 0;
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

	public function changeStatus(){
		$post = $this->input->post();

		if($post['buyer_id'] != ""){
			if(isset($post['status'])){

				$is_update = $this->db->query("update users set is_active=".$post['status']." where user_id=".$post['buyer_id']."");

				//echo $this->db->last_query(); die();

				if($is_update){
					$response['message'] = "Status has been changed";
					$response['success'] = 1;
				}else{
					$response['message'] = "Opps...Something went wrong";
					$response['success'] = 0;
				}

			}else{
				$response['message'] = "Please provide status";
				$response['success'] = 0;
			}
		}else{
			$response['message'] = "Please provide user id";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}

}
