<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model

{

   function __construct()

   {

      parent::__construct();

      $this->load->helper('path');

   }



   public function checkjson(&$json)

   {

      $json = json_decode($json);

      return (json_last_error() === JSON_ERROR_NONE);

   }

  /* public function sendMessage($to, $text){


      $authKey = "4d7d1c87-27b8-4071-8a2d-f0e174980c12";
      $senderId = "AQUALO";
      $message = $text;

      $curl = curl_init();

      $postFields = array(
        "sender" => "AQUALO",
        "route"  => "4",
        "country" => "91",
        "sms" => array(
          array(
            "message" => $message,
            "to" => array(
              $to
            ),
          )
        )
      );

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.msg91.com/api/v2/sendsms",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postFields),
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTPHEADER => array(
          "authkey: $authKey",
          "content-type: application/json"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      $response_object = curl_exec($curl);
   }*/

    public function sendMessage($to, $text){

      $msg = urlencode($text);

      $url = "http://sms.hspsms.com/sendSMS?username=digitalfriend&message=".$msg."&sendername=CPOINT&smstype=TRANS&numbers=".$to."&apikey=4d7d1c87-27b8-4071-8a2d-f0e174980c12";
      $response = $this->get_web_page($url);
      $resArr = array();
      $resArr = json_decode($response,true);
      
      return $resArr;

   } 

   function get_web_page($url) {
          $options = array(
              CURLOPT_RETURNTRANSFER => true,   // return web page
              CURLOPT_HEADER         => false,  // don't return headers
              CURLOPT_FOLLOWLOCATION => true,   // follow redirects
              CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
              CURLOPT_ENCODING       => "",     // handle compressed
              CURLOPT_USERAGENT      => "test", // name of client
              CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
              CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
              CURLOPT_TIMEOUT        => 120,    // time-out on response
          ); 

          $ch = curl_init($url);
          curl_setopt_array($ch, $options);

          $content  = curl_exec($ch);

          curl_close($ch);

          return $content;
  }


   function push_notification($operation, $message, $sender_id, $receiver_id, $receiver_role, $sender_role, $item_id){

      /*GCM Token Ids / Array for multiple tokens*/

      $data = array(
         "operation" => $operation,
         "message" => $message,
         "sender_id" => $sender_id,
         "sender_role" => $sender_role,
         "receiver_id" => $receiver_id,
         "receiver_role" => $receiver_role,
         "is_read" => 0,
         "created_at" => date("Y-m-d h:i:s"),
         "updated_at" => date("Y-m-d h:i:s"),
         "item_id" => $item_id
      );

      $insert = $this->db->insert("notifications", $data);


      if($insert){

        $user_tokens = $this->db->get_where("fcm_tokens", array("user_id" => $receiver_id))->result_array();
        $tokens = array_map(function($a) {
          return $a['token'];
        }, $user_tokens);


        define( 'API_ACCESS_KEY', 'AAAA9i7n8oQ:APA91bHmZPnhn6BQwUGu_Fm9tGptYA0ISIv9QP30iKVhMS4tlAxl3E3KMdsJhUCpchu3AaSQh41G-ln7IZ7yj7cN9qV9f3Cz0aD2JtlayuOhzPBcAES2mtq0sFbddwNCKh7i0Xd4LGRU' );
        /*Data object for android foreground and background / ios forground / Fields can be modified as per requirements*/
        $msg = array('title' => "Aqualogy",'message' => $data['message'], 'operation' => $data['operation'], 'content_id'=>$data['item_id'], "notificationsentfrom" => "serverside");
        /*Notification object for ios background / Fields except body can be modified as per requirements*/
        $notification = array('title' => "Aqualogy",'body' =>$data['message'],"sound" =>"default");
        /*Notification Payload*/
        $fields = array('registration_ids'  => $tokens,'notification'=> $notification,'data'=> $msg,'content_available' => true);
        $headers = array('Authorization: key=' . API_ACCESS_KEY,'Content-Type: application/json');
        $ch = curl_init();

        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch);

        curl_close( $ch );

      }
      

    }

   public function generate_otp($user_id, $otp, $mobile){

       $this->db->where("user_id", $user_id);
       $this->db->delete("user_otp");

       $otp = array(
            "user_id" => $user_id,
            "otp_code" => $otp,
            "is_verified" => 0,
            "timestamp" => time()
       );

       $insert = $this->db->insert("user_otp", $otp);

       if($insert){
          $result['success'] = 1;
       }else{
          $result['success'] = 0; 
       }

       return $result;
   }



   public function generate_seller_otp($seller_id, $otp, $mobile){



       //Removing all old OTPs

       $this->db->where("seller_id", $seller_id);

       $this->db->delete("seller_otp");



       //Enter new otp

       $otp = array(

            "seller_id" => $seller_id,

            "otp_code" => $otp,

            "is_verified" => 0,

            "timestamp" => time()

       );



       $insert = $this->db->insert("seller_otp", $otp);



       if($insert){

          $result['success'] = 1;

       }

       else{

          $result['success'] = 0; 

       }



       return $result;



   }



   public function getSingleUserById($user_id){

      $user = $this->db->get_where("users", array("user_id" => $user_id))->row_array();

      if(!empty($user)){

         $result['user_id'] = $user_id;

         $getState = $this->db->get_where("states" ,array("id" => $user['state_id']))->row_array();
         $getCity  = $this->db->get_where("cities" ,array("id" => $user['city_id']))->row_array();

         $result['name']    = $user['name'];
         $result['email']   = $user['email'];
         $result['session'] = md5($user_id);
         $result['mobile']  = $user['mobile'] ? $user['mobile'] : "";
         $result['website']  = $user['website'] ? $user['website'] : "";
         $result['about']  = $user['about'] ? $user['about'] : "";
         $result['company']  = $user['company'] ? $user['company'] : "";
         $result['gst_no']  = $user['gst_no'] ? $user['gst_no'] : "";
         $result['notification_active']  = $user['notification_active'] ? $user['notification_active'] : 0;
         $result['is_active']  = $user['is_active'] ? $user['is_active'] : "";
         $totalCount = $this->db->query("select count(cart_id) as count from cart where session = '".md5($user_id)."'")->row()->count;
         $result['cart_count']  = $totalCount ? $totalCount : "0";
         $result['profile_pic'] = ($user['profile_pic']) ? base_url()."assets/uploads/users/".$user['profile_pic'] :  base_url()."assets/images/default-user.png";

         if(!empty($getState)){
            $result['state_name'] = $getState['name'];
            $result['state_id'] = $user['state_id'];
         }
         else{
            $result['state_name'] = "";
            $result['state_id'] = "";
         }

         if(!empty($getCity)){
            $result['city_name'] = $getCity['name'];
            $result['city_id'] = $user['city_id'];
         }
         else{
            $result['city_name'] = "";
            $result['city_id'] = "";
         }


         return $result;

      }
      else{
         return false;
      }
   }





   public function getSingleAdminById($admin_id){



      $admin = $this->db->get_where("admin",array("admin_id"=>$admin_id))->row_array();



      if(!empty($admin)){



         $result['admin_id']    = $admin_id;

         $result['first_name']  = ($admin['first_name']) ? $admin['first_name'] : "";

         $result['last_name']   = ($admin['last_name']) ? $admin['last_name'] : "";

         $result['email']       = ($admin['email']) ? $admin['email'] : "";

         $result['mobile']      = ($admin['mobile']) ? $admin['mobile'] : "";

         $result['profile_pic'] = ($admin['profile_pic']) ? base_url()."uploads/images/profile_pic/".$admin['profile_pic'] : base_url()."assets/images/default-user.png";



         return $result;

      }

      else{

         return false;

      }



   }



   public function getSingleSellerById($seller_id){

      $seller = $this->db->get_where("sellers", array("seller_id"=>$seller_id))->row_array();

      if(!empty($seller)){
         $result['seller_id']   = $seller_id;
         $result['first_name']  = ($seller['first_name']) ? $seller['first_name'] : "";
         $result['last_name']   = ($seller['last_name']) ? $seller['last_name'] : "";
         $result['name']        =  $result['first_name']." ".$result['last_name'];
         $result['storename']   = ($seller['storename']) ? $seller['storename'] : "";
         $result['mobile']  = ($seller['mobile']) ? $seller['mobile'] : "";
         $result['email']   = ($seller['email']) ? $seller['email'] : "";
         $result['website']   = ($seller['website']) ? $seller['website'] : "";
         $result['about']   = ($seller['about']) ? $seller['about'] : "";
         $result['preferred_categories']   = ($seller['preferred_categories']) ?   $seller['preferred_categories']  : "";
         $result['preferred_categories_two']   = ($seller['preferred_categories_two']) ?   $seller['preferred_categories_two']  : "";
         $result['preferred_categories_three']   = ($seller['preferred_categories_three']) ?  explode(",", $seller['preferred_categories_three'])  : "";

         $preferred_categories = explode(",", $seller['preferred_categories_three']);

          
          $cat_counter = 0;
          $response['selected_categories'] = array();

          foreach($preferred_categories as $preferred_category){
            if(!empty($preferred_categories)){
              $category = $this->db->get_where("categories", array("category_id"=>$preferred_category))->row_array();
              
              $parent = $this->db->get_where("categories", array("category_id"=>$seller['preferred_categories_two']))->row()->category;
              
              $parent_top = $this->db->get_where("categories", array("category_id"=>$seller['preferred_categories']))->row()->category;
              
              $result['selected_categories'][$cat_counter]['category'] = $category['category'];
              $result['selected_categories'][$cat_counter]['parent'] = $parent;
              $result['selected_categories_parent'] = $parent;
              $cat_counter++;
            }
          }

         $result['profile_pic']  = $seller['profile_pic'] ? base_url()."assets/uploads/sellers/profile_pic/".$seller['profile_pic'] : base_url()."assets/images/placeholder.png";
         
         $result['profile_pic_name']  = $seller['cover_pic'] ? $seller['profile_pic'] : "placeholder.png";
         $result['cover_pic_name']    = $seller['cover_pic'] ? $seller['cover_pic'] : "placeholder.png";
         
         $result['cover_pic']  = $seller['cover_pic'] ? base_url()."assets/uploads/sellers/cover_pic/".$seller['cover_pic'] : base_url()."assets/images/placeholder.png";

         $result['open_time']   = $seller['open_time'] ? $seller['open_time'] : "";
         $result['close_time']  = $seller['close_time'] ? $seller['close_time'] : "";
        $result['keywords_obj'] =  $seller['keywords'] ?  explode(",", $seller['keywords']) : "";
         $result['open_days_from']    = $seller['open_days_from'] ? $seller['open_days_from'] : "";
         $result['open_days_to']    = $seller['open_days_to'] ? $seller['open_days_to'] : "";
         $result['open_days_to']    = $seller['open_days_to'] ? $seller['open_days_to'] : "";
         $result['timestamp']  = $seller['timestamp'] ? $seller['timestamp'] : "";
         $result['is_active']  = $seller['is_active'] ? $seller['is_active'] : "";
         $result['facebook']  = $seller['facebook'] ? $seller['facebook'] : "";
         $result['twitter']  = $seller['facebook'] ? $seller['facebook'] : "";
         $result['instagram']  = $seller['instagram'] ? $seller['instagram'] : "";
         $result['youtube']  = $seller['youtube'] ? $seller['youtube'] : "";
        
         $result['company']  = $seller['company'] ? $seller['company'] : "";
         $result['account_token']  = $seller['account_token'] ? $seller['account_token'] : "";
          
         $office_address = $this->db->query("select * from seller_addresses where seller_id=".$seller_id." and type = 0")->row_array();

        $result['AddressObjOfc'] = array();

         if(!empty($office_address)){
             $result['AddressObjOfc']['address_id'] = ($office_address['address_id']) ? $office_address['address_id'] : "";
             $result['AddressObjOfc']['warehouse'] = ($office_address['warehouse']) ? $office_address['warehouse'] : "";
             $result['AddressObjOfc']['gst_no'] = ($office_address['gst_no']) ? $office_address['gst_no'] : "";
             $result['AddressObjOfc']['pan_no'] = ($office_address['pan_no']) ? $office_address['pan_no'] : "";
             $result['AddressObjOfc']['address_1'] = ($office_address['address_1']) ? $office_address['address_1'] : "";
             $result['AddressObjOfc']['address_2'] = ($office_address['address_2']) ? $office_address['address_2'] : "";
             $result['AddressObjOfc']['pincode'] = ($office_address['pincode']) ? $office_address['pincode'] : "";
             $result['AddressObjOfc']['city'] = ($office_address['city']) ? $office_address['city'] : "";
             $result['AddressObjOfc']['state'] = ($office_address['state']) ? $office_address['state'] : "";
             $result['AddressObjOfc']['country'] = ($office_address['country']) ? $office_address['country'] : "";
             $result['AddressObjOfc']['type'] = ($office_address['type']) ? $office_address['type'] : "";
             $result['AddressObjOfc']['seller_id'] = ($office_address['seller_id']) ? $office_address['seller_id'] : "";
             $result['AddressObjOfc']['timestamp'] = ($office_address['timestamp']) ? $office_address['timestamp'] : "";
         }         

         $galleries = $this->db->query("select * from seller_gallery where seller_id=".$seller_id."")->result_array();
         $result['gallery'] = array();

         if(!empty($galleries)){
            $count = 0;

            foreach($galleries as $gallery){

              $is_video = strripos($gallery['image'],"mp4");
              
              if($is_video > 0){
                $image = "https://www.transparentpng.com/download/youtube/video-youtube-icon-png-images-21.png";
                $video = base_url()."assets/uploads/sellers/gallery/".$gallery['image'];
              }else{
                $image = base_url()."assets/uploads/sellers/gallery/".$gallery['image'];  
                $video = "";  
              }

              $result['gallery'][$count]['image_id']   = ($gallery['image_id']) ? $gallery['image_id'] : "";
              $result['gallery'][$count]['image']      = ($image) ? $image : "";
              $result['gallery'][$count]['video']      = ($video) ? $video : "";
              $result['gallery'][$count]['image_name'] = ($gallery['image']) ? $gallery['image'] : "";
              $result['gallery'][$count]['sr_no']      = ($gallery['sr_no']) ? $gallery['sr_no'] : "";
              $result['gallery'][$count]['type']       = ($gallery['type']) ? $gallery['type'] : "";
              $result['gallery'][$count]['is_default'] = ($gallery['is_default']) ? $gallery['is_default'] : "";
              $result['gallery'][$count]['timestamp']  = ($gallery['timestamp']) ? $gallery['timestamp'] : "";
            
              $count++;
            }
         }

         return $result;

      }else{
         return false;
      }
   }



   public function getSlug($string){
      $string = preg_replace("![^a-z0-9]+!i", "-", $string);
      $string = rtrim($string, "-");
      $string = ltrim($string, "-");
      return $string;
   }

   public function load_countries(){

      $this->db->order_by("name");
      $countries = $this->db->get_where("countries")->result_array();

      $c = 0;

      foreach ($countries as $country) {
         $result['countries'][$c]['id'] = $country['id'];
         $result['countries'][$c]['name'] = $country['name'];
         $result['countries'][$c]['code'] = $country['sortname'];
         $result['countries'][$c]['dial_code'] = $country['dial_code'];
         $c++;
      }  

      $result['success'] = 1;
      $result['message'] = "";

      return $result;

   }



   public function load_states($country_id){

      $this->db->order_by("name");
      $this->db->where("country_id", $country_id);
      $states = $this->db->get("states")->result_array();

      if(!empty($states)){
         $c = 0;
         foreach ($states as $state) {
            $result['states'][$c]['id'] = $state['id'];
            $result['states'][$c]['name'] = $state['name'];
            $c++;
         }

         $result['success'] = 1;
         $result['message'] = "";
      }
      else{
         $result['success'] = 0;
         $result['message'] = "States not found for selected country.";
      }

      return $result;
   }



   public function load_cities($state_id){

      $this->db->order_by("name");

      $this->db->where("state_id", $state_id);

      $cities = $this->db->get("cities")->result_array();



      if(!empty($cities)){

         $c = 0;

         foreach ($cities as $city) {

            $result['cities'][$c]['id'] = $city['id'];

            $result['cities'][$c]['name'] = $city['name'];

            $c++;

         }



          $result['success'] = 1;

         $result['message'] = "";

      }

      else{

         $result['success'] = 0;

         $result['message'] = "Cities not found for selected state.";

      }



      return $result;
   }

   public function getSellerAddresses($seller_id, $type = ""){



      $seller = $this->db->get_where("sellers", array("seller_id" => $seller_id))->row_array();



      if(!empty($seller)){



         $this->db->where("seller_id", $seller_id);

         $this->db->order_by("address_id", "desc");



         if($type != ""){

            $this->db->where("type", $type);

         }



         $userAddresses = $this->db->get("seller_addresses")->result_array();



         if(!empty($userAddresses)){

            $i = 0;

            foreach ($userAddresses as $address) {

                  

               $result['address'][$i]['address_id'] = $address['address_id'];

               $result['address'][$i]['warehouse']  = $address['warehouse'] ? $address['warehouse'] : "";

               $result['address'][$i]['address_1']  = $address['address_1'];

               $result['address'][$i]['address_2']  = $address['address_2'];

               $result['address'][$i]['city']       = $address['city'];

               $result['address'][$i]['state']      = $address['state'];

               $result['address'][$i]['pincode']    = $address['pincode'];

               $result['address'][$i]['timestamp']  = $address['timestamp'];

               $result['address'][$i]['seller_id']  = $address['seller_id'];

               $result['address'][$i]['type']       = $address['type'];

               $result['address'][$i]['gst_no']     = $address['gst_no'] ? $address['gst_no'] : "";

               $result['address'][$i]['pan_no']     = $address['pan_no'] ? $address['pan_no'] : "";



               $i++;



            }



            $result['success'] = 1;

            $result['message'] = "";



         }

         else{

            $result['success'] = 0;

            $result['message'] = "No saved addresses.";   

         }



         return $result;



      }

      else{

         return false;

      }
   }

   public function getCategoryType($type_id){
      $type = $this->db->get_where("category_types", array("type_id"=>$type_id))->row_array();
      return $type;
   }

   public function getCategoryName($category_id,$type_id){
      $category = $this->db->get_where("categories", array("category_id"=>$category_id,"type_id"=>$type_id))->row_array();
      return $category;
   }

}