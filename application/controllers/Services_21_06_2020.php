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

	public function director_list(){
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
		$users   	= $this->db->get('director');
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

		$directors = $this->db->query("select SQL_CALC_FOUND_ROWS * from director where 1=1 ".$cond." order by director_id DESC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

		$count = 0;
		if(!empty($directors)){
			foreach($directors as $director){

				$response['director'][$count]['director_id'] =  $director['director_id'];
				$response['director'][$count]['name'] 		 = $director['name'];
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No director found";
			$response['success'] = 0;
		}
		
		echo json_encode($response);
	}
	public function movie_list(){
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
		$users   	= $this->db->get('movie');
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
			$cond .= " and (title like '%".$search."%' or description_short like '%".$search."%' or description_long like '%".$search."%' or year like '%".$search."%' or genre_id in (select genre_id from genre where name like '%".$search."%')) ";
		}

		$movies = $this->db->query("select SQL_CALC_FOUND_ROWS * from movie where 1=1 ".$cond." order by movie_id DESC".$DisplayLimit)->result_array();
			$query = $this->db->query('SELECT FOUND_ROWS() as myCounter');
			$iFilteredTotal = $query->row()->myCounter;

			var_dump($movies);
			die;
		$count = 0;
		if(!empty($movies)){
			foreach($movies as $movie){

				$response['movie'][$count]['movie_id'] 			=  $movie['movie_id'];
				$response['movie'][$count]['title'] 			=  $movie['title'];
				$response['movie'][$count]['description_short'] =  $movie['description_short'];
				$response['movie'][$count]['description_long']  =  $movie['description_long'];
				$response['movie'][$count]['year'] 				=  $movie['year'];
				$response['movie'][$count]['price'] 			=  $movie['price'];
				$response['movie'][$count]['rating'] 			=  $movie['rating'];
				$response['movie'][$count]['genre_id'] 			=  $movie['genre_id'];
				$response['movie'][$count]['genre'] 			=  $movie['genre'];
				$response['movie'][$count]['categories'] 		=  $movie['categories'];
				$response['movie'][$count]['actors'] 			=  $movie['actors'];
				$response['movie'][$count]['director'] 			=  $movie['director'];
				$response['movie'][$count]['featured'] 			=  $movie['featured'];
				$response['movie'][$count]['kids_restriction'] 	=  $movie['kids_restriction'];
				$response['movie'][$count]['movie_url'] 		=  $movie['url'];
				$response['movie'][$count]['trailer_url'] 		=  $movie['trailer_url'];
				$response['movie'][$count]['duration'] 		    =  $movie['duration'];
				$response['movie'][$count]['movie_thumb'] 		=  $movie['movie_thumb'];
				$response['movie'][$count]['movie_poster'] 		=  $movie['movie_poster'];
				$response['movie'][$count]['created'] 		    =  $movie['created_timestamp'];
				$response['movie'][$count]['modefied'] 		    =  $movie['modefied_timestamp'];
				$count++;
			}
			$response['count'] = $iFilteredTotal;
			$response['success'] = 1;
		}else{
			$response['message'] = "No movie found";
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
}
