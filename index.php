<?php 
/**
 PHP API for Login, Register
 **/
 
 if(isset($_POST['tag']) && $_POST['tag'] != ''){
	 //Get the tag
	 $tag = $_POST['tag'];
	 
	 //Include Databse handler
	 require_once 'include/DB_Functions.php';
	 $db = new DB_Functions();
	 //Response Array
	 $response = array("tag" => $tag, "success" => 0, "error" => 0);
	 
	 //Check for tag type
	 if($tag == 'login'){
		 //Request type is check login
		 $username = $_POST['username'];
		 $password = $_POST['password'];
		 
		 //check for user_error
		 $user = $db->getUser($username, $password);
		 //print_r($user);
		 if($user != false){
			 //user found
			 //echo json with success = 1
			 $response["success"] = 1;
			 $response["user"]["username"] = $user["username"];
			 //$response["user"]["password"] = $user["encrypted_passwd"];	
			 $response["user"]["created"] = $user["created"];	
			//print_r($response);
			 echo json_encode($response);
		 }else{
			 //user not found
			 //echo json with error = 1.
			 $response["error"] = 1; 
			 $response["error_msg"] = "Incorrect username or password!";
			 echo json_encode($response);
		 }		 
	 }else if ($tag == 'register'){
		 //Request type is Register new user
		 $username = $_POST['username'];
		 $password = $_POST['password'];
		 
		 $subject = "Registration";
		 $message = "Hello $username,nnYou have sucessfully registered to our service.nnRegards,nAdmin.";
         $from = "balah balah";
         $headers = "From:" . $from;
		 
		 //Check if user is already existed 
		 if($db->isUserExisted($username)){
			// user is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = "User already existed";
            echo json_encode($response);
		 }else{
			 //Store user
			 $user = $db->storeUser($username,$password);
			 //print_r($user);
			 if($user){
				 //user store successfully
				 $response["success"] = 1;
				 $response["user"]["username"] = $user["username"];
				 //$response["user"]["password"] = $user["encrypted_passwd"];
				 $response["user"]["created"] = $user["created"];	
				 //mail($email,$subject,$message,$headers);
				 
				 echo json_encode($response);
			 } else {
                // user failed to store
                $response["error"] = 1;
                $response["error_msg"] = "JSON Error occured in Registartion";
                echo json_encode($response);
			 }
		 }
	 }else {
		$response["error"] = 3;
		$response["error_msg"] = "JSON ERROR";
		echo json_encode($response);
	 }
 }else{
	 echo "Time Management";
 }	
 ?>