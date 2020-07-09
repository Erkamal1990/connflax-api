<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class User_model extends CI_Model
{
   function __construct()
   {
      parent::__construct();
      $this->load->helper('path');
   }

   

   public function save_account($data)
   {  

      /** Checking for phone number or email is already used **/
      $this->db->where("mobile", $data['mobile']);
      $this->db->where("email", $data['email']);
      if(isset($data['user_id']) && $data['user_id'] != ""){
         $this->db->where("user_id != ", $data['user_id']);
      }
      $checkAccount = $this->db->get("users");

      if($checkAccount->num_rows() == 0){

         if(isset($data['user_id']) && $data['user_id'] != ""){
            $this->db->where("user_id", $data['user_id']);
            $save = $this->db->update("users", $data);
            $user_id = $data['user_id'];
         }
         else{
            $save = $this->db->insert("users", $data);
            $user_id = $this->db->insert_id();
         }

         if($save){

            $user = array(
               "user_id" => $user_id
            );
            $userInfo = $this->profile_information($user);

            if($userInfo['success'] == 1){
               $result['user'] = $userInfo['user'];
            }
            else{
               $result['user'] = (object)[];
            }

            $result['success'] = 1;
            $result['message'] = "Your account has been created successfully.";
         }
         else{
            $result['success'] = 0;
            $result['message'] = "Opps.. Something went wrong. Please try again later.";
         }

      }  
      else{
         $result['success'] = 0;
         $result['message'] = "This account is already exists";
       }   
      
      return $result;

   }

   public function profile_information($data){

      if(isset($data['user_id']) && $data['user_id'] != ""){

         $this->db->where("user_id", $data['user_id']);
         $checkUser = $this->db->get("users")->row_array();

         if(!empty($checkUser)){

            $getCityState = $this->getCityState($checkUser['city']);

            $result['user'] = array(
               'user_id'    => $checkUser['user_id'],
               'first_name' => $checkUser['first_name'],
               'last_name'  => $checkUser['last_name'],
               'email'      => $checkUser['email'],
               'mobile'     => $checkUser['mobile'],
               'gender'     => $checkUser['gender'],
               'city_id'    => $checkUser['city'] ? $checkUser['city'] : "",
               'state_id'   => $checkUser['state'] ? $checkUser['state'] : "",
               'country_id' => $checkUser['country'] ? $checkUser['state'] : "",
               'city'       => $getCityState['city'],
               'state'      => $getCityState['state'],
               'country'    => $getCityState['country']
            );

            $result['success'] = 1;
            $result['message'] = "";

         }
         else{
            $result['success'] = 0;
            $result['message'] = "Account does not exists.";
         }

      }
      else{
         $result['success'] = 0;
         $result['message'] = "User ID missing";
      }

      return $result;

   }

   function getCityState($data){

      if(isset($data['city']) && $data['city'] != ""){

         $this->db->select("cities.name city_name, state.name state_name, country.name as country_name");
         $this->db->from("cities");
         $this->db->join("states", 'cities.state_id=states.id');
         $this->db->join("countries", 'states.country_id=countries.id');
         $this->db->where("cities.id", $data['city']);
         $get = $this->db->get()->row_array();

         if(!empty($get)){
            $result['city'] = $get['city_name'];
            $result['state'] = $get['state_name'];
            $result['country'] = $get['country_name'];
         }
         else{
            $result['city'] = "";
            $result['state'] = "";
            $result['country'] = "";   
         }

      } 
      else{
         $result['city'] = "";
         $result['state'] = "";
         $result['country'] = "";
      }

      return $result;

   }

}