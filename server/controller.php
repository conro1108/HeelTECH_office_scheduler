<?php
/**
 * Created by PhpStorm.
 * User: connorrowe
 * Date: 12/7/17
 * Time: 7:36 PM
 */

require_once('orm/offices.php');
require_once ('orm/reservations.php');
require_once ('orm/users.php');

$resource_components = explode('/', $_SERVER['PATH_INFO']);

$resource_type = $resource_components[1];

if($resource_type == 'users'){
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(count($resource_components) == 2){//matches controller.php/users
            //return all userIDs
            header("Content-type: application/json");
            print(json_encode(Users::getAllIDs()));
            exit();
        }
        if(count($resource_components) == 3){
            $index = $resource_components[2];
            //controller.php/users/<id>
            if(is_numeric($index)){
                $userID = intval($index);
                $user = Users::findByID($userID);

                //user not found
                if($user == null){
                    header("HTTP/1.0 404 Not Found");
                    print("user id: " . $userID . " not found.");
                    exit();
                }

                //normal userID lookup
                header("Content-type: application/json");
                print($user->getJSON());
                exit();
            }
            //controller.php/users/<username>
            else{
                $passhash = md5($_REQUEST['password']);
                $user = Users::findByLoginPass($index, $passhash);

                //user not found
                if($user == null){
                    header("HTTP/1.0 404 Not Found");
                    print("username: " . $index . " not found.");
                    exit();
                }

                //normal username lookup
                header("Content-type: application/json");
                print($user->getJSON());
                exit();
            }

        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //either create or update user
        if((count($resource_components) > 2) && $resource_components[2] != ''){
            //update: controller.php/<id>
            $user_id = intval($resource_components[2]);
            $user = Users::findByID($user_id);

            if($user == null){
                header("HTTP/1.0 404 Not Found");
                print("user id: $user_id not found when attempting update");
                exit();
            }

            if(isset($_REQUEST['delete'])){
                if($_REQUEST['delete'] == 'true'){
                    $user->delete();
                }
            }

            $new_username = false;
            if(isset($_REQUEST['username'])){
                $new_username = trim($_REQUEST['username']);
            }

            $new_passhash = false;
            if(isset($_REQUEST['passhash'])){
                $new_passhash = trim($_REQUEST['passhash']);
            }

            $new_perm = false;
            if(isset($_REQUEST['perm'])){
                $new_perm = trim($_REQUEST['perm']);
            }

            if($new_username){
                $user->setUsername($new_username);
            }
            if($new_passhash){
                $user->setPasshash($new_passhash);
            }
            if($new_perm){
                $user->setPerm($new_perm);
            }

            //return json for updated $user
            header("Content-type: application:json");
            print($user->getJSON());
            exit();
        } else {
            //create new user

            //validate that all values exist/are valid
            if (!isset($_REQUEST['username'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing username");
                exit();
            }

            $login = trim($_REQUEST['username']);
            if ($login == "") {
                header("HTTP/1.0 400 Bad Request");
                print("Bad username");
                exit();
            }

            if (!isset($_REQUEST['password'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing password");
                exit();
            }

            $hash = md5(trim($_REQUEST['password']));

            if (!isset($_REQUEST['perm'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing permission");
                exit();
            }

            $perms = array('read', 'employee', 'executive', 'admin');
            $perm = trim($_REQUEST['perm']);
            if (!in_array($perm, $perms)) {
                header("HTTP/1.0 400 Bad Request");
                print("Bad permission");
                exit();
            }

            // Create new User via ORM
            $new_user = Users::create($login, $hash, $perm);

            // Report if failed
            if ($new_user == null) {
                header("HTTP/1.0 500 Server Error");
                print("Server couldn't create new User.");
                exit();
            }

            //Generate JSON encoding of new User
            header("Content-type: application/json");
            print($new_user->getJSON());
            exit();
        }

    }

}

if($resource_type == 'offices'){
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(count($resource_components) == 2){
            //controller.php/offices -> return all officeIDs
            header("Content-type: application/json");
            print(json_encode(Offices::getAllIDs()));
        }

        if(count($resource_components) > 2){
            if(is_numeric($resource_components[2])){
                //controller.php/offices/<id> -> return office by ID
                header("Content-type: application/json");
                $officeID = intval($resource_components[2]);
                $office = Offices::findByID($officeID);

                if($office == null){
                    //office not found
                    header("HTTP1.0 404 Not found");
                    print("Office $officeID not found");
                    exit();
                } else {
                    //normal officeID lookup
                    header("Content-type: application/json");
                    print($office->getJSON());
                    exit();
                }
            }
            else{
                //controller.php/offices/<description>
                $description = $resource_components[2];
                header("Content-type: application/json");
                print(json_encode(Offices::getIDsByDesc($description)));
                exit();
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        if((count($resource_components) > 2) && $resource_components[2] != ''){
            //update: controller.php/offices/<id>
            $office_id = intval($resource_components[2]);
            $office = Offices::findByID($office_id);


            if($office == null){
                header("HTTP/1.0 404 Not Found");
                print("office id: $office_id not found when attempting update");
                exit();
            }

            if(isset($_REQUEST['delete'])){
                if($_REQUEST['delete'] == 'true'){
                    $office->delete();
                }
            }

            $new_desc = false;
            if(isset($_REQUEST['description'])){
                $new_desc = trim($_REQUEST['description']);
            }

            $new_perm = false;
            if(isset($_REQUEST['perm'])){
                $new_perm = trim($_REQUEST['perm']);
            }

            $new_permrecur = false;
            if(isset($_REQUEST['perm_recur'])){
                $new_permrecur = trim($_REQUEST['perm_recur']);
            }

            if($new_desc){
                $office->setDescription($new_desc);
            }
            if($new_perm){
                $office->setPerm($new_perm);
            }

            if($new_permrecur){
                $office->setPermRecur($new_permrecur);
            }

            //return json for updated $user
            header("Content-type: application:json");
            print($office->getJSON());
            exit();
        } else {
            //create new office

            //validate that all values exist/are valid
            if (!isset($_REQUEST['description'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing description");
                exit();
            }

            $desc = trim($_REQUEST['description']);
            if ($desc == "") {
                header("HTTP/1.0 400 Bad Request");
                print("Bad description");
                exit();
            }

            $perms = array('read', 'employee', 'executive', 'admin');
            $perm = trim($_REQUEST['perm']);
            if (!in_array($perm, $perms)) {
                header("HTTP/1.0 400 Bad Request");
                print("Bad permission");
                exit();
            }

            if (!isset($_REQUEST['perm'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing permission");
                exit();
            }

            $perm_recur = trim($_REQUEST['perm_recur']);
            if (!in_array($perm_recur, $perms)) {
                header("HTTP/1.0 400 Bad Request");
                print("Bad permission");
                exit();
            }

            // Create new Office via ORM
            $new_office = Offices::create($desc, $perm, $perm_recur);

            // Report if failed
            if ($new_office == null) {
                header("HTTP/1.0 500 Server Error");
                print("Server couldn't create new Office.");
                exit();
            }

            //Generate JSON encoding of new office
            header("Content-type: application/json");
            print($new_office->getJSON());
            exit();
        }
    }
}
if($resource_type == 'reservations'){
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(count($resource_components) == 2){
            // controller.php/reservations -> return all resIDs
            header("Content-type: application/json");
            print(json_encode(Reservations::gettAllIDs()));
        }
        if(count($resource_components) > 2){
            if(trim($resource_components[2] == 'offices')){
                // controller.php/reservations/offices/<id> -> get array of resID by officeID
                $officeID = intval($resource_components[3]);
                header("Content-type: application/json");
                print(json_encode(Reservations::getResIDsbyOfficeID($officeID)));
                exit();
            }
            // controller.php/reservations/<id> -> return single reservation
            $resID = intval($resource_components[2]);
            $reservation = Reservations::findByID($resID);

            if($reservation == null){
                header("HTTP/1.0 404 Not Found");
                print("Reservation with id $resID not found");
                exit();
            } else{
                //normal lookup by resID
                header("Content-type: application/json");
                print($reservation->getJSON());
                exit();
            }
        }
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if((count($resource_components) > 2) && $resource_components[2] != ''){
            //controller.php/reservations/<id> -> update

            $resID = intval($resource_components[2]);
            $reservation = Reservations::findByID($resID);

            if($reservation == null){
                header("HTTP/1.0 404 Not Found");
                print("Reservation with id $resID not found");
                exit();
            }

            if(isset($_REQUEST['delete'])){
                if($_REQUEST['delete'] == 'true'){$reservation->delete();}
            }

            $new_userID = false;
            if(isset($_REQUEST['userID'])){
                $new_userID = intval($_REQUEST['userID']);
            }

            $new_officeID = false;
            if(isset($_REQUEST['officeID'])){
                $new_officeID = intval($_REQUEST['officeID']);
            }

            $new_starttime = false;
            if(isset($_REQUEST['starttime'])){
                $new_starttime = DateTime::createFromFormat("Y-m-d H:i:s",
                                                            $_REQUEST['starttime']);
            }

            $new_duration = false;
            if(isset($_REQUEST['duration'])){
                $new_duration = intval($_REQUEST['duration']);
            }

            $new_description = false;
            if(isset($_REQUEST['description'])){
                $new_description = trim($_REQUEST['description']);
            }

            if($new_userID){$reservation->setUserID($new_userID);}
            if($new_officeID){$reservation->setOfficeID($new_officeID);}
            if($new_starttime){$reservation->setStarttime($new_starttime);}
            if($new_duration){$reservation->setDuration($new_duration);}
            if($new_description){$reservation->setDescription($new_description);}

            header("Content-type: application/json");
            print($reservation->getJSON());
            exit();
        }
        else {
            // create new reservation

            //validate input parameters
            if (!isset($_REQUEST['userID'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing userID");
                exit();
            }
            $userID = intval($_REQUEST['userID']);

            if (!isset($_REQUEST['officeID'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing officeID");
                exit();
            }
            $officeID = intval($_REQUEST['officeID']);

            if(!isset($_REQUEST['starttime'])){
                header("HTTP/1.0 400 Bad Request");
                print("Missing starttime");
                exit();
            }
            $starttime = trim($_REQUEST['starttime']);
            if($starttime == ""){
                header("HTTP/1.0 400 Bad Request");
                print("Bad starttime");
                exit();
            }
            $starttime = DateTime::createFromFormat("Y-m-d H:i:s", $starttime);

            if (!isset($_REQUEST['duration'])) {
                header("HTTP/1.0 400 Bad Request");
                print("Missing duration");
                exit();
            }
            $duration = intval($_REQUEST['duration']);

            if(!isset($_REQUEST['description'])){
                header("HTTP/1.0 400 Bad Request");
                print("Missing description");
                exit();
            }
            $description = trim($_REQUEST['description']);
            if($description == ""){
                header("HTTP/1.0 400 Bad Request");
                print("Bad description");
                exit();
            }

            //create new res through ORM
            $new_res = Reservations::create($userID, $officeID, $starttime, $duration, $description);

            // Report if failed
            if ($new_res == null) {
                header("HTTP/1.0 500 Server Error");
                print("Server couldn't create new Reservation.");
                exit();
            }

            //Generate JSON encoding of new office
            header("Content-type: application/json");
            print($new_res->getJSON());
            exit();
        }
    }
}