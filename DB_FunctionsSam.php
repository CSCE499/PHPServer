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
	
	//store single event
	public function createOther($notes, $s_date, $e_time, $s_time, $namedept, $priority, $location, $evuname, $crsToLink){
		
		
		$result = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							    VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', null)");
		
		if($result == false){
			//echo "false false  false";
			return false;
		}

		$lastEventNum = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		if($lastEventNum == false){
			//echo "false false false false";
			return false;
		}
		
		$lastEventNo = @mysql_fetch_array($lastEventNum, MYSQL_ASSOC);
		$lastnum = $lastEventNo['event_num'];

		if($crsToLink == null){
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
			//echo "false last";
			return false;
		}												
	}
	
	//get single event
	public function getSingle($eventnum, $username){
		$result = mysql_query("SELECT event.*, single.* FROM event, single, user WHERE event_num = single_e_num AND event_num = '$eventnum' AND username = '$username'") or
			die(mysql_error());
			
		$numRows = mysql_num_rows($result);
		
		if($numRows > 0){
			return @mysql_fetch_array($result);
		}else{
			return false;
		}	
	}
	
	//free time
	public function findFree($hr, $min, $range, $d){
		$seconds = ($hr * 3600) + ($min * 60);
		$end = $seconds + ($range * 60);
		$date = $d;
		$times = array();
		$dyofweek = date('w', strtotime($date));
		
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
			
			if(!$result && !$result2 && !$result3)
				array_push($times, $seconds);
			
			$seconds = $seconds + 1800;		
		}
		
		if(count($times) < 1)
				return false;
		
		return $times;
	}
	
	public function getCurrentCourses($username, $currentDt){
		$result = mysql_query("SELECT * FROM event, course WHERE (CURDATE() BETWEEN s_date AND crs_e_date) AND (event_num = crs_e_num) 
								AND (ev_uname = '$username')") or die(mysql_error());
		
		
		
		if(!$result){
			return false;
		}else{
			return @mysql_fetch_array($result);
		}
	}
	
	public function scheduleStudy($username, $currentDate){
		$crsInfo = $this->getCurrentCourses($username, $currentDate);
		
		$eH = idate('H', strtotime($crsInfo['e_time']));
		$sH = idate('H', strtotime($crsInfo['s_time']));
		$sM = idate('i', strtotime($crsInfo['s_time']));
		$eM = idate('i', strtotime($crsInfo['e_time']));
		$studyTime = ((($eH - $sH)* 60) + ($eM - $sM)) * 2;

		if($crsInfo['crs_num'] >= 300 && $crsInfo['crs_num'] < 400){
			$studyTime = $studyTime + 30;
		}else if($crsInfo['crs_num'] >= 400){
			$studyTime = $studyTime + 60;
		}else{}
		
		$sessions = $studyTime/30;
		$sessions = round($sessions);
		echo $studyTime;
		echo $sessions;
		
		$days = (string)$crsInfo['days'];
		$numDys = strlen($days);
		
		$number = (string)$crsInfo['crs_num'];
		
		$curdate = new DateTime($currentDate);
		
		//for($i = 0; i < $numDys; $i++){
			//echo "working\n\n\n";
			
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
			
			$freeTimes = $this->findFree(17, 0, ($sessions * 30 * 2), $curdate->format('Y-m-d'));
			//print_r($freeTimes);
			if(sizeof($freeTimes) >= $sessions * 2){
				//for($x = 0; $x < $sessions; $x++){
					$sT = gmdate('H:i:s', $freeTimes[0 * 2]);
					$eT = gmdate('H:i:s', $freeTimes[0 * 2] + 1800);
					$this->createOther('', $curdate->format('Y-m-d'), $eT, $sT, "study " . $number, $crsInfo['priority'], $crsInfo['location'], $username, $crsInfo['event_num']);
				//}
			}else{
				//for($x = 0; $x < $sessions; $x++){
					$sT = gmdate('H:i:s', $freeTimes[0]);
					$eT = gmdate('H:i:s', $freeTimes[0] + 1800);
					$this->createOther('', $curdate->format('Y-m-d'), $eT, $sT, "study " . $number, $crsInfo['priority'], $crsInfo['location'], $username, $crsinfo['event_num']);
				//}
			}
		//}
	}
}
?>