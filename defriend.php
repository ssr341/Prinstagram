<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Remove Friend</title>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}

else if(isset($_POST["users"])){
	$users = $_POST["users"];
	$group = $_POST["gname"];
	
	//loops through all friendgroups that were selected
	echo "You have successfully removed";
	for($i = 0; $i < count($users) - 1; ++$i)
		echo " $groups[$i],";
	if(count($users) > 1)
		echo " and";
	$last = $users[count($users) - 1];
	echo " $last from $group.<br>";
	
	for($i = 0; $i < count($users); ++$i){
		//delete tags to photos no longer visible to the users
		if($stmt = $mysqli->prepare("select distinct pid 
									 from tag natural join shared natural join ingroup
									 where username = taggee  and gname = ?")){
			$stmt->bind_param("s", $group);
			$stmt->execute();
			$pids = $stmt->get_result();
			$stmt->close();
			while($pid = $pids->fetch_array(MYSQLI_NUM)){
				if($stmt = $mysqli->prepare("delete from tag where pid = ? and taggee = ?")){
					$stmt->bind_param("is", $pid[0], $users[$i]);
					$stmt->execute();
					$stmt->close();
				}
			}
		}
		//delete friends from ingroup
		if($stmt = $mysqli->prepare("delete from ingroup where gname = ? and ownername = ? and username = ?")){
			$stmt->bind_param("sss", $group, $_SESSION["username"], $users[$i]);
			$stmt->execute();
			$stmt->close();
		}
	}
	echo "You will return to your page in 3 seconds, or click <a href=\"index.php\">here</a>.";
	header("refresh: 3; index.php");
}

else {
	echo 'Here you may remove a friend from a FriendGroups you own.<br />';
	echo 'Select the friend you wish to remove from a FriendGroup.<br />';
	echo "<br>";

	if($stmt = $mysqli->prepare("select gname, descr from friendgroup where ownername = ?")){
		$stmt->bind_param("s", $_SESSION["username"]);
		$stmt->execute();
		$groups = $stmt->get_result();
		$stmt->close();
		while($group = $groups->fetch_array(MYSQLI_NUM)){
			echo "Group name: $group[0] <br>";
			echo "Description: $group[1] <br>";
			
			echo '<form action="defriend.php" method="POST">';
			echo '<select multiple="multiple" name="users[]">';
			if($stmt = $mysqli->prepare("select username, fname, lname 
										 from person natural join ingroup
										 where ownername = ? and gname = ?")){
				$stmt->bind_param("ss", $_SESSION["username"], $group[0]);
				$stmt->execute();
				$stmt->bind_result($username, $fname, $lname);
				while($stmt->fetch())
					echo "<option value=$username>$fname $lname ($username)</option>";
				$stmt->close();
			}
			echo "<input type= \"hidden\" name=\"gname\" value=\"$group[0]\"><class=\"submit\">";
			echo '</select><br><input type="submit" value="Defriend">';
			echo '</form><br><br>';
		}
	}
	echo '<a href="index.php">Go back</a><br /><br />';
	echo "\n";
}

?>

</html>