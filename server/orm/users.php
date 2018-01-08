<?php
date_default_timezone_set('America/New_York');

class Users
{
	private $userID;
	private $username;
	private $passhash;
	private $perm;

	public static function connect() {
		return new mysqli("classroom.cs.unc.edu", 
		                  "cdrowe", 
		                  "mexic@n1comp", 
		                  "cdrowedb");
	}

	public static function create($username, $passhash, $perm){
		$conn = Users::connect();

		/*
		$result = $conn->query("insert into Users values (0, '" .
						$conn->real_escape_string($username) . "', '" .
						$conn->real_escape_string($passhash) . "', '" .
						$conn->real_escape_string($perm) . "')");
        */

		$result = $conn->query("insert into Users(userID, username, passhash, perm) values(0, '$username', '$passhash', '$perm')");
		if($result){
			$userID = $conn->insert_id;
			return new Users($userID, $username, $passhash, $perm);
		}
		return null;
	}

	public static function findByID($userID){
		$conn = Users::connect();

		$result = $conn->query("select * from Users where userid=" . $userID);
		if($result){
			if($result->num_rows == 0){
				return null;
			}

			$userinfo = $result->fetch_array();

			return new Users(intval($userinfo['userID']),
						$userinfo['username'],
						$userinfo['passhash'],
						$userinfo['perm']);
		}
		return null;
	}

	public static function findByLoginPass($username, $passhash){
	    $conn = Users::connect();

	    $result = $conn->query("Select * from Users where username='$username' and passhash='$passhash'");
        if($result){
            if($result->num_rows == 0){
                return null;
            }

            $userinfo = $result->fetch_array();

            return new Users(intval($userinfo['userID']),
                $userinfo['username'],
                $userinfo['passhash'],
                $userinfo['perm']);
        }
        return null;
    }

	public static function getAllIDs() {
		$conn = Users::connect();

		$result = $conn->query("select userID from Users");
		$id_array = array();

    	if ($result) {
      		while ($next_row = $result->fetch_array()) {
	     		$id_array[] = intval($next_row['userID']);
      		}
    	}
    	return $id_array;
	}

	private function __construct($userID, $username, $passhash, $perm){
		$this->userID = $userID;
		$this->username = $username;
		$this->passhash = $passhash;
		$this->perm = $perm;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function getUsername(){
		return $this->username;
	}

	public function getPasshash(){
		return $this->passhash;
	}

	public function getPerm(){
		return $this->perm;
	}

	public function setUsername($username){
		$this->username = $username;
		return $this->update();
	}

	public function setPasshash($passhash){
		$this->passhash = $passhash;
		return $this->update();
	}

	public function setPerm($perm){
		$this->perm = $perm;
		return $this->update();
	}

	private function update(){
		$conn = Users::connect();

		$result = $conn->query("update Users set " .
							"username='" . $this->username .
							"', passhash='" . $this->passhash .
							"', perm='" . $this->perm .
							"' where userID=" . $this->userID);
		return $result;
	}

	public function delete(){
		$conn = Users::connect();
		$conn->query("delete from Users where userID=" . $this->userID);
	}

	public function getJSON(){
		$json_obj = array('userID' => $this->userID,
		      'username' => $this->username,
		      'passhash' => $this->passhash,
		      'perm' => $this->perm);
    return json_encode($json_obj);
	}
}