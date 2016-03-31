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
		 
		 $single = $db->createOther($notes, $sdate, $etime, $stime, $namedept, $priority, $location, $evuname);
		 
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
	 }else if($tag == 'mult'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evuname = $_POST['evuname'];
		 $days = $_POST['days'];
		 
		 $multi = $db->createMulti($notes, $sdate, $etime, $stime, $namedept, $priority, $days, $location, $evuname);
		 
		 if($multi){
			$response["success"] = 1;
			$response["event_num"] = $multi['event_num'];
			$response["namedept"] = $multi['name_dept'];
			$response["msg"] = "course created!";
			echo json_encode($response);
		 }else{
			 $response["error"] = 65;
			 $response["error_msg"] = "multi create failed";
			 echo json_encode($response);
		 } 
	 }else if($tag == 'remind'){
		 $num = $_POST['num'];
		 $units = $_POST['units'];
		 $even = $_POST['even'];
		 
		 $remind = $db->newReminder($num, $units, $even);
		 
		 if($remind){
			 $response["success"] = 1;
			 $response["msg"] = "reminder added";
			 echo json_encode($response);
		 }else{
			 $response["error"] = 94;
			 $response["error_msg"] = "reminder failed";
			 echo json_encode($response);
		 }
		 
	 }else if($tag == 'delete'){
		 $evnum = $_POST['evnum'];
		 
		 $del = $db->deleteEvent($evnum);
		 
		 if($del){
			 $response["success"] = 1;
			 $response["msg"] = "event deleted";
			 echo json_encode($response);
		 }else{
			 $response["error"] = 45;
			 $response["error_msg"] = "event still around";
			 echo json_encode($response);
		 }
	 }else if($tag == 'norem'){
		 $n = $_POST['n'];
		 $u = $_POST['u'];
		 $e = $_POST['e'];
		 
		 $delRem = $db->deleteReminder($n, $u, $e);
		 
		 if($delRem){
			 $response["success"] = 1;
			 $response["msg"] = "reminder deleted";
			 echo json_encode($response);
		 }else{
			 $response["error"] = 48;
			 $response["error_msg"] = "reminder still around";
			 echo json_encode($response);
		 }	 
	 }else if($tag == 'deluse'){
		 $usenm = $_POST['usenm'];
		 
		 $delUser = $db->deleteAccount($usenm);
		 
		  if($delUser){
			 $response["success"] = 1;
			 $response["msg"] = "account deleted";
			 echo json_encode($response);
		 }else{
			 $response["error"] = 45;
			 $response["error_msg"] = "account still around";
			 echo json_encode($response);
		 }
	 }else if($tag == 'cour'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evuname = $_POST['evuname'];
		 $edate = $_POST['edate'];
		 $creds = $_POST['creds'];
		 $crsnum = $_POST['crsnum'];
		 $crstl = $_POST['crstl'];
		 $days = $_POST['days'];
		 
		 $course = $db->createCourse($notes, $sdate, $edate, $crsnum, $creds, $etime, $stime, $namedept, $priority, $days, $location, $evuname, $crstl);
		 
		 if($course){
			$response["success"] = 1;
			$response["event_num"] = $course['event_num'];
			$response["namedept"] = $course['name_dept'];
			$response["msg"] = "course created!";
			echo json_encode($response);
		 }else{
			 $response["error"] = 64;
			 $response["error_msg"] = "course create failed";
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
		 
	 }else if($tag == 'gete'){
		 $e = $_POST['e'];
		 
		 $event = $db->getEvent($e);
		 
		 if($event){
			 $response["success"] = 1;
			 $response["evInfo"] = $event;
			 echo json_encode($response);
		 }else{
			 $response["error"] = 21;
			 $response["error_msg"] = "event not found";
			 echo json_encode($response);
		 }
	 }else if($tag == 'all'){
		 $u = $_POST['u'];
		 
		 $events = $db->getAllEvents($u);
		 print_r($events);
		 if($events){
			 $response["success"] = 1;
			 $response["evInfo"] = $events;
			 echo json_encode($response);
		 }else{
			 $response["error"] = 23;
			 $response["error_msg"] = "no events found";
			 echo json_encode($response);
		 } 
	 }else if($tag == 'rems'){
		 $eno = $_POST['eno'];
		 
		 $reminders = $db->getReminder($eno);
		 
		 if($reminders){
			$response["success"] = 1;
			$response["remInfo"] = $reminders;
			echo json_encode($response);
		 }else{
			 $response["error"] = 24;
			 $response["error_msg"] = "No reminders for this event";
			 echo json_encode($response);
		 }
	 }else if($tag == 'allrem'){
		 $unam = $_POST['unam'];
		 
		 $rems = $db->getReminders($unam);
		 
		 if($rems){
			 $response["success"] = 1;
			 $response["remInfo"] = $rems;
			 echo json_encode($response);
		 }else{
			 $response["error"] = 234;
			 $response["error_msg"] = "No reminders";
			 echo json_encode($response);
		 }
	 }else if($tag == 'chng'){
		 $unm = $_POST['unm'];
		 $new = $_POST['new'];
		 
		 $prestochango = $db->changePasswd($unm, $new);
		 
		 if($prestochango){
			$response["success"] = 1;
			$response["message"] = "password changed";
			echo json_encode($response);
		 }else{
			 $response["error"] = 29;
			 $response["error_msg"] = "couldnt change password";
			 echo json_encode($response);
		 }
	 }else if($tag == 'upcrs'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evn = $_POST['evn'];
		 $days = $_POST['days'];
		 $edate = $_POST['edate'];
		 $creds = $_POST['creds'];
		 $crsnum = $_POST['crsnum'];
		 
		 $upCrs = $db->updateCrs($notes, $evn, $sdate, $edate, $crsnum, $creds, $etime, $stime, $namedept, $priority, $days, $location);
		 
		 if($upCrs){
			$response["success"] = 1;
			echo json_encode($response);
		 }else{
			 $response["error"] = 122;
			 $response["error_msg"] = "couldnt alter event";
			 echo json_encode($response);
		 }
	 }else if($tag == 'upmul'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evn = $_POST['evn'];
		 $days = $_POST['days'];
		 
		 $upMult = $db->updateMulti($notes, $sdate, $etime, $stime, $namedept, $priority, $days, $location, $evn);
		 
		 if($upMult){
			$response["success"] = 1;
			echo json_encode($response);
		 }else{
			 $response["error"] = 123;
			 $response["error_msg"] = "couldnt alter event";
			 echo json_encode($response);
		 }
		 
	 }else if($tag == 'upsing'){
		 $notes = $_POST['notes'];
		 $sdate = $_POST['sdate'];
		 $etime = $_POST['etime'];
		 $stime = $_POST['stime'];
		 $namedept = $_POST['namedept'];
		 $priority = $_POST['priority'];
		 $location = $_POST['location'];
		 $evn = $_POST['evn'];
		 $crstl = $_POST['crstl'];
		 $d = $_POST['d'];
		 
		 $upSin = $db->updateSingle($d, $notes, $sdate, $etime, $stime, $namedept, $priority, $location, $crstl, $evn);
		 
		 if($upSin){
			$response["success"] = 1;
			echo json_encode($response);
		 }else{
			 $response["error"] = 124;
			 $response["error_msg"] = "couldnt alter event";
			 echo json_encode($response);
		 }
		 
	// }//else if($tag == ){
		 
	 }else if($tag == 'cur'){
		 $uname = $_POST['uname'];
		 $dt = $_POST['dt'];
		 
		 $curCourses = $db->getCurrentCourses($uname,$dt);
		 
		 if($curCourses){
			 $response["success"] = 1;
			 $response["crses"] = $curCourses;
			 echo json_encode($response);
		 }else{
			 $response["error"] = 10;
			 $response["error_msg"] = "No current courses";
			 echo json_encode($response);
		 }
	 }else if($tag == 'order'){
		 $u = $_POST['u'];
		 $c = $_POST['c'];
		 
		 $ord = $db->orderCourses($u, $c);
		 
		 $response["success"] = 1;
		 $response["ordered"] = $ord;
		 echo json_encode($response);
		 
	 }else if($tag == 'note'){
		 $eveno = $_POST['eveno'];
		 
		 $noteLength = $db->noteLen($eveno);
		 
		 $response["success"] = 1;
		 $response["notelen"] = $noteLength;
		 echo json_encode($response);
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