<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Add Friend</title>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}

else {
	//if the user have entered a photo, insert it into database
	if(isset($_POST["fname"]) && isset($_POST["gnames"])) {
		$fname = $_POST["fname"];
		$lname = $_POST["lname"];
		$groups = $_POST["gnames"];
		
		$numPeople = 0;
		if($stmt = $mysqli->prepare("select count(username) from person 
									 where fname = ? and lname = ?")){
			$stmt->bind_param("ss", $fname, $lname);
			$stmt->execute();
			$stmt->bind_result($numPeople);
			$stmt->fetch();
			$stmt->close();
		}		
		
		//handles duplicate entries
		if(isset($_POST["duplicate"])){
			$user = $_POST["duplicate"];
			echo "You have successfully added $fname $lname into";
			//check if All friendgroups were selected
			if($groups[0] == 1){
				echo " all your friend groups.<br>";
				if($stmt = $mysqli->prepare("insert into ingroup (gname, ownername, username)
											 (select distinct gname, ownername, person.username			
											  from friendgroup join person	
											  where ownername = ? and person.username = ?)")){
				$stmt->bind_param("ss", $_SESSION["username"], $user);
				$stmt->execute();
				$stmt->close();
				}
			}
			
			//loop through array to add all friendgroups selected
			else{
				for($i = 0; $i < count($groups) - 1; ++$i)
					echo " $groups[$i],";
				if(count($groups) > 1)
					echo " and";
				$last = $groups[count($groups) - 1];
				echo " $last.<br>";
				
				for($i = 0; $i < count($groups); ++$i){
					if($stmt = $mysqli->prepare("insert into ingroup (gname, ownername, username)
												 (select distinct gname, ownername, person.username			
												  from friendgroup join person	
												  where gname = ? and ownername = ? and person.username = ?)")){
					$stmt->bind_param("sss", $groups[$i], $_SESSION["username"], $user);
					$stmt->execute();
					$stmt->close();
					}
				}
			}
			echo "You will return to your page in 3 seconds, or click <a href=\"index.php\">here</a>.";
			header("refresh: 3; index.php");
		}
		
		else if($numPeople == 1){
			echo "You have successfully added $fname $lname into";
			//Checks if All friendgroups were selected
			if($groups[0] == 1){
				echo " all your friend groups.<br>";
				if($stmt = $mysqli->prepare("insert into ingroup (gname, ownername, username)
											 (select distinct gname, ownername, person.username			
											  from friendgroup join person	
											  where ownername = ? and person.username = (select username
                                                                                               from person where fname = ? and lname = ?))")){
				$stmt->bind_param("sss", $_SESSION["username"], $fname, $lname);
				$stmt->execute();
				$stmt->close();
				}
			}
			//loops through all friendgroups that were selected
			else{
				for($i = 0; $i < count($groups) - 1; ++$i)
					echo " $groups[$i],";
				if(count($groups) > 1)
					echo " and";
				$last = $groups[count($groups) - 1];
				echo " $last.<br>";
				
				for($i = 0; $i < count($groups); ++$i){
					if($stmt = $mysqli->prepare("insert into ingroup (gname, ownername, username)
												 (select distinct gname, ownername, person.username			
												  from friendgroup join person	
												  where gname = ? and ownername = ? and 
														person.username = (select username from person where fname = ? and lname = ?))")){
					$stmt->bind_param("ssss", $groups[$i], $_SESSION["username"], $fname, $lname);
					$stmt->execute();
					$stmt->close();
					}
				}
			}
			echo "You will return to your page in 3 seconds, or click <a href=\"index.php\">here</a>.";
			header("refresh: 3; index.php");
		}
		
		//handles people who are not in database
		else if($numPeople == 0){
			echo "$fname $lname cannot be found<br>";
			echo "Please try again. You will be redirected in 3 seconds, or click <a href=\"addfriend.php\">here</a>.";
			header("refresh: 3; addfriend.php");
		}
		
		//handles multiple people with same name
		else{
			echo "There are multiple people with the name $fname $lname.<br>";
			echo '<form action = "addfriend.php" method="POST">';
			echo 'Choose the person you are trying to add:<br>';
			echo '<select name="duplicate">';
			if($stmt = $mysqli->prepare("select username, fname, lname
										 from person where fname = ? and lname = ?")){
				//echo "<br>Query Worked<br>";
				$stmt->bind_param("ss", $fname, $lname);
				$stmt->execute();
				$stmt->bind_result($username, $fname, $lname);
				while($stmt->fetch())
					echo "<option value=$username>$fname $lname ($username)</option>";
				echo "<input type= \"hidden\" name=\"fname\" value=\"$fname\"><class=\"submit\">";
				echo "<input type= \"hidden\" name=\"lname\" value=\"$lname\"><class=\"submit\">";
				foreach($groups as $gname)
					echo "<input type= \"hidden\" name=\"gnames[]\" value=\"$gname\"><class=\"submit\">";				
				$stmt->close();
			}
			echo '</select><input type = "submit" value = "Submit">';
			echo '</form>';
		}
	}

  
  //if not then display the form for posting message
  else {
	$numGroups = 0;
	if($stmt = $mysqli->prepare("select count(gname) from friendgroup 
								 where ownername = ?")){
		$stmt->bind_param("s", $_SESSION["username"]);
		$stmt->execute();
		$stmt->bind_result($numGroups);
		$stmt->fetch();
		$stmt->close();
	}
	if($numGroups == 0){
		echo "You previously owned no friend groups. A default friend group has been made for you.<br>";
		if($stmt = $mysqli->prepare("insert into friendgroup (gname, descr, ownername) values ('default', null, ?)")){
			$stmt->bind_param("s", $_SESSION["username"]);
			$stmt->execute();
			$stmt->close();
		}
		echo "<br>";
	}		
	
    echo '<form action="addfriend.php" method="POST">';
    
	//List of users
	echo "Please enter the first and last names of the person you want to add.<br>";
	echo 'First Name: <input type="text" name="fname" /><br />';
    echo "\n";
	echo 'Last Name: <input type="text" name="lname" /><br />';
	echo "\n";
	
	//List of Friend Groups
	echo "<br>Friend Group(s) to add into:<br>";
	echo '<select multiple = "multiple" name = "gnames[]">';
	echo "<option value=1>All</option>";
	if($stmt = $mysqli->prepare("select distinct gname
								 from friendgroup
								 where ownername = ?")){
		$stmt->bind_param("s", $_SESSION["username"]);
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