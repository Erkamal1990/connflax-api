<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends CI_Controller {

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
	public function categories_list(){
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
		$users   	= $this->db->get('categories');
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
			$cond .= " and (name like '%".$search."%') ";
		}

		$categories = $this->db->query("select SQL_CALC_FOUND_ROWS * from categories where 1=1 ".$cond." order by category_id DESC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($categories)){
			foreach($categories as $category){

				$response['category'][$count]['category_id'] = ($category['category_id']) ? $category['category_id'] : "";
				$response['category'][$count]['name'] = ($category['name']) ? $category['name'] : "";

				$response['category'][$count]['image'] = $this->crud_model->get_category_image_url($category['category_id']) ;
				$response['category'][$count]['timestamp'] = ($category['timestamp']) ? date("d M Y",$category['timestamp']) : "";

				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No category found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function genre_list(){
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
		$users   	= $this->db->get('genre');
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
			$cond .= " and (name like '%".$search."%') ";
		}

		$genres = $this->db->query("select SQL_CALC_FOUND_ROWS * from genre where 1=1 ".$cond." order by genre_id DESC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($genres)){
			foreach($genres as $genre){

				$response['genre'][$count]['genre_id'] =  $genre['genre_id'];
				$response['genre'][$count]['name'] 		= $genre['name'];
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No genre found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function actor_list(){
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
		$users   	= $this->db->get('actor');
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
			$cond .= " and (name like '%".$search."%') ";
		}

		$actors = $this->db->query("select SQL_CALC_FOUND_ROWS * from actor where 1=1 ".$cond." order by actor_id DESC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($actors)){
			foreach($actors as $actor){

				$response['actor'][$count]['actor_id'] =  $actor['actor_id'];
				$response['actor'][$count]['name'] 		= $actor['name'];
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No actor found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function language_list(){
		$language_files = array();
		$all_files = $this->common_model->get_list_of_language_files();
		foreach ($all_files as $file) {
			$info = pathinfo($file);
			if( isset($info['extension']) && strtolower($info['extension']) == 'json') {
				$file_name = explode('.json', $info['basename']);
				array_push($language_files, $file_name[0]);
			}
		}
		$count = 0;
		foreach ($language_files as $lang_name) {
			$response['language'][$count]['name'] = $lang_name;
			$count++;
		}
		echo json_encode($response);
	}
	public function notification_setting(){
		$post = $this->input->post();
		if($post['user_id'] != ''){
			if($post['value'] != ''){
				$data = array('notification' =>$post['value'], );
			    $update = $this->db->update('user', $data,  array('user_id' => $post['user_id']));
			    if($update){
			    	$response['message'] = "Notification status has been updated.";
			    	$response['success'] = 1;
			    }
			    else{
			    	$response['message'] = "Opps.. Something went wrong. Please try again.";
			    	$response['success'] = 0;
			    }
			}
			else{
				$response['message'] = "Please provide notification status.";
			    $response['success'] = 0;
			}
		}
		else{
			$response['message'] = "user_id can not be blank.";
			$response['success'] = 0;
		}
		echo json_encode($response);
	}
	public function autoplay_setting(){
		$post = $this->input->post();
		if($post['user_id'] != ''){
			if($post['value'] != ''){
				$data = array('autoplay' =>$post['value'], );
			    $update = $this->db->update('user', $data,  array('user_id' => $post['user_id']));
			    if($update){
			    	$response['message'] = "Autoplay status has been updated.";
			    	$response['success'] = 1;
			    }
			    else{
			    	$response['message'] = "Opps.. Something went wrong. Please try again.";
			    	$response['success'] = 0;
			    }
			}
			else{
				$response['message'] = "Please provide autoplay status.";
			    $response['success'] = 0;
			}
		}
		else{
			$response['message'] = "user_id can not be blank.";
			$response['success'] = 0;
		}
		echo json_encode($response);
	}
public function media_list(){
		$post = $this->input->post();
			$data = array(
				'limit' => $post['limit'],
				'page' => $post['page'] ,
				'q' => $post['q'] ,
				'type' => $post['type'] ,
				'genre_id' => $post['genre_id'] ,
				'featured' => ($post['featured']) ? $post['featured'] : 0,
				 );

			$data['category_id'] = ($post['category_id']) ? $post['category_id'] : "" ;
			$result = $this->common_model->allVideoList($data);
			if(!empty($result)){
				$response['videos'] = $result;
				$response['success'] = 1;
				$response['message'] = '';
			}else{
				$response['success'] = 0;
				$response['message'] = 'No Data Found!';
			}

		echo json_encode($response);
	}
	public function video_by_id(){
		$post = $this->input->post();
		if(isset($post['type']) && $post['type']){
			if(isset($post['video_id']) && $post['video_id']){
				$results = $this->common_model->getVideoById($post['video_id'],$post['type'],$post['user_id']);
				if($results){
					$response['message'] = "";
					$response['success'] = 1;
					$response['videos'] = $results;

				}
				else{
					$response['message'] = "No video found.";
					$response['success'] = 0;
				}
			}else{
					$response['message'] = "video id can not be blank.";
					$response['success'] = 0;
			}
		}
			else{
			$response['message'] = "type can not be blank.";
			$response['success'] = 0;
		}

		echo json_encode($response);
	}
	public function video_type_list(){
// 		$video_type  = array('movie','series','tvshows');
		$video_type  = array('movie','tvshows');
		$count = 0;
		if(!empty($video_type)){
			foreach($video_type as $type){
				$response['video_type'][$count]['type'] =  $type;
				$count++;
			}
			$response['message'] = '';
			$response['success'] = 1;
		}else{
			$response['message'] = "No type found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function manage_mylist_video(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id'] != ""){
			if(isset($post['video_id']) && $post['video_id'] != ""){
				if(isset($post['type']) && $post['type'] != ""){
					 $checkVideo = $this->db->get_where('mylist',array('user_id'=>$post['user_id'],'video_id'=>$post['video_id'],'type'=>$post['type']))->row_array();
					 if(empty($checkVideo)){
					  $listData = array(
					      'user_id'   => $post['user_id'],
					      'video_id'  => $post['video_id'],
					      'type'      => $post['type'],
					      'timestamp' => time(),
				       );
					 	$insert = $this->db->insert('mylist',$listData);
					 	if($insert){
					 		$response['success'] = 1;
							$response['message'] = "Video successfully add in my list.";
					 	}else{
					 		$response['success'] = 0;
					        $response['message'] = "Opps.. Something went wrong.";
					 	}

					 }else{
					 	    $where = array('id' => $checkVideo['id'],'user_id' => $checkVideo['user_id']);
    						$this->db->where($where);
    						$delete = $this->db->delete('mylist',$where);
    					if($delete){
					 		$response['success'] = 1;
							$response['message'] = "Video successfully removed in my list.";
					 	}else{
					 		$response['success'] = 0;
					        $response['message'] = "Opps.. Something went wrong.";
					 	}
					 }
				}else{
					$response['success'] = 0;
					$response['message'] = "Video type can not blank.";
				}

			}else{
				$response['success'] = 0;
				$response['message'] = "Video id can not blank.";
			}

		}else{
			$response['success'] = 0;
			$response['message'] = "User id can not blank.";
		}
		echo json_encode($response);
	}
	public function video_mylist(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id'] != ""){
			if($post['page'] || !empty($post['page']) || $post['limit'] || !empty($post['limit'])){
				$curpage = $post['page'];
				$limit = $post['limit'];
			}else{
				$curpage = 1;
				$limit = 20;
			}

			$start      = ($curpage * $limit) - $limit;
			$users   	= $this->db->get('mylist');
			$totlerec   = $users->num_rows();
			$endpage    = ceil($totlerec/$limit);
			$startpage  = 1;
			$nextpage   = $curpage + 1;
			$prevpage   = $curpage - 1;

			$DisplayLimit = " limit ".$start.",".$limit;

			$mylistData = $this->db->query("select SQL_CALC_FOUND_ROWS * from mylist where 1=1 and user_id =".$post['user_id']." order by id DESC".$DisplayLimit)->result_array();
				$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
				$iFilteredTotal = $query->row()->myCounter;

			$count = 0;
			if(!empty($mylistData)){
				foreach($mylistData as $list){

					$response['videos'][$count]['user_id'] 		=  $list['user_id'];
					$response['videos'][$count]['video_id'] 	=  $list['video_id'];
					$response['videos'][$count]['type'] 		  = $list['type'];
					$response['videos'][$count]['video_thumb'] =  base_url().'assets/global/'.$list['type'].'_thumb/' . $list['video_id'] . '.jpg';
					$response['videos'][$count]['video_banner'] = base_url().'assets/global/'.$list['type'].'_poster/' . $list['video_id'] . '.jpg';;
					$response['videos'][$count]['timestamp'] 	= date("d M Y",$list['timestamp']);
					$count++;
				}
				$response['count'] = $iFilteredTotal;
				$response['success'] = 1;
				$response['message'] = "";
			}else{
				$response['message'] = "No video found.";
				$response['success'] = 0;
			}
		}else{
				$response['message'] = "user id can not blank.";
				$response['success'] = 0;
		}
		echo json_encode($response);
	}

    public function clean($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

	   return preg_replace('/[^A-Za-z0-9\-.]/', '', $string); // Removes special chars.
	}
	public function notification_list(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id'] != ""){

			if(isset($post['type']) && $post['type'] != ""){

				if (isset($post['limit']) && $post['limit'] != "") {
					$limit = $post['limit'];
				} else {
					$limit = 20;
				}

				if (isset($post['page']) && $post['page'] != "") {
					$page = $post['page'];
				} else {
					$page = 1;
				}	

				$start = ($page - 1) * $limit;

				$notifications = $this->db->query("select SQL_CALC_FOUND_ROWS * from notifications where receiver_id = ".$post['user_id']." and receiver_role = '".$post['type']."' order by id desc limit " . $start . ", " . $limit) -> result_array();

				$query = $this->db->query('SELECT FOUND_ROWS() AS `Count`');
				$num_results = $query->row()->Count;

				if(!empty($notifications)){
					$i = 0;	
					foreach ($notifications as $notification) {
						$response['notifications'][$i]['id'] = $notification['id'];
						$response['notifications'][$i]['message'] = $notification['message'];
						$response['notifications'][$i]['operation'] = $notification['operation'];
						$response['notifications'][$i]['is_read'] = $notification['is_read'];
						$response['notifications'][$i]['time'] = $this->RelativeTime(strtotime($notification['created_at']));
						$response['notifications'][$i]['item_id'] = $notification['item_id'] ? $notification['item_id'] : "";
						$i++;
					}

					$response['success'] = 1;
					$response['total']   = $num_results;
					$response['message'] = $num_results." notifications found.";
				}
				else{
					$response['success'] = 0;
					$response['message'] = "No notification found";
				}

			}
			else{
				$response['success'] = 0;
				$response['message'] = "Invalid Notification Type";
			}

		}
		else{

			$response['success'] = 0;
			$response['message'] = "Invalid User ID";

		}

		echo json_encode($response);

	}

	public function RelativeTime($timestamp){
	    $difference = time() - $timestamp;
	    $periods = array(" sec", " min", " hour", " day", " week", " month", " years", " decade");
	    $lengths = array("60","60","24","7","4.35","12","10");

	    if ($difference > 0)
	    {
	        $ending = " ago";
	    }
	    else
	    {
	         $difference = -$difference;
	         $ending = "to go";
	    }

	    for($j = 0; $difference >= $lengths[$j]; $j++)
	    {
	        $difference /= $lengths[$j];
	    }

	    $difference = round($difference);

	    if($difference != 1)
	    {
	         $periods[$j].= "s";
	    }
	    return $difference . $periods[$j] . $ending;
	}

	public function mark_as_read(){

		$post = $this->input->post();

		if(isset($post['id']) && $post['id']){

			$this->db->where("id", $post['id']);
			$update = $this->db->update("notifications", array("is_read" => 1));

			if($update){
				$response['success'] = 1;
				$response['message'] = "";
			}
			else{
				$response['success'] = 0;
				$response['message'] = "Opps.. Something went wrong.";
			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "Invalid ID";
		}

		echo json_encode($response);

	}

	public function mark_all_read(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'])	{

			if(isset($post['type']) && $post['type'])	{

				$this->db->where("receiver_id", $post['user_id']);
				$this->db->where("receiver_role", $post['type']);
				$update = $this->db->update("notifications", array("is_read" => 1));

				if($update){
					$response['success'] = 1;
					$response['message'] = "";
				}
				else{
					$response['success'] = 0;
					$response['message'] = "Opps.. Something went wrong.";
				}

			}
			else{

				$response['success'] = 0;
				$response['message'] = "Invalid Receiver Type";

			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "Invalid ID";
		}

		echo json_encode($response);

	}


	public function unread_count(){

		$post = $this->input->post();

		if(isset($post['user_id']) && $post['user_id'])	{

			if(isset($post['type']) && $post['type'])	{

				$this->db->where("receiver_id", $post['user_id']);
				$this->db->where("receiver_role", $post['type']);
				$this->db->where("is_read", 0);
				$count = $this->db->get("notifications")->num_rows();
				
				$response['success'] = 1;
				$response['count'] = $count;
				$response['message'] = "";
				
			}
			else{

				$response['success'] = 0;
				$response['message'] = "Invalid Receiver Type";

			}

		}
		else{
			$response['success'] = 0;
			$response['message'] = "Invalid ID";
		}

		echo json_encode($response);

	}
 public function wallet_transaction(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id'] != ""){
			if(isset($post['amount']) && $post['amount'] != ""){
				if(isset($post['txn_id']) && $post['txn_id'] != ""){
					$txnData = array(
						'user_id'        =>$post['user_id'],
						'amount'         =>$post['amount'],
						'transaction_id' =>$post['txn_id'],
						'payment_mode'   =>$post['payment_mode'],
						'currency'       =>$post['currency'],
						'status'         =>$post['status'],
						'timestamp'      =>time(),
						 );
					$results  = $this->common_model->addTransactionDetail($txnData);
					if($results['success'] == 1){
						$response['message'] = "";
					    $response['success'] = 1;
					}else{
						$response['message'] = "Opps.. Something went wrong.";
					    $response['success'] = 0;
					}
					if($post['status'] == 'success'){
						$type = 1;
						$mode = 'Add Money';
						$comment = 'Point add by you.';
						$this->common_model->walletTransaction($post['user_id'],$post['amount'],$type,$mode,$comment);
					}
				}else{
					$response['message'] = "txn_id can not blank.";
					$response['success'] = 0;
				}
			}else{
				$response['message'] = "Amount can not blank.";
				$response['success'] = 0;
			}
		}else{
				$response['message'] = "user id can not blank.";
				$response['success'] = 0;

		}
		echo json_encode($response);
	}

	public function subscription(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id'] != ""){
			$subData = array(
				'user_id'  =>$post['user_id'] ,
				'plan_id'  =>$post['plan_id'],
				'video_id' =>$post['video_id'],
				'amount'   =>$post['amount'],
				'type'     =>$post['type'],
				 );
			if(isset($post['txn_id']) && $post['txn_id'] != ""){
				$txnData = array(
					'user_id'        =>$post['user_id'],
					'amount'         =>$post['amount'],
					'transaction_id' =>$post['txn_id'],
					'payment_mode'   =>$post['payment_mode'],
					'currency'       =>$post['currency'],
					'status'         =>$post['status'],
					'timestamp'      =>time(),
					 );
				$results  = $this->common_model->addTransactionDetail($txnData);
				if($post['status'] == 'success'){
						$type = 1;
						$mode = 'Add Money';
						$comment = 'Point add by you.';
						$this->common_model->walletTransaction($post['user_id'],$post['amount'],$type,$mode,$comment);
					}
			}
			$results = $this->common_model->getSubscription($subData);
			if($results['success'] == 1){
				$transactionType = 2;
				$mode = 'Purchase';
				if(isset($post['plan_id']) && $post['plan_id'] != ""){
					$planData   = $this->db->query('select * from plan where plan_id = '.$post['plan_id'])->row_array();
					$comment = 'Take '.$planData['days'].' days Subscription.';
				}else{
					$planData   = $this->common_model->getVideoById($post['video_id'],$post['type']);
					$comment = 'Use point for  '.$post['type'].' '.$planData['title'];
				}
				$this->common_model->walletTransaction($post['user_id'],$post['amount'],$transactionType,$mode,$comment);
			}
			if($results['success'] == 1){
				$response['message'] = "";
			    $response['success'] = 1;
			}else{
				$response['message'] = "Opps.. Something went wrong.";
			    $response['success'] = 0;
			}
		}else{
			$response['message'] = "user id can not blank.";
			$response['success'] = 0;	
		}
		echo json_encode($response);
	}
	public function popular_search_video(){
		$post = $this->input->post();
		if(isset($post['search']) && $post['search']){
		$search = $post['search'];
		$cond = " and (title like '%".$search."%' or description_short like '%".$search."%' or description_long like '%".$search."%') ";
		$videoSearch =  $this->db->query('select video_id,type,title,description_short, description_long FROM movie where 1=1 '.$cond.' UNION ALL select video_id,type,title,description_short, description_long FROM series where 1=1 '.$cond.' order by video_id desc limit 20')->result_array();
			$count = 0;
			if(!empty($videoSearch)){
				foreach($videoSearch as $list){

					$response['videos'][$count]['video_id'] 	  = $list['video_id'];
					$response['videos'][$count]['type'] 		  = $list['type'];
					$response['videos'][$count]['description_short'] = $list['description_short'];
					$response['videos'][$count]['description_long'] = $list['description_long'];
					$response['videos'][$count]['title'] 		  = $list['title'];
					$response['videos'][$count]['video_thumb'] =  base_url().'assets/global/'.$list['type'].'_thumb/' . $list['video_id'] . '.jpg';
					$response['videos'][$count]['video_banner'] = base_url().'assets/global/'.$list['type'].'_poster/' . $list['video_id'] . '.jpg';;
					$count++;
				}
				$response['success'] = 1;
				$response['message'] = "";
			}else{
				$response['message'] = "No video found.";
				$response['success'] = 0;
			}
		echo json_encode($response);

		}
	}
	public function insert_search_data(){
		$post = $this->input->post();
		if(isset($post['video_id']) && $post['video_id']){
			if(isset($post['title']) && $post['title'])	{
				if(isset($post['type']) && $post['type']){
					$results = $this->common_model->insert_search_data($post['video_id'],$post['title'],$post['type']);
					if($results['success'] == 1){
						$response['success'] = 1;
						$response['message'] = "";
					}else{
						$response['success'] = 0;
						$response['message'] = "Opps.. Something went wrong.";
					}
				}else{
					$response['success'] = 0;
					$response['message'] = "type can not blank.";
				}
			}else{
				$response['success'] = 0;
				$response['message'] = "title can not blank.";
			}
		}else{
			$response['success'] = 0;
			$response['message'] = "video id can not blank.";
		}
		echo json_encode($response);
	}	
	public function popular_search_list(){
		$post = $this->input->post();
			if($post['page'] || !empty($post['page']) || $post['limit'] || !empty($post['limit'])){
				$curpage = $post['page'];
				$limit = $post['limit'];
			}else{
				$curpage = 1;
				$limit = 20;
			}

			$start      = ($curpage * $limit) - $limit;
			$search   	= $this->db->get('popular_search');
			$totlerec   = $search->num_rows();
			$endpage    = ceil($totlerec/$limit);
			$startpage  = 1;
			$nextpage   = $curpage + 1;
			$prevpage   = $curpage - 1;

			$DisplayLimit = " limit ".$start.",".$limit;

			$searchlistData = $this->db->query("select SQL_CALC_FOUND_ROWS * from popular_search where 1=1 order by hits DESC".$DisplayLimit)->result_array();
				$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
				$iFilteredTotal = $query->row()->myCounter;

			$count = 0;
			if(!empty($searchlistData)){
				foreach($searchlistData as $list){

					$response['videos'][$count]['video_id'] 	  = $list['video_id'];
					$response['videos'][$count]['type'] 		  = $list['type'];
					$response['videos'][$count]['title'] 		  = $list['title'];
					$response['videos'][$count]['video_thumb'] =  base_url().'assets/global/'.$list['type'].'_thumb/' . $list['video_id'] . '.jpg';
					$response['videos'][$count]['video_banner'] = base_url().'assets/global/'.$list['type'].'_poster/' . $list['video_id'] . '.jpg';;
					$count++;
				}
				$response['count'] = $iFilteredTotal;
				$response['success'] = 1;
				$response['message'] = "";
			}else{
				$response['message'] = "No video found.";
				$response['success'] = 0;
			}
		echo json_encode($response);
	}
	public function plan_list(){
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
		$plans   	= $this->db->get('plan');
		$totlerec   = $plans->num_rows();
		$endpage    = ceil($totlerec/$limit);
		$startpage  = 1;
		$nextpage   = $curpage + 1;
		$prevpage   = $curpage - 1;

		if($search == ""){
			$DisplayLimit = " limit ".$start.",".$limit;
		}

		$cond = "";

		if($search != ""){
			$cond .= " and (name like '%".$search."%') ";
		}

		$planData = $this->db->query("select SQL_CALC_FOUND_ROWS * from plan where 1=1 ".$cond." order by price ".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($planData)){
			foreach($planData as $plan){

				$response['plan'][$count]['plan_id']    = $plan['plan_id'];
				$response['plan'][$count]['name'] 		= $plan['name'];
				$response['plan'][$count]['price'] 		= $plan['price'];
				$response['plan'][$count]['days'] 		= $plan['days'];
				$response['plan'][$count]['status'] 	= $plan['status'] == 1 ? 'Active':'Inactive';
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
			$response['message'] = "";
		}else{
			$response['message'] = "No plan found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function wallet_history(){
		$post = $this->input->post();
		if(isset($post['user_id']) && $post['user_id']){
			$cond = "";
			if($post['start_date'] != "" && $post['end_date'] != ""){
				$cond .= " AND DATE(timestamp) BETWEEN '".$post['start_date']."' AND '" .$post['end_date']."'";
			}
			//DATE(created_at) BETWEEN '2011-12-01' AND '2011-12-06'

			$usedHistory = $this->db->query("select * from wallet where payment_type = 2 and user_id = ".$post['user_id'].$cond." order by wallet_id desc ")->result_array();
			$addHistory = $this->db->query("select * from wallet where payment_type = 1 and user_id = ".$post['user_id'].$cond." order by wallet_id desc ")->result_array();
	
			$count = 0;
			if(!empty($usedHistory) || !empty($addHistory)){
				foreach($addHistory as $add){
						$response['history']['add_point'][$count]['wallet_id']   = $add['wallet_id'];
						$response['history']['add_point'][$count]['amount'] 	 = $add['amount'];
						$response['history']['add_point'][$count]['comment'] 	 = $add['comment'];
						$response['history']['add_point'][$count]['timestamp']   = $add['timestamp'];
					$count++;
				}
				$count1 = 0;
				foreach($usedHistory as $used){
						$response['history']['used_point'][$count1]['wallet_id']   = $used['wallet_id'];
						$response['history']['used_point'][$count1]['amount'] 	   = $used['amount'];
						$response['history']['used_point'][$count1]['comment'] 	   = $used['comment'];
						$response['history']['used_point'][$count1]['timestamp']   = $used['timestamp'];
					$count1++;
				}
				$response['success'] = 1;
				$response['message'] = "";
			}else{
				$response['message'] = "No history found."; 
				$response['success'] = 0;
			}
		}else{
			$response['message'] = "user id can not blank.";
			$response['success'] = 0;
		}
		echo json_encode($response);
	}
public function pages($pageName){
	$action = array('contact_us','about_us','privacy_policy','terms_condition');
	if(in_array($pageName, $action)){
		if($pageName == 'contact_us'){
			$post = $this->input->post();
			if(isset($post['name']) && $post['name'] !=''){
				if(isset($post['email']) && $post['email'] !=''){
					if(isset($post['phone']) && $post['phone'] !=''){
						if(isset($post['message']) && $post['message'] !=''){
							    $insertData  = array(
						            'name'      =>$post['name'],
						            'email'     =>$post['email'],
						            'phone'     =>$post['phone'],
						            'message'   =>$post['message'],
						         );
							$result = $this->common_model->insertInquiry($insertData);
							if($result['success'] == 1){
							$sendInquiry = $this->email_model->send_inquiry_email($insertData);
								if($sendInquiry){
									$response['message'] = "your inquiry has been submited.";
									$response['success'] = 1;
								}else{
									$response['message'] = "Something went wrong!";
									$response['success'] = 0;
								}
							}else{
								$response['message'] = "Something went wrong!";
								$response['success'] = 0;
							}
						}else{
							$response['message'] = "Message con not be blank.";
							$response['success'] = 0;
						}
					}else{
						$response['message'] = "Phone con not be blank.";
						$response['success'] = 0;
					}
				}else{
					$response['message'] = "Email con not be blank.";
					$response['success'] = 0;
				}
			}else{
				$response['message'] = "Name con not be blank.";
				$response['success'] = 0;
			}
		}
		if($pageName == 'about_us'){
			$pageData = $this->db->get_where('pages',array("page_slug"=>'about_us'))->row_array();
			if(!empty($pageData)){
				$response['pageData']['pagename'] = $pageData['page_name'];
				$response['pageData']['pageLongText'] = $pageData['page_date'];
				$response['message'] = "";
				$response['success'] = 1;

			}else{
				$response['message'] = "no data found!";
				$response['success'] = 0;
			}
		}
		if($pageName == 'privacy_policy'){
			$pageData = $this->db->get_where('pages',array("page_slug"=>'privacy_policy'))->row_array();
			if(!empty($pageData)){
				$response['pageData']['pagename'] = $pageData['page_name'];
				$response['pageData']['pageLongText'] = $pageData['page_date'];
				$response['message'] = "";
				$response['success'] = 1;

			}else{
				$response['message'] = "no data found!";
				$response['success'] = 0;
			}
		}
		if($pageName == 'terms_condition'){
			$pageData = $this->db->get_where('pages',array("page_slug"=>'terms_condition'))->row_array();
			if(!empty($pageData)){
				$response['pageData']['pagename'] = $pageData['page_name'];
				$response['pageData']['pageLongText'] = $pageData['page_date'];
				$response['message'] = "";
				$response['success'] = 1;

			}else{
				$response['message'] = "no data found!";
				$response['success'] = 0;
			}
		}

	}else{
		$response['message'] = "invalid action.";
		$response['success'] = 0;	
	}
echo json_encode($response);
}
public function testPush(){
	$id = 15;
	$message = 'new movie added.';
	$opration = 'Movie Add.';
	echo $opration; die;
	$result = $this->common_model->push_notification($id,$message,$opration);
	echo json_encode($result);
}



}
