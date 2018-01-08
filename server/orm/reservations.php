<?php
date_default_timezone_set('America/New_York');

class Reservations
{
	private $resID;
	private $userID;
	private $officeID;
	private $starttime;
	private $duration;
	private $description;

	public static function connect(){
		return new mysqli("classroom.cs.unc.edu", 
		                  "cdrowe", 
		                  "mexic@n1comp", 
		                  "cdrowedb");
	}

	public static function create($userID, $officeID, $starttime, $duration, $description){
		$conn = Reservations::connect();
		if($starttime == null){
		    $startstring = null;
        } else{
		    $startstring = "'" . $starttime->format('Y-m-d H:i:s') . "'";
        }

		$result = $conn->query("insert into Reservations values (0, ".
					$userID . ", " . $officeID . ", ".
					$startstring . ", " . $duration . ", '".
					$conn->real_escape_string($description) . "')");

		if($result){
			$resID = $conn->insert_id;
			return new Reservations($resID, $userID, $officeID, $starttime, $duration, $description);
		}
		return null;
	}

	public static function getResIDsbyOfficeID($officeID){
	    $conn = Reservations::connect();

	    $result = $conn->query("select resID, starttime from Reservations where officeID=$officeID ORDER BY starttime");
	    $id_array = array();

        if ($result) {
            while ($next_row = $result->fetch_array()) {
                $id_array[] = intval($next_row['resID']);
            }
        }
        return $id_array;
    }

	public static function findByID($resID){
		$conn = Reservations::connect();

		$result = $conn->query("select * from Reservations where resID=" . $resID);
		if($result){
			if($result->num_rows == 0){
				return null;
			}

			$res_info = $result->fetch_array();

			return new Reservations(intval($res_info['resID']),
								intval($res_info['userID']),
								intval($res_info['officeID']),
								new DateTime($res_info['starttime']),
								intval($res_info['duration']),
								$res_info['description']);
		}
	}

	public static function gettAllIDs() {
		$conn = Reservations::connect();

		$result = $conn->query("select resID from Reservations");
		$id_array = array();

    if ($result) {
      while ($next_row = $result->fetch_array()) {
	     $id_array[] = intval($next_row['resID']);
      }
    }
    return $id_array;
	}

	private function __construct($resID, $userID, $officeID, $starttime, $duration, $description){
		$this->resID = $resID;
		$this->userID = $userID;
		$this->officeID = $officeID;
		$this->starttime = $starttime;
		$this->duration = $duration;
		$this->description = $description;
	}

	public function getResID(){
		return $this->resID;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function getOfficeID(){
		return $this->officeID;
	}

	public function getStarttime(){
		return $this->starttime;
	}

	public function getDuration(){
		return $this->duration;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setResID($resID){
		$this->resID = $resID;
		return $this->update();
	}

	public function setUserID($userID){
		$this->userID = $userID;
		return $this->update();
	}

	public function setOfficeID($officeID){
		$this->officeID = $officeID;
		return $this->update();
	}

	public function setStarttime($starttime){
		$this->starttime = $starttime;
		return $this->update();
	}

	public function setDuration($duration){
		$this->duration = $duration;
		return $this->update();
	}

	public function setDescription($description){
		$this->description = $description;
		return $this->update();
	}

	private function update(){
		$conn = Reservations::connect();

		$startstring = "'" . ($this->starttime)->format('Y-m-d H:i:s') . "'";

		$result = $conn->query("update Reservations set ".
							"userID=" . $this->userID.
							", officeID=" . $this->officeID.
							", starttime=" . $startstring.
							", duration=" . $this->duration,
							", description='" . $this->description .
							"' where resID=" . $this->resID);

		return $result;
	}

	public function delete() {
		$conn = Reservations::connect();
		$conn->query("delete from Reservations where resID=" . $this->resID);
	}

	public function getJSON(){
        $startstring = "'" . ($this->starttime)->format('Y-m-d H:i:s') . "'";

		$json_obj = array('resID' => $this->resID,
						'userID' => $this->userID,
						'officeID' => $this->officeID,
						'starttime' => $startstring,
						'duration' => $this->duration,
						'description' => $this->description);
		return json_encode($json_obj);
	}
}
