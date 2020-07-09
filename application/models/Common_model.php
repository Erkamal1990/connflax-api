<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{
   function __construct(){
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
public function clean($string) {
     $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

     return preg_replace('/[^A-Za-z0-9\-.]/', '', $string); // Removes special chars.
  }
public function sendMessage($to, $text){
      $msg = urlencode($text);
      $url = "http://sms.hspsms.com/sendSMS?username=digitalfriend&message=".$msg."&sendername=CPOINT&smstype=TRANS&numbers=".$to."&apikey=4d7d1c87-27b8-4071-8a2d-f0e174980c12";
      $response = $this->get_web_page($url);
      $resArr = array();
      $resArr = json_decode($response,true);
      return $resArr;
} 
public function get_web_page($url) {
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
public function push_notification($operation, $message, $sender_id, $receiver_id, $receiver_role, $sender_role, $item_id){
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
       $updateOtp = array('is_verified' =>0,);
       $this->db->where('user_id',$user_id);
       $this->db->update('user_otp',$updateOtp);

       $otp = array(
            "user_id" => $user_id,
            "otp_code" => $otp,
            "is_verified" => 1,
            "timestamp" => time(),
       );

       $insert = $this->db->insert("user_otp", $otp);

       if($insert){
          $result['success'] = 1;
       }else{
          $result['success'] = 0; 
       }

       return $result;
}
public function getSingleUserById($user_id){
      $user = $this->db->get_where("user", array("user_id" => $user_id))->row_array();
      if(!empty($user)){
        if($user['login_type'] == 1){
          $login_type = 'Facebook';
        }else if($user['login_type'] == 2){
           $login_type = 'Google';
        }else{
          $login_type = 'Regular';
        }
        $walletCR = $this->db->query('select sum(amount) AS total from wallet where payment_type = 1 and user_id ='.$user_id)->row_array();
        $walletDR = $this->db->query('select sum(amount) AS total from wallet where payment_type = 2 and user_id ='.$user_id)->row_array();
        
        $walletAmount = 0;
        $walletAmount = $walletCR['total'] - $walletDR['total'];

        $isPlan = '';
        $planStart = '';
        $planEnd = '';
        if(!empty($user_id)){
          $subscrbeData = $this->db->query('select * from subscription where user_id ='.$user_id.' order by subscription_id desc limit 1')->row_array();
          if(!empty($subscrbeData)){
            $isPlan = $subscrbeData['plan_id'];
            $planStart = date("d M Y",$subscrbeData['timestamp_from']);
            $planEnd = date("d M Y",$subscrbeData['timestamp_to']);
          }
         }

         $result['user_id'] = $user_id;

         $result['first_name']    = $user['first_name'];
         $result['last_name']     = $user['last_name'] ? $user['last_name'] : "";
         $result['email']         = $user['email']? $user['email'] : "";
         $result['mobile']        = $user['mobile'] ? $user['mobile'] : "";
         $result['country_code']  = $user['country_code']? $user['country_code'] : "";
         $result['referral_code'] = $user['referral_code'] ? $user['referral_code'] : "";
         $result['notification']  = $user['notification'] == 0?'OFF':'ON';
         $result['autoplay']      = $user['autoplay'] == 0?'OFF':'ON';
         $result['plan_id']       = $isPlan?$isPlan:'';
         $result['start_date']    = $planStart ? $planStart : '';
         $result['end_date']      = $planEnd ? $planEnd : '';
         $result['social_media_id']  = $user['social_media_id']?$user['social_media_id']:'';
         $result['status']        = $user['status'];
         $result['login_type']    = $login_type;
         $result['my_wallet']     = $walletAmount;
         $result['profile_pic']   = $user['profile_pic'] ? base_url().'assets/global/user_thumb/' . $user['profile_pic'] : '';
         return $result;
      }
      else{
         return false;
      }
  }
public function generate_code($data){
    $this->db->where('code',$data['code']);
    $this->db->where('user_id',$data['user_id']);
    $checkCode = $this->db->get_where("referral_code")->row_array();
    if(empty($checkCode)){
        $codeData = array(
          'user_id' => $data['user_id'],
          'code' =>$data['code'] ,
          'timestamp' => time(),
           );
        $insert = $this->db->insert('referral_code',$codeData);

        if($insert){
          $this->db->where('referral_code',$data['code']);
          $getUser = $this->db->get_where("user")->row_array();
          $getsetting = $this->db->query("select * from settings where type = 'referral_amount'")->row_array();
          $user_id = $getUser['user_id'];
          $amount = $getsetting['description'];
          $type = 1;
          $mode = 'referral';
          $comment = 'Referral bonus.';
          $this->walletTransaction($user_id,$amount,$type,$mode,$comment);
          $result['success'] = 1;
       }else{
          $result['success'] = 0; 
       }
    }else{
      $result['success'] = '';
    }
     return $result;
  }

public function updateDeviceId($user_id,$device_id){
    $data = array('device_id' =>$device_id , );
    $this->db->where("user_id",$user_id);
    $update = $this->db->update("user", $data);
    if($update){
        $result['success'] = 1;
     }else{
        $result['success'] = 0; 
     }
     return $result;
  }
public function getCategoryType($type_id){
  $type = $this->db->get_where("category_types", array("type_id"=>$type_id))->row_array();
  return $type;
}
public function getCategoryName($category_id,$type_id){
    $category = $this->db->get_where("categories", array("category_id"=>$category_id,"type_id"=>$type_id))->row_array();
    return $category;
 }

public function getVideoById($video_id,$type,$user_id=''){
  if($type == 'movie'){
    $videoData = $this->db->get_where("movie", array("video_id" => $video_id,"type"=>$type))->row_array();
  }else{
    $videoData = $this->db->get_where("series", array("video_id" => $video_id,"type"=>$type))->row_array();
  }
    
    if(!empty($videoData)){

       $result['video_id']      =  $videoData['video_id'];
       $result['title']         =  $videoData['title'];
       $result['description_short'] =  $videoData['description_short'] ? $videoData['description_short'] : '';
       $result['description_long']  =  $videoData['description_long'] ? $videoData['description_long'] : '';
       $result['year']        =  $videoData['year'] ? $videoData['year'] : '';
       $result['price']       =  $videoData['price'] ? $videoData['price']:'';
       $result['rating']      =  $videoData['rating'] ? $videoData['rating'] : '';
       $ismylist = '';
       if(!empty($user_id)){
        $mylist = $this->db->get_where("mylist", array("user_id" => $user_id,'type'=>$type,'video_id'=>$video_id))->row_array();
        if(!empty($mylist)){
          $ismylist = "1";
        }
       }
       $isSubscrbe = '';
      if(!empty($user_id)){
        $subscrbe = $this->db->get_where("subscription", array("user_id" => $user_id,'type'=>$type,'video_id'=>$video_id))->row_array();
        if(!empty($subscrbe)){
          $isSubscrbe = "1";
        }
       }

       $result['subscrbe']    =  $isSubscrbe;
       $result['mylist']      =  $ismylist;

       $genreExp = explode(',', $videoData['genre_id']);
       foreach ($genreExp as  $genre) {
          $genreVal[] = $this->db->get_where("genre", array("genre_id" => $genre))->row()->name;
       }

       if(!empty($genreVal)){
          $genres = implode(",", $genreVal);
       }else{
          $genres = "";
       }

       $categoryExp = explode(',', $videoData['categories']);
       foreach ($categoryExp as  $cat) {
          $carVal[] = $this->db->get_where("categories", array("category_id" => $cat))->row()->name;
       }

       if(!empty($carVal)){
          $category = implode(",", $carVal);
       }else{
          $category = "";
       }

      $actorExp = explode(',', $videoData['actors']);
       foreach ($actorExp as  $actor) {
          $actorVal[] = $this->db->get_where("actor", array("actor_id" => $actor))->row()->name;
       }

       if(!empty($actorVal)){
          $actors = implode(",", $actorVal);
       }else{
          $actors = "";
       }

      $directorExp = explode(',', $videoData['director']);
       foreach ($directorExp as  $director) {
          $directorVal[] = $this->db->get_where("director", array("director_id" => $director))->row()->name;
       }

       if(!empty($directorVal)){
          $directors = implode(",", $directorVal);
       }else{
          $directors = "";
       }

       $result['genre']         =  $genres;
       $result['categories']    = $category;
       $result['actors']        =  $actors;
       $result['director']      =  $directors;
       $result['featured']      =  $videoData['featured'];
       $result['kids_restriction']  =  $videoData['kids_restriction'];
       $result['movie_url']         =  $videoData['url'] ? $videoData['url'] : '';
       $result['trailer_url']       =  $videoData['trailer_url'] ? $videoData['trailer_url'] :'';
       $result['duration']          =  gmdate("H:i:s", $videoData['duration']);
       $result['video_thumb']       =  base_url().'assets/global/'.$type.'_thumb/' . $videoData['video_id'] . '.jpg';
       $result['video_banner']      =  base_url().'assets/global/'.$type.'_poster/' . $videoData['video_id'] . '.jpg';
       $result['created']           =  date("d M Y",$videoData['created_timestamp']);
       if($type == 'movie'){
          $relatedVideos = $this->db->query("select * from movie where genre_id in(".$videoData['genre_id'].") or categories in(".$videoData['categories'].")")->result_array();
      }else{
          $relatedVideos = $this->db->query("select * from series where genre_id in(".$videoData['genre_id'].") or categories in(".$videoData['categories'].")")->result_array();
      }

      if(!empty($relatedVideos)){
        $reCount = 0;
        foreach ($relatedVideos as $related) {
          $result['relatedVideos'][$reCount]['video_id'] = $related['video_id'];
          $result['relatedVideos'][$reCount]['type'] = $type;
          $result['relatedVideos'][$reCount]['title'] = $related['title'];
          $result['relatedVideos'][$reCount]['video_thumb'] = base_url().'assets/global/'.$type.'_thumb/' . $related['video_id'] . '.jpg';
          $result['relatedVideos'][$reCount]['video_banner'] = base_url().'assets/global/'.$type.'_poster/' . $related['video_id'] . '.jpg';
          $reCount++;
        }
      }else{
        $result['relatedVideos'] = array();
      }
       return $result;
    }else{
       return $result;
    }
  }

public function allVideoList($data){ 
  if($data['type'] == ''){
     $banners =  $this->db->query('select video_id,title,banner,type FROM movie where banner=1 UNION ALL select video_id,title,banner,type FROM series where banner=1 order by video_id desc')->result_array();
     $bcount = 0;
     foreach ($banners as  $banner) {
      $allvideoData['banners'][$bcount]['video_id'] = $banner['video_id'];
      $allvideoData['banners'][$bcount]['title'] = $banner['title'];
      $allvideoData['banners'][$bcount]['type']  = $banner['type']?$banner['type']:'';
      $allvideoData['banners'][$bcount]['video_thumb'] = base_url().'assets/global/'.$banner['type'].'_thumb/' . $banner['video_id'] . '.jpg';
      $allvideoData['banners'][$bcount]['video_banner'] = base_url().'assets/global/'.$banner['type'].'_poster/' . $banner['video_id'] . '.jpg';
       $bcount++;
     }
  }else{

      $condInner = "";
      if($data['type']!=''){
            $condInner .= ' type="'.$data['type'].'"';
        }

      if($data['genre_id']!=''){
            $condInner .= ' and genre_id='.$data['genre_id'];
        }

    $innerVideo =  $this->db->query('select video_id,title,banner,type FROM movie where '.$condInner.' UNION ALL select video_id,title,banner,type FROM series where '.$condInner.' order by video_id desc')->result_array();
    if($innerVideo){
        $allvideoData['innerVideo'][0]['video_id']     = $innerVideo[0]['video_id'];
        $allvideoData['innerVideo'][0]['title']        = $innerVideo[0]['title'];
        $allvideoData['innerVideo'][0]['type']         = $innerVideo[0]['type']?$innerVideo[0]['type']:'';
        $allvideoData['innerVideo'][0]['video_thumb'] = base_url().'assets/global/'.$innerVideo[0]['type'].'_thumb/' . $innerVideo[0]['video_id'] . '.jpg';
        $allvideoData['innerVideo'][0]['video_banner']  = base_url().'assets/global/'.$innerVideo[0]['type'].'_poster/' . $innerVideo[0]['video_id'] . '.jpg';
      }
}
    if($data['category_id'] != ""){
      $categories = $this->db->get_where('categories',array("category_id"=>$data['category_id']))->result_array();
      }else{
         $categories = $this->db->get('categories')->result_array();
     }
     //print_r($categories);
   $catCount = 0;
   foreach ($categories as $cat) {
       if($data['page'] || !empty($data['page']) || $data['limit'] || !empty($data['limit'])){
          $curpage = $data['page'];
          $limit = $data['limit'];
        }else{
          $curpage = 1;
          $limit = 30;
        }

        $cond = "";

        if($data['type']!=''){
            $cond .= ' and type="'.$data['type'].'"';
        }

        if($data['genre_id']!=''){
            $cond .= ' and genre_id="'.$data['genre_id'].'"';
        }

        if(isset($data['featured']) && $data['featured'] != ""){
            $cond .= ' and featured="'.$data['featured'].'"';
        }

        $start  = ($curpage * $limit) - $limit;
        $total_videos  =  $this->db->query('select video_id, title, type FROM movie where 1=1 '.$cond.' and FIND_IN_SET('.$cat['category_id'].',categories) UNION ALL select video_id, title, type FROM series where 1=1 '.$cond.' and FIND_IN_SET('.$cat['category_id'].',categories)');
        $totlerec   = $total_videos->num_rows();
        $endpage    = ceil($totlerec/$limit);
        $startpage  = 1;
        $nextpage   = $curpage + 1;
        $prevpage   = $curpage - 1;

        $DisplayLimit = " limit ".$start.",".$limit;

       $allvideos = $this->db->query('select video_id, title, type,categories FROM movie where 1=1 '.$cond.' and FIND_IN_SET('.$cat['category_id'].',categories) group by categories UNION ALL select video_id, title, type, categories FROM series where 1=1 '.$cond.' and FIND_IN_SET('.$cat['category_id'].',categories) group by categories'.$DisplayLimit)->result_array();
       $query_tot = $this->db->query('SELECT FOUND_ROWS() as myCounter');
       $iFilteredTotal = $query_tot->row()->myCounter;

       if($totlerec){
          $allvideoData['list'][$catCount]['category_title'] = $cat['name'];
          $allvideoData['list'][$catCount]['category_id']   = $cat['category_id'];
          $allvideoData['list'][$catCount]['totalrecord'] = $iFilteredTotal;
        }

      // $allvideoData['list'][$catCount]['videos'] = array();

     if(!empty($allvideos)){
        $count = 0;
       foreach ($allvideos as $video) {

         $allvideoData['list'][$catCount]['videos'][$count]['video_id'] =  $video['video_id'];
         $allvideoData['list'][$catCount]['videos'][$count]['type'] =  $video['type']?$video['type']:'';
         $allvideoData['list'][$catCount]['videos'][$count]['title'] =  $video['title'];
         $allvideoData['list'][$catCount]['videos'][$count]['video_thumb'] = base_url().'assets/global/'.$video['type'].'_thumb/' . $video['video_id'] . '.jpg';
         $allvideoData['list'][$catCount]['videos'][$count]['video_banner'] = base_url().'assets/global/'.$video['type'].'_poster/' . $video['video_id'] . '.jpg';

         $count++;
       }
     }
     // else{
     //  unset($allvideoData['list'][$catCount]['videos']);
     // }
    // print_r($allvideoData['list'][$catCount]['videos']);
     $catCount++;
   }

  return $allvideoData;

}

public function get_list_of_directories_and_files($dir = APPPATH, &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }

  public function get_all_php_files() {
    $all_files = $this->get_list_of_directories_and_files();
    foreach ($all_files as $file) {
      $info = pathinfo($file);
      if( isset($info['extension']) && strtolower($info['extension']) == 'php') {
        // echo $file.' <br/> ';
        if ($fh = fopen($file, 'r')) {
          while (!feof($fh)) {
            $line = fgets($fh);
            preg_match_all('/get_phrase\(\'(.*?)\'\)\;/s', $line, $matches);
            foreach ($matches[1] as $matche) {
              get_phrase($matche);
            }
          }
          fclose($fh);
        }
      }
    }

    echo 'I Am So Lit';
  }
  public function get_list_of_language_files($dir = APPPATH.'/language', &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value){
      $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
      if(!is_dir($path)) {
        $results[] = $path;
      } else if($value != "." && $value != "..") {
        $this->get_list_of_directories_and_files($path, $results);
        $results[] = $path;
      }
    }
    return $results;
  }
public function walletTransaction($user_id,$amount,$type=null,$mode=null,$comment=null){
  // $type = CR/DR
  //$mode = referral,promo bonus,add money 
  //$comment = comments 
    $data = array(
        'user_id'      => $user_id,
        'amount'       => $amount,
        'payment_type' => $type,
        'payment_mode' => $mode,
        'comment'      => $comment,
       );
    $insert = $this->db->insert('wallet',$data);
    if($insert){
        $result['success'] = 1;
     }else{
        $result['success'] = 0; 
     }
     return $result;
}
public function addTransactionDetail($txnData){
    $insert = $this->db->insert('transactions',$txnData);
    if($insert){
        $result['success'] = 1;
     }else{
        $result['success'] = 0; 
     }
     return $result;
}
public function getSubscription($data){
  if($data['type'] == 'movie'){
    $planData   = $this->getVideoById($data['video_id'],$data['type']);
    $to_date    = date(strtotime('+365 days'));
  }else{
    $planData   = $this->db->query('select * from plan where plan_id = '.$data['plan_id'])->row_array();
    $to_date    =  date(strtotime("+".$planData['days']." days"));
  }
    $from_date   =  time();
    $SubData  = array(
            'user_id'         =>$data['user_id'],
            'plan_id'         =>$data['plan_id'],
            'price_amount'    =>$planData['price'],
            'paid_amount'     =>$data['amount'],
            'video_id'        =>$data['video_id'],
            'type'            =>$data['type'],
            'timestamp_from'  =>$from_date,
            'timestamp_to'    =>$to_date,
            'payment_method'  =>$data['payment_mode'],
            'payment_details' =>'',
            'payment_timestamp' =>time(),
         );
    $insert = $this->db->insert('subscription',$SubData);
    if($insert){
        $result['success'] = 1;
     }else{
        $result['success'] = 0; 
     }
     return $result;
}
//https://subinsb.com/trending-box-html-php-mysql/
public function insert_search_data($video_id,$title,$type){
  $searchCheck = $this->db->get_where('popular_search',array("video_id"=>$video_id,'type'=>$type))->row_array();
    if(!empty($searchCheck)){
      $results = $this->db->query('update popular_search SET hits=hits+1 WHERE video_id = '.$video_id.' and type = "'.$type.'"');
    }else{
      $searchData = array(
          'video_id' => $video_id,
          'title' =>$title,
          'type' =>$type,
          'hits' =>1,
         );
      $results = $this->db->insert('popular_search',$searchData);
    }
    if($results){
      $response['success'] = 1;
    }else{
      $response['success'] = 0;
    }
    return $response;
}

}
//update popular_search SET hits=hits+1 WHERE video_id = 7 and type = "movie"