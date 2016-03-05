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
			return false;
		}

		$lastEventNum = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		if($lastEventNum == false){
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
	public function findFree($hr, $min, $range){
		$seconds = ($hr * 3600) + ($min * 60);
		$end = $seconds + ($range * 60);
		$times = array();
		
		while($seconds < $end){
			$result = mysql_query("SELECT * FROM event WHERE 
								(SEC_TO_TIME('$seconds') BETWEEN s_time AND e_time)
				`		OR (SEC_TO_TIME('$seconds') < s_time AND ADDTIME((SEC_TO_TIME('$seconds')), '00:30:00') >= s_time)");
			
			if(!$result)
				array_push($times, $seconds);
			
			$seconds = $seconds + 1800;		
		}
		
		if(count($times) < 1)
				return false;
		
		return $times;
	}
	
}
?>