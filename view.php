<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<body>
<?php

include ("connectdb.php");

//check if the user exists and prints out username, if not redirects back to homepage
if ($stmt = $mysqli->prepare("select username from person where username = ?")) {
  $stmt->bind_param("s", $_SESSION["username"]);
  $stmt->execute();
  $stmt->bind_result($username);
  if($stmt->fetch()) {
	$username = htmlspecialchars($username);
	echo "<title>$username's Prinstagram</title>\n";
	echo "$username's Profile: <br />\n";
  }
  else {
    echo "Prinstagram not found. \n";
    echo "You will be redirected in 3 seconds or click <a href=\"index.php\">here</a>.\n";
    header("refresh: 3; index.php");
  }
  $stmt->close();
}

//check if the user is also the one who is logged in
if(isset($_SESSION["username"])) {
  echo 'Below are pictures visible to you. You may click <a href="post.php">here</a> to post a photo, or click <a href="index.php">here</a> to return to go to the homepage.<br />';
  echo "\n";

//print out all the messages from this user in a pretty table
    if ($stmt1 = $mysqli->prepare("(select pid, poster, pdate, caption, is_pub
									from photo
								    where poster = ? or is_pub = true) union
								   (select pid, poster, pdate, caption, is_pub
								    from ingroup natural join shared natural join photo
								    where username = ?) union
								   (select pid, poster, pdate, caption, is_pub
								    from photo natural join tag
									where taggee = ? and tstatus = true) order by pdate desc")) {
        $stmt1->bind_param("sss", $_SESSION["username"], $_SESSION["username"], $_SESSION["username"]);

        // execute query
        $stmt1->execute();
		echo "<br>";
		
        // Store values from photo fetch
		$photos = $stmt1->get_result();
		$stmt1->close();
		
		//Print Results
		while($photo = $photos->fetch_array(MYSQLI_NUM)){
			//Show Photo
			//echo "Photo $photo[0]\n";
			echo "<table border = '1'>\n";
			echo "<tr>";
			//0 = pid, 1 = poster, 2 = pdate, 3 = caption
			echo "<td><b>ID</td><td><b>Poster</td><td><b>Date/Time</td><td><b>Caption</td>";
			echo "</tr>\n";
			echo "<td>$photo[0]</td><td>$photo[1]</td><td>$photo[2]</td><td>$photo[3]</td>";
			echo "</tr>\n";
			echo "</table>\n";
				
			//Show Tags
			if($stmt2 = $mysqli->prepare("select fname, lname
										  from person join tag
										  where taggee = username and pid = ? and tstatus = true")){
				$stmt2->bind_param("i", $photo[0]);
				$stmt2->execute();
				$stmt2->bind_result($fname, $lname);
				echo "Tagged:<br>";
				echo "<table border = '1'>\n";
				while($stmt2->fetch()){
					echo "<tr>";
					echo "<td>$fname $lname</td>";
					echo "</tr>\n";
				}
				echo "</table>";
				$stmt2->close();
			}
			//Dropdown menu listing users to tag
			echo '<form action = "tag.php" method="GET">';
			echo 'Choose a person to tag<br>';
			echo '<select name="username">';
			if($stmt21 = $mysqli->prepare("select username, fname, lname
										  from person")){
				$stmt21->execute();
				$stmt21->bind_result($username, $fname, $lname);
				while($stmt21->fetch())
					echo "<option value=$username>$fname $lname ($username)</option>";
				echo "<input type= \"hidden\" name=\"pid\" value=\"$photo[0]\"><class=\"submit\">";	
				echo "<input type= \"hidden\" name=\"poster\" value=\"$photo[1]\"><class=\"submit\">";	
				echo "<input type= \"hidden\" name=\"pdate\" value=\"$photo[2]\"><class=\"submit\">";	
				echo "<input type= \"hidden\" name=\"caption\" value=\"$photo[3]\"><class=\"submit\">";		
				echo "<input type= \"hidden\" name=\"is_pub\" value=\"$photo[4]\"><class=\"submit\">";		
				$stmt21->close();
			}
			echo '</select><input type = "submit" value = "Tag">';
			echo '</form>';
			
			//Show Comments
			echo "<br>";
			if($stmt3 = $mysqli->prepare("select fname, lname, ctime, ctext
										  from person natural join commenton natural join comment
										  where pid = ?")){
				$stmt3->bind_param("i", $photo[0]);
				$stmt3->execute();
				$stmt3->bind_result($fname, $lname, $ctime, $ctext);
				
				echo "<table border = '1'>\n";
				echo "Comments:<br>";
				echo "<tr>";
				echo "<td><b>Name:</td><td><b>Date/Time:</td><td><b>Comment:</td>";
				echo "</tr>";
				while($stmt3->fetch()){
					echo "<tr>";
					echo "<td>$fname $lname</td><td>$ctime</td><td>$ctext</td>";
					echo "</tr>\n";
				}
				echo "</table>";
				$stmt3->close();
			}
			//Comment box
			?>
			Please comment below<br>
			<form action = "comment.php" method="GET">
			<textarea cols="25" rows="1" name="comment" />Insert your comment here</textarea><br />
			<?php
			echo "<input type= \"hidden\" name=\"pid\" value=\"$photo[0]\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"poster\" value=\"$photo[1]\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"pdate\" value=\"$photo[2]\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"caption\" value=\"$photo[3]\"><class=\"submit\">";					
			?>
			<input type = "submit" value = "Comment">
			</form>
			<?php
			echo "<br><br><br><br>";
		}
	}
	$mysqli->close();
    
}

echo '<a href="index.php">Go back</a><br /><br />';
echo "\n";

?>
</body>
</html>