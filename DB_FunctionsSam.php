<?php
class DB_Functions{
	private $db;
	
	//Constructor.
	function __construct(){
		require_once 'DB_Connect.php';
		//connecting to database.
		$this->db = new DB_Connect();
		$this->db->connect();
	}
	
	//Destructor.
	function __destruct(){
		
	}
	
	//Adding new user to mysql database, and return user details.
	public function storeUser($username,$password){
		$encrypted_password  = $this->hashSSHA($password); //encrypted password.
		
		$result = mysql_query("INSERT INTO user(username,passwd,created) VALUES('$username','$encrypted_password', NOW())");
		if($result){
			//get user details
			//$uid = mysql_insert_id(); //last inserted id
			$result = mysql_query("SELECT * FROM user WHERE username = '$username'");
			//print($username);
			//return user details.
			return @mysql_fetch_array($result);
		}else{
			return false;
		}
	}
	
	//Verifies user by username and password.
	public function getUser($username,$password){
		$result = mysql_query("SELECT * FROM user where username = '$username'") or die(mysql_error());
		//Check for result
		$numRows = mysql_num_rows($result);
		//print_r($numRows);
		if($numRows > 0){
			$result = @mysql_fetch_array($result);
			//print_r($result);
			$encrypted_password = $result['passwd'];
			//check for password equality.
			if($encrypted_password == crypt($password, $encrypted_password)){
				return $result; //user authentication are correct.
			}else{
				return false;	//user not found.
			}
		}
	}
	
	//Check user is existed or not.
	public function isUserExisted($username){
		$result = mysql_query("SELECT * FROM user WHERE username LIKE '$username'");
		$numRows = mysql_num_rows($result);
		if($numRows > 0){
			return true;	//user existed.
		}else{
			return false;	//user not existed.
		}
	}
	
	//Encrupting password. Returns salt and encrypted password.
	public function hashSSHA($password){
		return crypt($password);
	}
	
	//create reminder
	public function newReminder($num, $units, $linkedEv){
		$res = mysql_query("INSERT INTO reminder VALUES('$num', '$units', '$linkedEv')");
		
		if(!$res){
			return false;
		}else{
			return true;
		}	
	}
	
	//store course
	public function createCourse($notes, $s_date, $e_date, $crsnum, $credits, $e_time, $s_time, $namedept, $priority, $days, $location, $evuname){
		$res = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$days')");
								
		if($res == false){
			return false;
		}						
		
		$lastEventN = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		if(!$lastEventN){
			return false;
		}
		
		$lastEvento = @mysql_fetch_array($lastEventN, MYSQL_ASSOC);
		$lastn = $lastEvento['event_num'];
		
		$res2 = mysql_query("INSERT INTO course VALUES('$credits','$e_date', '$crsnum', '$lastn')");
		
		if(!$res2){
			mysql_query("DELETE FROM event WHERE event_num = '$lastn'");  //delete event created earlier in function
			
			return false;
		}else{
			$res2 = mysql_query("SELECT event_num, name_dept FROM event, course WHERE event_num = crs_e_num AND event_num = '$lastn'") or
			die(mysql_error());
			
			return @mysql_fetch_array($res2);
		}
	}
	
	//create multi event
	public function createMulti($notes, $s_date, $e_time, $s_time, $namedept, $priority, $days, $location, $evuname){
		$res = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$days')");
		
		if($res == false){
			return false;
		}						
		
		$lastEventN = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		if(!$lastEventN){
			return false;
		}
		
		$lastEvento = @mysql_fetch_array($lastEventN, MYSQL_ASSOC);
		$lastn = $lastEvento['event_num'];
		
		$res2 = mysql_query("INSERT INTO multi VALUES('$lastn')");
		
		if(!$res2){	
			mysql_query("DELETE FROM event WHERE event_num = '$lastn'");  //delete event created earlier in function
			
			return false;
		}else{
			$res2 = mysql_query("SELECT event_num, name_dept FROM event, multi WHERE event_num = multi_e_num AND event_num = '$lastn'") or
			die(mysql_error());
			
			return @mysql_fetch_array($res2);
		}
	}
	
