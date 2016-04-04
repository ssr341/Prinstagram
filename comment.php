<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Comment</title>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}

else {
	$user = $_SESSION["username"];
	$input_pid = $_GET["pid"];
	$input_poster = $_GET["poster"];
	$input_pdate = $_GET["pdate"];
	$input_caption = $_GET["caption"];
	$input_comment = $_GET["comment"];
	
	//Show Photo
	echo "<table border = '1'>\n";
	echo "<tr>";
	echo "<td><b>ID</td><td><b>Poster</td><td><b>Date/Time</td><td><b>Caption</td>";
	echo "</tr><tr>";
	echo "<td>$input_pid</td><td>$input_poster</td><td>$input_pdate</td><td>$input_caption</td>";
	echo "</tr>\n";
	echo "</table>\n";
	
	if($stmt1 = $mysqli->prepare("insert into comment values (NULL, now(), ?)")){
		//Add to comment and save cid
		$stmt1->bind_param("s", $input_comment);
		$stmt1->execute();
		$cid = $mysqli->insert_id;
		$stmt1->close();
	}
	if($stmt2 = $mysqli->prepare("insert into commenton values (?, ?, ?)")){
		//Add to commenton
		$stmt2->bind_param("iis", $cid, $input_pid, $user);
		$stmt2->execute();
		$stmt2->close();
	}
	echo "You have successfully commented on photo $input_pid.<br>You will be returned to your page in 3 seconds, or click <a href=\"view.php\">here</a>.\n";
	header("refresh: 3; view.php");	
}

?>

</html>