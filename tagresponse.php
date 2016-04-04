<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Tag</title>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to your pending tags in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}

else {
	$user = $_SESSION["username"];
	$input_pid = $_GET["pid"];
	$input_poster = $_GET["poster"];
	$input_pdate = $_GET["pdate"];
	$input_caption = $_GET["caption"];
	$tagger = $_GET["tagger"];
	$response = $_GET["response"];
	
	//Show Photo
	echo "<table border = '1'>\n";
	echo "<tr>";
	echo "<td><b>Photo</td><td><b>Poster</td><td><b>Date/Time</td><td><b>Caption</td>";
	echo "</tr><tr>";
	echo "<td>$input_pid</td><td>$input_poster</td><td>$input_pdate</td><td>$input_caption</td>";
	echo "</tr>\n";
	echo "</table>\n";
	
	if($response){
		if($stmt = $mysqli->prepare("update tag set tstatus = true 
									 where taggee = ? and tagger = ? and pid = ?")){
			$stmt->bind_param("ssi", $user, $tagger, $input_pid);
			$stmt->execute();
			$stmt->close();
			echo "You have successfully accepted $tagger's tag on photo $input_pid.<br>You will be returned to your page in 3 seconds, or click <a href=\"tagview.php\">here</a>.\n";	
		}
	}
	else{
		if($stmt = $mysqli->prepare("delete from tag 
									where taggee = ? and tagger = ? and pid = ?")){
			$stmt->bind_param("ssi", $user, $tagger, $input_pid);
			$stmt->execute();
			$stmt->close();
			echo "You have successfully rejected $tagger's tag on photo $input_pid.<br>You will be returned to your page in 3 seconds, or click <a href=\"tagview.php\">here</a>.\n";
		}
	}
	header("refresh: 5; tagview.php");
}

?>

</html>