	//store single event
	public function createOther($notes, $s_date, $e_time, $s_time, $namedept, $priority, $location, $evuname, $crsToLink){
		$dy1 = date('w', strtotime($s_date));
		
		//get day of the week of the event with start (and end) date s_date
		if($dy1 == 0){
			$dy1 = "S";  //sunday
		}else if($dy1 == 1){
			$dy1 = "M";  //monday
		}else if($dy1 == 2){
			$dy1 = "T";  //tuesday
		}else if($dy1 == 3){
			$dy1 = "W";  //wednesday
		}else if($dy1 == 4){
			$dy1 = "R";  //thursday
		}else if($dy1 == 5){
			$dy1 = "F";  //friday
		}else{
			$dy1 = "A";  //saturday
		}
		
		$result = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							    VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$dy1')");
		
		if($result == false){
			return false;
		}

		$lastEventNum = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		if($lastEventNum == false){
			return false;
		}
		
		$lastEventNo = @mysql_fetch_array($lastEventNum, MYSQL_ASSOC);
		$lastnum = $lastEventNo['event_num'];

		if($crsToLink == 'null'){
			$result2 = mysql_query("INSERT INTO single(single_e_date, single_e_num, course_eventnum)
								VALUES('$s_date', '$lastnum', null)");
		}else{
			$result2 = mysql_query("INSERT INTO single(single_e_date, single_e_num, course_eventnum)
								VALUES('$s_date', '$lastnum', '$crsToLink')");
		}
								
		if($result2){
			$result2 = mysql_query("SELECT event_num, name_dept FROM event, single WHERE event_num = single_e_num AND event_num = '$lastnum'") or
			die(mysql_error());
			
			return @mysql_fetch_array($result2);
		}else{
			mysql_query("DELETE FROM event WHERE event_num = '$lastnum'");  //delete event created earlier in function
			
			return false;
		}												
	}
	
	//delete event
	public function deleteEvent($eventnum){
		$res = mysql_query("DELETE FROM event WHERE event_num = '$eventnum'");
		
		if($res){
			return true;
		}else{
			return false;
		}
	}
	
	//delete user
	public function deleteAccount($usename){
		$res = mysql_query("DELETE FROM user WHERE username = '$usename'");
		
		if($res){
			return true;
		}else{
			return false;
		}
	}
	
	//delete reminder
	public function deleteReminder($num, $unit, $ev){
		$res = mysql_query("DELETE FROM reminder WHERE num = '$num' AND units = '$unit' AND remind_e_num = '$ev'");
		
		if($res){
			return true;
		}else{
			return false;
		}
	}
	
	//get every event for a user
	public function getAllEvents($username){
		$result = mysql_query("SELECT event.*, course.* FROM event, course WHERE event_num = crs_e_num AND '$username' = ev_uname") or
			die(mysql_error());
			
		$result2 = mysql_query("SELECT event.*, multi.* FROM event, multi WHERE event_num = multi_e_num AND '$username' = ev_uname") or
			die(mysql_error());
			
		$result3 = mysql_query("SELECT event.*, single.* FROM event, single WHERE event_num = single_e_num AND '$username' = ev_uname") or
			die(mysql_error());
			
		if(!$result && !$result2 && !$result3){
			return false;
		}else{
			$array1 = array("courses" => @mysql_fetch_array($result),
							"multi" => @mysql_fetch_array($result2),
							"single" => @mysql_fetch_array($result3));

			return $array1;
		}
	}
	
	//get event
	public function getEvent($eventnum){
		$typeC = mysql_query("SELECT * FROM course WHERE crs_e_num = '$eventnum'");
		$typeM = mysql_query("SELECT * FROM multi WHERE multi_e_num = '$eventnum'");
		$typeS = mysql_query("SELECT * FROM single WHERE single_e_num = '$eventnum'");
		
		$numC = mysql_num_rows($typeC);
		$numM = mysql_num_rows($typeM);
		$numS = mysql_num_rows($typeS);

		if($numC > 0){
			$table = "course";
			$fKey = "crs_e_num";
		}else if($numM > 0){
			$table = "multi";
			$fKey = "multi_e_num";
		}else if($numS > 0){
			$table = "single";
			$fKey = "single_e_num";
		}else{
			return false;
		}
		
		$result = mysql_query("SELECT event.*, $table.* FROM event, $table WHERE event_num = $fKey AND event_num = '$eventnum'") or
			die(mysql_error());
			
		if(!$result){
			return false;
		}else{
			return @mysql_fetch_array($result);
		}
	}
	
	//free time
	public function findFree($hr, $min, $range, $d){
		//convert to seconds
		$seconds = ($hr * 3600) + ($min * 60);
		$end = $seconds + ($range * 60);
		
		//create array, get day of the week of current date
		$date = $d;
		$times = array();
		$dyofweek = date('w', strtotime($date));
		
		//depending on day, set variable to check event days against
		if($dyofweek == 0){
			$dy = "%S%";
		}else if($dyofweek == 1){
			$dy = "%M%";
		}else if($dyofweek == 2){
			$dy = "%T%";
		}else if($dyofweek == 3){
			$dy = "%W%";
		}else if($dyofweek == 4){
			$dy = "%R%";
		}else if($dyofweek == 5){
			$dy = "%F%";
		}else{
			$dy = "%A%";
		}
		
		//check if any events overlap
		while($seconds < $end){
			$result = mysql_query("SELECT * FROM event, course WHERE 
								(SEC_TO_TIME('$seconds') BETWEEN s_time AND e_time)
				`		OR (SEC_TO_TIME('$seconds') < s_time AND ADDTIME((SEC_TO_TIME('$seconds')), '00:30:00') >= s_time)
				AND (STR_TO_DATE('$date', '%Y%m%d') BETWEEN s_date AND crs_e_date) AND (days like '$dy') AND (event_num = crs_e_num)");
				
			$result2 = mysql_query("SELECT * FROM event, single WHERE 
								(SEC_TO_TIME('$seconds') BETWEEN s_time AND e_time)
				`		OR (SEC_TO_TIME('$seconds') < s_time AND ADDTIME((SEC_TO_TIME('$seconds')), '00:30:00') >= s_time)
				AND (STR_TO_DATE('$date', '%Y%m%d') = s_date) AND (event_num = single_e_num)");
				
			$result3 = mysql_query("SELECT * FROM event, multi WHERE 
								(SEC_TO_TIME('$seconds') BETWEEN s_time AND e_time)
				`		OR (SEC_TO_TIME('$seconds') < s_time AND ADDTIME((SEC_TO_TIME('$seconds')), '00:30:00') >= s_time)
				AND (STR_TO_DATE('$date', '%Y%m%d') >= s_date) AND (days like '$dy') AND (event_num = single_e_num)");
			
			//if no events found
			if(!$result && !$result2 && !$result3)
				array_push($times, $seconds);
			
			$seconds = $seconds + 1800;		//look 30 minutes later
		}
		
		//if no times found 
		if(count($times) < 1)
				return false;
		
		return $times;  //otherwise return array
	}
	
	//get cuurent courses user is taking 
	public function getCurrentCourses($username, $currentDt){
		$result = mysql_query("SELECT * FROM event, course WHERE (CURDATE() BETWEEN s_date AND crs_e_date) AND (event_num = crs_e_num) 
								AND (ev_uname = '$username')") or die(mysql_error());
		
		
		
		if(!$result){
			return false;
		}else{
			return @mysql_fetch_array($result);
		}
	}
	
	//schedule study sessions (in progress)
	public function scheduleStudy($username, $currentDate){
		$crsInfo = $this->getCurrentCourses($username, $currentDate);
		
		//convert times to seconds
		$eH = idate('H', strtotime($crsInfo['e_time']));  
		$sH = idate('H', strtotime($crsInfo['s_time']));
		$sM = idate('i', strtotime($crsInfo['s_time']));
		$eM = idate('i', strtotime($crsInfo['e_time']));
		
		//calculate study time (2 hr rule)
		$studyTime = ((($eH - $sH)* 60) + ($eM - $sM)) * 2;

		//add study time based on course number
		if($crsInfo['crs_num'] >= 300 && $crsInfo['crs_num'] < 400){
			$studyTime = $studyTime + 30;
		}else if($crsInfo['crs_num'] >= 400){
			$studyTime = $studyTime + 60;
		}else{}
		
		//calculate number of study sessions for class
		$sessions = $studyTime/30;
		$sessions = round($sessions);
		echo $studyTime;
		echo $sessions;
		
		//new variable for days
		$days = (string)$crsInfo['days'];
		$numDys = strlen($days);
		//course number
		$number = (string)$crsInfo['crs_num'];
		//current date to php date type
		$curdate = new DateTime($currentDate);
		
		//for($i = 0; i < $numDys; $i++){
			//echo "working\n\n\n";
			
			//add days to current date depending on day of week in loop (it is assumed the function will start scheduling on Mondays)
			if($days[0] == 'M'){
				$curdate->add(new DateInterval('P0D'));
			}else if($days[0] == 'T'){
				$curdate->add(new DateInterval('P1D'));
			}else if($days[0] == 'W'){
				$curdate->add(new DateInterval('P2D'));
			}else if($days[0] == 'R'){
				$curdate->add(new DateInterval('P3D'));
			}else if($days[0] == 'F'){
				$curdate->add(new DateInterval('P4D'));
			}else if($days[0] == 'A'){
				$curdate->add(new DateInterval('P5D'));
			}else{
				$curdate->add(new DateInterval('P6D'));
			}
			
			echo $curdate->format('Y-m-d');
			
			//find free times 
			$freeTimes = $this->findFree(17, 0, ($sessions * 30 * 2), $curdate->format('Y-m-d'));
			//print_r($freeTimes);
			
			//schedule study times (as single events for now, might switch to multi events if single events yield too many events)
			if(sizeof($freeTimes) >= $sessions * 2){
				//for($x = 0; $x < $sessions; $x++){
					$sT = gmdate('H:i:s', $freeTimes[0 * 2]);         //format start time 
					$eT = gmdate('H:i:s', $freeTimes[0 * 2] + 1800);  //format end time
					$this->createOther('', $curdate->format('Y-m-d'), $eT, $sT, "study " . $number, $crsInfo['priority'], $crsInfo['location'], $username, $crsInfo['event_num']);
				//}
			}else{
				//for($x = 0; $x < $sessions; $x++){
					$sT = gmdate('H:i:s', $freeTimes[0]);            //format start time
					$eT = gmdate('H:i:s', $freeTimes[0] + 1800);     //format end time
					$this->createOther('', $curdate->format('Y-m-d'), $eT, $sT, "study " . $number, $crsInfo['priority'], $crsInfo['location'], $username, $crsinfo['event_num']);
				//}
			}
		//}
	}
}
?>