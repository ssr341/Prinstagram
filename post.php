<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Post</title>

<?php

include "connectdb.php";

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}
else {
	//if the user have entered a photo, insert it into database
	if(isset($_POST["caption"])) {

		$is_pub = $_POST["is_pub"];
		//insert into database, note that message_id is auto_increment and time is set to current_timestamp by default
		if ($stmt = $mysqli->prepare("insert into photo (pid, poster, caption, pdate, is_pub) values (NULL,?,?,NOW(),?)")) {
			$stmt->bind_param("ssi", $_SESSION["username"], $_POST["caption"], $is_pub);
			if (!$stmt->execute()) echo "query failed";

			$stmt->close();
			$username = htmlspecialchars($_SESSION["username"]);
			
			if(!$is_pub){
				$pid = $mysqli->insert_id;
				$gname = $_POST["gname"];
				if($gname[0] == 1){
					if($stmt = $mysqli->prepare("insert into shared (gname, ownername, pid)
												(select distinct gname, ownername, pid
												 from ingroup natural join friendgroup join photo
												 where (username = ? or ownername = ?) and pid = ?)")){
						$stmt->bind_param("ssi", $_SESSION["username"], $_SESSION["username"], $pid);
						$stmt->execute();
						$stmt->close();
					}
				}
				else{
					for($i = 0; $i < count($gname); ++$i){
						if($stmt = $mysqli->prepare("insert into shared (gname, ownername, pid)
												    (select distinct gname, ownername, pid
												     from ingroup natural join friendgroup join photo
												     where (username = ? or ownername = ?) and pid = ? and gname = ?)")){
						$stmt->bind_param("ssis", $_SESSION["username"], $_SESSION["username"], $pid, $gname[$i]);
						$stmt->execute();
						$stmt->close();
						}
					}
				}
			}
			echo "Your photo has been posted. \n";
		}
		echo "You will be returned to your blog in 3 seconds or click <a href=\"view.php?username=$username\">here</a>.";
		header("refresh: 3; view.php?username=$username");
	}
  
  //if not then display the form for posting message
  else {
    echo "Enter your caption: <br /><br />\n";
    echo '<form action="post.php" method="POST">';
    echo "\n";	
    echo '<textarea cols="40" rows="10" name="caption" />enter your caption here</textarea><br />';
    echo "\n";
	
	echo "Privacy Settings:<br>";
	echo '<select name = "is_pub">';
	echo "<option value=1>Public</option>";
	echo "<option value=0>Private</option>";
    echo '</select>';
	
	echo "<br><br>Friend Group to add photo to:<br>";
	echo '<select multiple = "multiple" name = "gname[]">';
	echo "<option value=1>All</option>";
	if($stmt = $mysqli->prepare("(select distinct gname
								 from ingroup 
								 where username = ?) union
								 (select gname 
								 from friendgroup 
								 where ownername = ?)")){
		$stmt->bind_param("ss", $_SESSION["username"], $_SESSION["username"]);
		$stmt->execute();
		$stmt->bind_result($gname);
		while($stmt->fetch())
			echo "<option value=$gname>$gname</option>";				
		$stmt->close();
	}
	echo '</select><br><input type="submit" value="Submit" />';
    echo "\n";
	echo '</form>';
	echo "\n";
	echo '<br /><a href="index.php">Go back</a>';
  }
}
$mysqli->close();
?>

</html>