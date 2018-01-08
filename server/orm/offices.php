<?php
date_default_timezone_set('America/New_York');

class Offices
{
  private $officeID;
  private $description;
  private $perm;
  private $perm_recur;


  public static function connect() {
    return new mysqli("classroom.cs.unc.edu", 
                      "cdrowe",
                      "mexic@n1comp", 
                      "cdrowedb");
  }

  public static function create($description, $perm, $perm_recur) {
    $conn = Offices::connect();

    $result = $conn->query("insert into Offices values (0, " .
			     "'" . $conn->real_escape_string($description) . "', " .
			     "'" . $conn->real_escape_string($perm) . "', " .
			     "'" . $conn->real_escape_string($perm_recur) . "') ");
    
    if ($result) {
      $id = $conn->insert_id;
      return new Offices($id, $description, $perm, $perm_recur);
    }
    return null;
  }

  public static function findByID($officeID) {
    $conn = Offices::connect();

    $result = $conn->query("select * from Offices where officeID = " . $officeID);
    if ($result) {
      if ($result->num_rows == 0) {
	       return null;
      }

      $ofc_info = $result->fetch_array();

      return new Offices(intval($ofc_info['officeID']),
		      $ofc_info['description'],
		      $ofc_info['perm'],
		      $ofc_info['perm_recur']);
    }
    return null;
  }

  public static function getIDsByDesc($desc){
      $conn = Offices::connect();

      $result = $conn->query("select officeID from Offices where description='$desc'");
      $id_array = array();

      if($result){
          while($next_row = $result->fetch_array()){
              $id_array[] = intval($next_row['officeID']);
          }
      }
      return $id_array;
  }

  public static function getAllIDs() {
    $conn = Offices::connect();

    $result = $conn->query("select officeID from Offices");
    $id_array = array();

    if ($result) {
      while ($next_row = $result->fetch_array()) {
	     $id_array[] = intval($next_row['officeID']);
      }
    }
    return $id_array;
  }

  private function __construct($officeID, $description, $perm, $perm_recur) {
    $this->officeID = $officeID;
    $this->description = $description;
    $this->perm = $perm;
    $this->perm_recur = $perm_recur;
  }

  public function getOfficeID(){
    return $this->officeID;
  }

  public function getDescription(){
    return $this->description;
  }

  public function getPerm(){
    return $this->perm;
  }

  public function getPermRecur(){
    return $this->perm_recur;
  }

  public function setDescription($description){
    $this->description = $description;
    return $this->update();
  }

  public function setPerm($perm){
    $this->perm = $perm;
    return $this->update();
  }

  public function setPermRecur($perm_recur){
    $this->perm_recur = $perm_recur;
    return $this->update();
  }

  private function update() {
    $conn = Offices::connect();

    $result = $conn->query("update Offices set " .
                        "description='" . $this->description . 
                        "', perm='" . $this->perm .
                        "', perm_recur=" . $this->perm_recur .
                        " where officeID=" . $this->officeID);
    return $result;
  }

    public function delete() {
    $conn = Offices::connect();
    $conn->query("delete from Offices where officeID = " . $this->officeID);
  }

  public function getJSON() {
    $json_obj = array('officeID' => $this->officeID,
		      'description' => $this->description,
		      'perm' => $this->perm,
		      'perm_recur' => $this->perm_recur);
    return json_encode($json_obj);
  }
}