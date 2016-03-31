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
		//run insert on reminder table to create reminder (almost seems too simple)
		$res = mysql_query("INSERT INTO reminder VALUES('$num', '$units', '$linkedEv')");
		
		//if insertion unsuccessful, return false
		if(!$res){
			return false;
		}else{  //if successful, return true
			return true;
		}	
	}
	
	//store course
	public function createCourse($notes, $s_date, $e_date, $crsnum, $credits, $e_time, $s_time, $namedept, $priority, $days, $location, $evuname, $crsToLink){
		//insert appropriate data into event table
		
		if($days == 'find')
			$days = $this->getDayOfWk($s_date);
		
		$res = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$days')");
		
		
		//if insert fails, return false
		if($res == false){
			return false;
		}						

		//get event number of last created event 
		$lastEventN = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		//if unable to retrieve newest event number
		if(!$lastEventN){
			return false;
		}
		
		//extrapolate the newest event number & prepare it for insertion into course table
		$lastEvento = @mysql_fetch_array($lastEventN, MYSQL_ASSOC);
		$lastn = $lastEvento['event_num'];
		
		//run insert on the course table to complete the course event
		if($crsToLink == 'null'){
			$res2 = mysql_query("INSERT INTO course VALUES('$credits','$e_date', '$crsnum', '$lastn', null)");
		}
		else{
			$res2 = mysql_query("INSERT INTO course VALUES('$credits','$e_date', '$crsnum', '$lastn', '$crsToLink')");
		}

		//if creation of course event was unsuccessful
		if(!$res2){
			//delete event row from event table inserted earlier in function
			mysql_query("DELETE FROM event WHERE event_num = '$lastn'");  //delete event created earlier in function
			
			return false;  //return not not not not not not not not not not not not not not not not not not not not not not not not not true
		}else{ 
				 //if creation of course event was not unsuccessful and the process was in fact completed, therefore and thusly rendering 
				 //the variable res2 not false, as it would have been if it were not true, but on the other hand not not true, which means 
				 //that the false version of the variable is false, making it a falsity.  So if the true variable res2 was false, it would not 
				 //be true, but this is not a consideration since it is a false falsity and therefore true.  The false falseness, or as you may
				 //and/or might want to think of it, truth of this non-false fact means that the process of creating a course event was not 
				 //aborted for any reason whatsoever, known or unknown, and the course was unequivocally and undoubtedly inserted with great
				 //care into the proper tables (puting them in the improper tables would be unwise, and generally improper).  What we have
				 //proven through all of this is that false falsities are true except when their false versions are true, and that somewhere 
				 //along the line a course event was somehow added to the user's calendar.  We also learned not to write comments when drowsy
				 
				 //have fun reading that, whoever ^^^
			
			//send back select data as proof of creation
			$res2 = mysql_query("SELECT event_num, name_dept FROM event, course WHERE event_num = crs_e_num AND event_num = '$lastn'") or
			die(mysql_error());
			
			return @mysql_fetch_array($res2);  //return selected proof data
		}
	}
	
	//create multi event
	public function createMulti($notes, $s_date, $e_time, $s_time, $namedept, $priority, $days, $location, $evuname){
		//insert appropriate data into event table in db
		$res = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$days')");
		
		//if insert fails
		if($res == false){
			return false;
		}						
		
		//run query to get event number of the event just created
		$lastEventN = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		//if getting the last event number fails
		if(!$lastEventN){
			return false;
		}
		
		//extract last event number
		$lastEvento = @mysql_fetch_array($lastEventN, MYSQL_ASSOC);
		$lastn = $lastEvento['event_num'];
		
		//insert data into multi table
		$res2 = mysql_query("INSERT INTO multi VALUES('$lastn')");
		
		//if creation of event not successfully completed
		if(!$res2){	
			//delete event created earlier in the function
			mysql_query("DELETE FROM event WHERE event_num = '$lastn'");  //delete event created earlier in function
			
			return false;   //& return false
		}else{ //event successfully created
			//get some data to send back as proof of creation
			$res2 = mysql_query("SELECT event_num, name_dept FROM event, multi WHERE event_num = multi_e_num AND event_num = '$lastn'") or
			die(mysql_error());
			
			//return select data
			return @mysql_fetch_array($res2);
		}
	}
	
	//get day of week for specific date
	public function getDayOfWk($date){
		$dt = date('w', strtotime($date));
		
		if($dt == 0){
			$dt = "S";  //sunday
		}else if($dt == 1){
			$dt = "M";  //monday
		}else if($dt == 2){
			$dt = "T";  //tuesday
		}else if($dt == 3){
			$dt = "W";  //wednesday
		}else if($dt == 4){
			$dt = "R";  //thursday
		}else if($dt == 5){
			$dt = "F";  //friday
		}else{
			$dt = "A";  //saturday
		}
		
		return $dt;
	}
	
	//store single event
	public function createOther($notes, $s_date, $e_time, $s_time, $namedept, $priority, $location, $evuname){
		$dy1 = date('w', strtotime($s_date));   //get day of the week (represented by integer 0(sunday) - 6(saturday))
		
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
		
		//insert appropriate values into event table
		$result = mysql_query("INSERT INTO event(notes, s_date, e_time, s_time, name_dept, priority, location, ev_uname, days)
							    VALUES('$notes', '$s_date', '$e_time', '$s_time', '$namedept', '$priority', '$location', '$evuname', '$dy1')");
		
		//if insert fails
		if($result == false){
			return false;
		}

		//run query to get the event number of the newest event created (works because event_num is set to auto-increment)
		$lastEventNum = mysql_query("SELECT event_num FROM event ORDER BY event_num DESC  LIMIT 1") or die(mysql_error());
		
		//if the last event number cannot be pulled from db
		if($lastEventNum == false){
			return false;
		}
		
		//get last event number from array returned
		$lastEventNo = @mysql_fetch_array($lastEventNum, MYSQL_ASSOC);
		$lastnum = $lastEventNo['event_num'];

		//run necessary inserts for the single table depending on crsToLink (if its a study session or not)
		/*if($crsToLink == 'null'){
			$result2 = mysql_query("INSERT INTO single(single_e_date, single_e_num, course_eventnum)
								VALUES('$s_date', '$lastnum', null)");   //foreign key null if not a study session
		}else{*/
			$result2 = mysql_query("INSERT INTO single(single_e_date, single_e_num)
								VALUES('$s_date', '$lastnum')");  //set fk to crsToLink if it is a study session
		//}
		
		//if event successfully created 
		if($result2){
			//get some data from new event to send back as proof of event creation
			$result2 = mysql_query("SELECT event_num, name_dept FROM event, single WHERE event_num = single_e_num AND event_num = '$lastnum'") or
			die(mysql_error());
			
			//return selected info
			return @mysql_fetch_array($result2); 
		}else{   //if event not created successfully
			//delete the data from the event table inserted earlier in the function
			mysql_query("DELETE FROM event WHERE event_num = '$lastnum'");  //delete event created earlier in function
			
			return false;  //return false
		}												
	}
	
	//change course event
	public function updateCrs($notes, $evnum, $s_date, $e_date, $crsnum, $credits, $e_time, $s_time, $namedept, $priority, $days, $location){
		//run necessary update on event table for course event indicated by $evnum
		$result = mysql_query("UPDATE event SET notes = '$notes', s_date = '$s_date', e_time = '$e_time', s_time = '$s_time', 
							name_dept = '$namedept', priority = $priority, days = '$days', location = '$location' WHERE event_num = $evnum");
			
		//if update fails
		if(!$result){
			return false;
		}	
	
		//run update on necessary attributes of the course table
		$result = mysql_query("UPDATE course SET crs_num = $crsnum, credits = $credits, crs_e_date = '$e_date' WHERE crs_e_num = '$evnum'");
		
		//if that update fails
		if(!$result){
			return false;
		}
		return true;
	}
	
	//change multi day repeating event
	public function updateMulti($notes, $s_date, $e_time, $s_time, $namedept, $priority, $days, $location, $evnum){
		//update attributes for multi day repeating event
		$result = mysql_query("UPDATE event SET notes = '$notes', s_date = '$s_date', e_time = '$e_time', s_time = '$s_time', 
							name_dept = '$namedept', priority = $priority, days = '$days', location = '$location' WHERE event_num = $evnum");
			
		//if update fails
		if(!$result){
			return false;
		}

		//no pertinent data to update in the multi table; all of it lies in the event table
		
		return true;
	}
	
	//change single event
	public function updateSingle($day, $notes, $s_date, $e_time, $s_time, $namedept, $priority, $location, $crsToLink, $evnum){
		//update everyting relevant on the single event given by $evnum (easier to just update everything)
		$result = mysql_query("UPDATE event SET notes = '$notes', s_date = '$s_date', e_time = '$e_time', s_time = '$s_time', 
							name_dept = '$namedept', priority = $priority, days = '$day',location = '$location' WHERE event_num = $evnum");
		
		//if update fails
		if(!$result){
			return false;
		}	
	
		//update on applicable attributes of the single table
		$result = mysql_query("UPDATE single SET single_e_date = '$s_date' WHERE single_e_num = '$evnum'");
		
		//if that update fails
		if(!$result){
			return false;
		}
		return true;
	}
	
	//change time of reminder
	public function updateRemind($amount, $unit, $evnum){
		//run update on given reminder
		$result = mysql_query("UPDATE reminder SET num = '$amount', units = '$unit' WHERE remind_e_num = '$evnum' AND num = '$amount' AND units = '$unit'");
		
		//if update fails
		if(!$result)
			return false;
		
		return true;
		
	}
	
	//change password for specific user
	public function changePasswd($uname, $newWd){
		$safePwd = crypt($newWd);  //encrypt new password
		
		//run update and set passed to the new new encrypted password for given user
		$result = mysql_query("UPDATE user SET passwd = '$safePwd' WHERE username = '$uname'");
		
		//if update fails
		if(!$result)
			return false;
		
		return true;
	}
	
	//delete event
	public function deleteEvent($eventnum){
		//delete event specified by given event number from db
		$res = mysql_query("DELETE FROM event WHERE event_num = '$eventnum'");
		
		//if delete successful
		if($res){
			return true;
		}else{
			return false;   //if not successful
		}
	}
	
	//delete user
	public function deleteAccount($usename){
		//delete user with given username from db
		$res = mysql_query("DELETE FROM user WHERE username = '$usename'");
		
		//if delete successful
		if($res){
			return true;
		}else{
			return false;   //if not successful
		}
	}
	
	//delete reminder
	public function deleteReminder($num, $unit, $ev){
		//run delete operation
		$res = mysql_query("DELETE FROM reminder WHERE num = '$num' AND units = '$unit' AND remind_e_num = '$ev'");
		
		//if delete successful, return true, otherwise return false
		if($res){
			return true;
		}else{
			return false;
		}
	}
	
	//get every event for a user
	public function getAllEvents($username){
		//get all course events associated with the given user
		$result = mysql_query("SELECT event.*, course.* FROM event, course WHERE event_num = crs_e_num AND '$username' = ev_uname") or
			die(mysql_error());
		//get all multi day repeating events associated with the given user
		$result2 = mysql_query("SELECT event.*, multi.* FROM event, multi WHERE event_num = multi_e_num AND '$username' = ev_uname") or
			die(mysql_error());
		//get all single events associated with the given user		
		$result3 = mysql_query("SELECT event.*, single.* FROM event, single WHERE event_num = single_e_num AND '$username' = ev_uname") or
			die(mysql_error());
			
		//if user has no events at all (very rare), return false
		if(!$result && !$result2 && !$result3){
			return false;
		}else{
			$array1 = array();  //array for courses
			$array2 = array();  //array for multi day repeating events
			$array3 = array();  //array for single events
			
			//get all course events and add them to the course array
			while($row = mysql_fetch_row($result)){
				array_push($array1, $row);
			}
			//get all multi day repeating events and add them to the multi array
			while($row2 = mysql_fetch_row($result2)){
				array_push($array2, $row2);
			}
			//get all single events and add them to the single array
			while($row3 = mysql_fetch_row($result3)){
				array_push($array3, $row3);
			}
			
			//an array to hold the arrays of events
			$arr = [
							"courses" => $array1,
							"multi" => $array2,
							"single" => $array3,
							];
			

			return $arr;   //return all events
		}
	}
	
	//get event
	public function getEvent($eventnum){
		//check the event number to see what type of event it is
		$typeC = mysql_query("SELECT * FROM course WHERE crs_e_num = '$eventnum'");
		$typeM = mysql_query("SELECT * FROM multi WHERE multi_e_num = '$eventnum'");
		$typeS = mysql_query("SELECT * FROM single WHERE single_e_num = '$eventnum'");
		
		//check number of rows for each result(two will = 0, one will = 1 (the one we want))
		$numC = mysql_num_rows($typeC);
		$numM = mysql_num_rows($typeM);
		$numS = mysql_num_rows($typeS);

		//if a course
		if($numC > 0){
			$table = "course";
			$fKey = "crs_e_num";
		}else if($numM > 0){   //if a multi day repeating event
			$table = "multi";
			$fKey = "multi_e_num";
		}else if($numS > 0){   //if a single event
			$table = "single";
			$fKey = "single_e_num";
		}else{                 //if for some reason the specified event number was not found
			return false;
		}
		
		//get all event info from both tables
		$result = mysql_query("SELECT event.*, $table.* FROM event, $table WHERE event_num = $fKey AND event_num = '$eventnum'") or
			die(mysql_error());
			
		//if no event (shouldnt occur but just in case)
		if(!$result){
			return false;
		}else{
			return @mysql_fetch_array($result);  //put event info into array and return
		}
	}
	
	//get reminders for a specific event
	public function getReminder($evnum){
		//get reminder info for given event
		$result = mysql_query("SELECT reminder.* FROM reminder, event WHERE event_num = remind_e_num AND remind_e_num = '$evnum'");
		
		//if no reminders
		if(!$result){
			return false;
		}else{
			$remArr = array();  //create array
			
			//get rows of result set
			while($row = mysql_fetch_row($result)){
				array_push($remArr, $row);  //append to array
			}
			
			return $remArr;   //return array of rows
		}
	}
	
	//get all reminders for a specified user
	public function getReminders($uname){
		//gets all reminders for all events for the specified username
		$result = mysql_query("SELECT reminder.* FROM reminder, event WHERE remind_e_num = event_num AND ev_uname = '$uname'");
		
		//if no reminders
		if(!$result){
			return false;
		}else{
			$remAr = array();  //create array
			
			//get all rows & append them to the array
			while($row = mysql_fetch_row($result)){
				array_push($remAr, $row);
			}
			
			return $remAr;  //return array of reminders
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
		//query to get current courses taken by the user specified by the username passed into the function
		$result = mysql_query("SELECT * FROM event, course WHERE (CURDATE() BETWEEN s_date AND crs_e_date) AND (event_num = crs_e_num) 
								AND (ev_uname = '$username') AND parent_crs IS NULL") or die(mysql_error());
		
		//if no current courses found
		if(!$result){
			return false;   //return false 
		}else{   //if some current courses found
			$crsAr = array();  //array for to hold courses in
			
			//loop throuhg result set, appending to array along the way
			while($row = mysql_fetch_row($result)){
				array_push($crsAr, $row);
			}
			
			return $crsAr;  //return array of said courses
		}
	}
	
	//comments coming soon!  wait in grueling antici..........................................pation
	public function noteLen($eve_no){
		$result = mysql_query("SELECT notes FROM event, course WHERE crs_e_num = event_num AND (event_num = '$eve_no' OR '$eve_no' = parent_crs)");
		
		if(!$result){
			return 0;
		}else{
			$noteAr = array();
			$length = 0;
			$sz = 0;
			
			while($row = mysql_fetch_row($result)){
				$sz = array_push($noteAr, $row);
			}
			
			for($in = 0; $in < $sz; $in++){
				$length = $length + strlen($noteAr[$in][0]);
			}
			
			return $length;
		}
	}
	
	//Holy Crap, this is a gnarly looking function if i ever saw one
	//this terrible mess checks priority for 2 events(called class1 & class2, but i'm too lazy to change it)
	//returns true if class one has priority and false if class2 has it
	//if two events are still tied after all tests(highly unlikely, border INCONCEIVABLE!) it doesnt really matter all that much since the events
	//are pretty equal; return true in this case for class1, just 'cause
	public function tiebreaker($class1, $class2){
		//if two events not the same type of event
		if(sizeof($class1) != sizeof($class2)){
			if(sizeof($class1) == 15){   //if class1 a course
				return true;
			}else if(sizeof($class1) == 11 && sizeof($class2) == 12){  //class1 a multi day repeating event and class2 a single
				return true;
			}else{   //other cases class2 has priority
				return false;
			}
		}
		
		//terrible awful quagmire to test priority for the same type of event
		if($class1[5] > $class2[5]){   //test priority attribute  that wasnt too bad
			return true;
		}else if($class1[5] < $class2[5]){   
			return false;
		}else{  //uh oh, priorities were equal
			if(sizeof($class1) == 15){     //gotta know if class1 & class2 are course events
				if($class1[12] > $class2[12]){   //test course numbers, highest one wins
					return true;
				}else if($class1[12] < $class2[12]){
					return false;
				}else{   //oh god, the course numbers were the same, no freakin way
					if($this->noteLen($class1[7]) > $this->noteLen($class2[7])){  //test amount of notes taken, more notes == more important
						return true;
					}else if($this->noteLen($class1[7]) < $this->noteLen($class2[7])){
						return false;
					}else{   //they're equal again???  screw it, just return something
						return true;
					}	
				}
			}else{   //not a course, no course number.  Just test notes.  see flippant comments above 
				if($this->noteLen($class1[7]) > $this->noteLen($class2[7])){
					return true;
				}else if($this->noteLen($class1[7]) < $this->noteLen($class2[7])){
						return false;
				}else{
					return true;
				}
			}
		}
	}
	
	//takes an array of courses and orders them based on priority
	public function orderCourses($classes){
		//$classes = $this->getCurrentCourses($u, $c);           for testing
		
		$ordered = array();   //ordered array
		
		$ind = 0;  //index of course getting tested for highest priority
		//$ind2 = $ind + 1;
		
		
		//nested mismatched loops to test priority & order courses; goes until original array is empty
		while(sizeof($classes) > 0){
			$boo = true;   //boolean for something.  i'm getting tired.  update tomorrow
			for($ind2 = $ind + 1; $ind2 < sizeof($classes) && $boo == true; $ind2++){  //check against other courses for higher priority
				$boo = $this->tiebreaker($classes[$ind], $classes[$ind2]);  //call tiebreaker function
				//$ind2++;
			}
			
			//if highest remaining prioirty is found
			if($boo || $ind == sizeof($classes) - 1){    
				array_push($ordered, $classes[$ind]);  //push highest remaining prioirity into new ordered array
				unset($classes[$ind]);   //remove highest priority for original array
				$classes = array_values($classes);   //correct array indexes
				$ind = 0;   //set ind back to zero
			}else{   //this course did not have highest priority, check another one
				$ind++;
			}
		}
		
		//print_r($ordered);
		return $ordered;   //return ordered course array
	}
	
	//schedule study sessions (making progress; still have to loop thru courses and improve algoritm)
	public function scheduleStudy($username, $currentDate){
		$crsInfo = $this->getCurrentCourses($username, $currentDate);  //get user's current courses
		
		//if no current courses, can't schedule study time
		if(!$crsInfo)
			return false;
		
		$crsInfo = $this->orderCourses($crsInfo);  //order current courses based on priority considerations
	
		//format times in minutes & hrs         first index will eventually be a loop variable after course loop is added
		$eH = idate('H', strtotime($crsInfo[0][2]));  
		$sH = idate('H', strtotime($crsInfo[0][3]));
		$sM = idate('i', strtotime($crsInfo[0][3]));
		$eM = idate('i', strtotime($crsInfo[0][2]));
		
		//calculate study time (2 hr rule)
		$studyTime = ((($eH - $sH)* 60) + ($eM - $sM)) * 2;

		//add study time based on course number
		if($crsInfo[0][12] >= 300 && $crsInfo[0][12] < 400){
			$studyTime = $studyTime + 30;
		}else if($crsInfo[0][12] >= 400){
			$studyTime = $studyTime + 60;
		}else{}
		
		//calculate number of study sessions for class
		$sessions = $studyTime/30;
		$sessions = round($sessions);
		//echo $studyTime;           some prints to help out a poor old programmer like me
		//echo $sessions;
		
		//new variable for days
		$days = (string)$crsInfo[0][9];
		$numDys = strlen($days);
		//echo $numDys;
		//course number
		$number = (string)$crsInfo[0][12];
		//current date to php date type
		$curdate = new DateTime($currentDate);
		//$curdateCopy = new DateTime($currentDate);
		
		//for($i = 0; $i < $numDys; $i++){
			
			//thought i'd add a index of indexes (he he) to remind me instead of going back to phpmyadmin every 2 minutes
			/*    indices for the current course information:     
			[0]  notes
			[1]  start date 
			[2]  end time 
			[3]  start time 
			[4]  name_dept
			[5]  priority
			[6]  location
			[7]  event number
			[8]  username
			[9]  days of week
			[10] credits
			[11] end date
			[12] course number
			[13] foreign key to course number
			[14] linked course (null if not a study session)
			*/
			
			//some stuff that may or may not make it past the weekend. not sure yet, that's why it's still here
			/*//add days to current date depending on day of week in loop (it is assumed the function will start scheduling on Mondays)
			if($days[$i] == 'M'){    //if monday
				$curdate->add(new DateInterval('P0D'));
			}else if($days[$i] == 'T'){  //if tuesday
				$curdate->add(new DateInterval('P1D'));
			}else if($days[$i] == 'W'){   //if wednesday
				$curdate->add(new DateInterval('P2D'));
			}else if($days[$i] == 'R'){   //if thursday
				$curdate->add(new DateInterval('P3D'));
			}else if($days[$i] == 'F'){   //if friday
				$curdate->add(new DateInterval('P4D'));
			}else if($days[$i] == 'A'){   //if saturday
				$curdate->add(new DateInterval('P5D'));
			}else{                        //if sunday
				$curdate->add(new DateInterval('P6D'));
			}*/
			
			//find free times 
			$freeTimes = $this->findFree(15, 0, ($sessions * 30 * 3), $curdate->format('Y-m-d'));
			
			//set up a datetime for 2 days after monday(wednesday, to use the technical term) and look for free time there
			$curdatePlusOne = new DateTime($currentDate);
			$curdatePlusOne->add(new DateInterval('P2D'));
			$freeTimes2 = $this->findFree(15,0, ($sessions * 30 * 3), $curdatePlusOne->format('Y-m-d'));
			//do this crap ^^^ again except for friday.  at this point the method will schedule study times on the same day as classes
			$curdatePlus2 = new DateTime($currentDate);
			$curdatePlus2->add(new DateInterval('P4D'));
			$freeTimes3 = $this->findFree(15,0, ($sessions * 30 * 3), $curdatePlus2->format('Y-m-d'));
			
			//more diagnostic prints
			//echo $sessions;
			//print_r($freeTimes);
			//print_r($freeTimes2);
			//print_r($freeTimes3);
			
			$size = 0;  //size of the array of start times to schedule study sessions
			$schedTimes = array();   //the array of start times to schedule study sessions
			
			//compare free times on all days to see if any are the same, and can thus be used on multiple days for studying
			if(sizeof($freeTimes) >= $sessions * 2){   //nasty nested stuff
				for($z = 0; $z < sizeof($freeTimes) && $size < $sessions; $z++){  //if scheduled times are not filled up
					if(in_array($freeTimes[$z * 2], $freeTimes2) && in_array($freeTimes[$z*2], $freeTimes3))  //check for common times, leave time for snack breaks
						$size = array_push($schedTimes, $freeTimes[$z*2]); //if time in common, don't just stand there, put in the array!
					
				}
			}
			
			//HOLY CRAP, LOOK OUT BEHIND YOU!
			
			//get the day of the week for the dates tested
			$dow1 = $this->getDayOfWk($curdatePlusOne->format('Y-m-d'));
			$dow2 = $this->getDayOfWk($curdatePlus2->format('Y-m-d'));
			
			//concatenate string for days attribute of study sessions
			$daystring = "M" . $dow1 . $dow2;
			//echo $daystring;
			
			//print_r($schedTimes);
			
			//schedule study times (as single events for now, might switch to multi events if single events yield too many events) <--- yep
			for($x = 0; $x < $size; $x++){
				$sT = gmdate('H:i:s', $schedTimes[$x]);         //format start time 
				$eT = gmdate('H:i:s', $schedTimes[$x] + 1800);  //format end time
				$endDate = new DateTime($currentDate);          //end date(end of week)
				$endDate->add(new DateInterval('P6D'));  //end date = end of week
				//changed to course events.  every study session will have a "parent" course to which it is linked
				$this->createCourse('', $curdate->format('Y-m-d'), $endDate->format('Y-m-d'),0,0,$eT, $sT, "study " . $number, $crsInfo[0][5], $daystring, $crsInfo[0][6], $username, $crsInfo[0][13]);
			    //                notes  start date             end date            disregard  start & end time   title       priority      days of week  location         user       parent course
			}
			//more unnecessary stuff
			//$curdate = new DateTime($currentDate);
			//echo $curdate->format('Y-m-d');
		//}
		
		return true;
		
		/*I commend you if you're still paying attention.  That's a legitimate crapton of code and dumb comments to read through.  To reward you
		  for your patience and bravery, here's a joke: 
		  
		  A young Programmer and his Project Manager board a train headed through the mountains on its way to Wichita. They can find no place 
		  to sit except for two seats right across the aisle from a young woman and her grandmother. After a while, it is obvious that the young 
		  woman and the young programmer are interested in each other, because they are giving each other looks. Soon the train passes into a tunnel 
		  and it is pitch black. There is a sound of a kiss followed by the sound of a slap.
		  
		  When the train emerges from the tunnel, the four sit there without saying a word. The grandmother is thinking to herself, 
		  “It was very brash for that young man to kiss my granddaughter, but I’m glad she slapped him.”
	     
   		  The Project manager is sitting there thinking, “I didn’t know the young tech was brave enough to kiss the girl, but I sure wish she 
		  hadn’t missed him when she slapped me!”
	      
		  The young woman was sitting and thinking, “I’m glad the guy kissed me, but I wish my grandmother had not slapped him!”
          
		  The young programmer sat there with a satisfied smile on his face. He thought to himself, “Life is good. How often does a guy have 
		  the chance to kiss a beautiful girl and slap his Project manager all at the same time!”

		 */
	}
}
?>