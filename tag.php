<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Tag</title>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}

else {
    //Print Photo and Taggee information
	$user = $_SESSION["username"];
    $input_user = $_GET["username"];
	$input_pid = $_GET["pid"];
	$input_poster = $_GET["poster"];
	$input_pdate = $_GET["pdate"];
	$input_caption = $_GET["caption"];
	$is_pub = $_GET["is_pub"];
	
	//Show Photo
	echo "<table border = '1'>\n";
	echo "<tr>";
	echo "<td><b>ID</td><td><b>Poster</td><td><b>Date/Time</td><td><b>Caption</td>";
	echo "</tr><tr>";
	echo "<td>$input_pid</td><td>$input_poster</td><td>$input_pdate</td><td>$input_caption</td>";
	echo "</tr>\n";
	echo "</table>\n";
	
	//check for self tagging
	if($user == $input_user){
		if($stmt = $mysqli->prepare("insert into tag (pid, tagger, taggee, tstatus, ttime)
								 values (?, ?, ?, true, now())")){
			$stmt->bind_param("iss", $input_pid, $user, $input_user);
			$stmt->execute();
			$stmt->close();
			echo "You have successfully tagged yourself to photo $input_pid.<br>You will be returned to your page in 3 seconds, or click <a href=\"view.php\">here</a>.\n";
			header("refresh: 3; view.php");	
		}
	}
	
	//check if input_user is allowed to view photo
	//check if taggee is in the friendgroup that the photo is shared in
	else{
		$count = 0;
		if($stmt = $mysqli->prepare("select count(distinct gname)
									 from ingroup natural join shared
									 where (ownername = ? or username = ?) and pid = ?")){
			$stmt->bind_param("ssi", $input_user, $input_user, $input_pid);
			$stmt->execute();
			$stmt->bind_result($count);
			$stmt->fetch();
			$stmt->close();
		}
		//if photo is private and user is not in friendgroup, send error message
		if($count == 0 && !$is_pub){
			echo "Unable to tag $input_user to photo $input_pid. $input_user is not a member of the FriendGroup this photo is in.<br>You will be returned to your page in 3 seconds, or click <a href=\"view.php\">here</a>.\n";
			header("refresh: 3; view.php");	
		}
		else{
			if($stmt = $mysqli->prepare("insert into tag (pid, tagger, taggee, tstatus, ttime)
									     values (?, ?, ?, false, now())")){
			$stmt->bind_param("iss", $input_pid, $user, $input_user);
			$stmt->execute();
			$stmt->close();
			echo "You have successfully tagged $input_user to photo $input_pid.<br>You will be returned to your page in 3 seconds, or click <a href=\"view.php\">here</a>.\n";
			header("refresh: 3; view.php");	
		}
		}
	}
}
?>

</html>