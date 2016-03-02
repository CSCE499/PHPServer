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
		
		$result = mysql_query("INSERT INTO user(username,encrypted_passwd,created) VALUES('$username','$encrypted_password', NOW())");
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
			$encrypted_password = $result['encrypted_passwd'];
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
	
}
?>