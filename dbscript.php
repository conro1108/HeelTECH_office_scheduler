<?php

$servername = "*";
$username = "*";
$password = "*";
$dbname = "*";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
	die("connection failed: " . $conn->connect_error);
}
$handle = fopen("fakeusers.data", "r");
$conn->query("truncate Offices");
if($handle){
	while(!feof($handle)){
		$line = fgets($handle);
		$line = str_replace(array("\r", "\n"), '', $line); //remove newlines
		$larr = explode(" ", $line);

		$desc = $larr[0];
		$p = $larr[1];
		$r = $larr[2];
		$rp = $larr[3];

		$sql = "insert into Offices(officeID, description, perm, recurring, perm_recur) ".
			"values(0, '$desc', '$p', $r, '$rp')";

		//echo($sql."\n");
		$conn->query($sql);
	}
}	
?>