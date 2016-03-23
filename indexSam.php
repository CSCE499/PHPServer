<?php 
/**
 PHP API for Login, Register
 **/
 
 if(isset($_POST['tag']) && $_POST['tag'] != ''){
	 //Get the tag
	 $tag = $_POST['tag'];
	 
	 //Include Databse handler
	 require_once 'include/DB_FunctionsSam.php';
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
	 }else if($tag == 'single'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evuname = $_POST['evuname'];
		 $crstolink = $_POST["crstolink"];
		 
		 $single = $db->createOther($notes, $sdate, $etime, $stime, $namedept, $priority, $location, $evuname, $crstolink);
		 
		 if($single != false){
			$response["success"] = 1;
			$response["event_num"] = $single['event_num'];
			$response["namedept"] = $single['name_dept'];
			$response["msg"] = "event created!";
			echo json_encode($response);
		 }else{
			 $response["error"] = 7;
			 $response["error_msg"] = "cant do it";
			 echo json_encode($response);
		 }
	 }else if($tag == 'view'){
		$eventnum = $_POST['eventnum'];
		$username = $_POST['username'];
		
		$single = $db->getSingle($eventnum, $username);
		
		if($single != false){
			$response["success"] = 1;
			$response["attribute1"] = $single['notes'];
			$response["attribute2"] = $single['s_date'];
			$response["attribute3"] = $single['e_time'];
			$response["attribute4"] = $single['s_time'];
			$response["attribute5"] = $single['name_dept'];
			$response["attribute6"] = $single['priority'];
			$response["attribute7"] = $single['location'];
			$response["attribute8"] = $single['event_num'];
			$response["attribute9"] = $single['ev_uname'];
			$response["attribute10"] = $single['days'];
			$response["attribute11"] = $single['single_e_date'];
			$response["attribute12"] = $single['single_e_num'];
			$response["attribute13"] = $single['course_eventnum'];
			echo json_encode($response);
		}else{
			$response["error"] = 8;
			$response["error_msg"] = "no event found";
			echo json_encode($response);
		}
	 
	 }else if($tag == 'free'){
		 $hr = $_POST['hr'];
		 $minute = $_POST['minute'];
		 $range = $_POST['range'];
		 $date = $_POST['date'];
		 
		 $freetimes = $db->findFree($hr, $minute, $range, $date);
		 
		 if($freetimes){
			 $response["success"] = 1;
			 $response["free"] = gmdate("H:i:s", $freetimes[0]);
			 echo json_encode($response);
		 }else{
			 $response["error"] = 5;
			 $response["error_msg"] = "No free time found";
			 echo json_encode($response);
		 }
		 
	 }else if($tag == 'cur'){
		 $uname = $_POST['uname'];
		 $dt = $_POST['dt'];
		 
		 $curCourses = $db->getCurrentCourses($uname,$dt);
		 
		 if($curCourses){
			 $response["success"] = 1;
			 $response["crses"] = $curCourses[0];
			 echo json_encode($response);
		 }else{
			 $response["error"] = 10;
			 $response["error_msg"] = "No current courses";
			 echo json_encode($response);
		 }
	 }else if($tag == 'calcu'){
		 $uname = $_POST['uname'];
		 $today = $_POST['today'];
		 
		 $study = $db->scheduleStudy($uname, $today);
		 
		 if($study){
			 $response["success"] = 1;
		 }else{
			 $response["error"] = 12;
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