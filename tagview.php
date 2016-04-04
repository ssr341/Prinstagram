<!DOCTYPE html>
<!-- Taken from example Blog written by Raymond Mui -->
<html>
<title>Tag Approval</title>
<body>
<?php

include ("connectdb.php");

//if the user is not logged in, redirect them back to homepage
if(!isset($_SESSION["username"])) {
  echo "You are not logged in. ";
  echo "You will be returned to the homepage in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}
else{
  echo 'Below you can view tags for approval:<br />';
  echo "<br>";

  //print out all the messages from this user in a pretty table
    $username = $_SESSION["username"];
    if ($stmt = $mysqli->prepare("select pid, poster, pdate, caption, tagger 
								  from tag natural join photo
								  where taggee = ? and tstatus = false")) {
        $stmt->bind_param("s", $username);

        // execute query
        $stmt->execute();
        $stmt->bind_result($pid, $poster, $pdate, $caption, $tagger);
        // Printing results in HTML
        while ($stmt->fetch()) {
			echo "$tagger tagged you in the following photo:<br>";
			echo "<table border = '1'>\n";
	        echo "<tr>";
			echo "<td><b>ID</td><td><b>Poster</td><td><b>Date/Time</td><td><b>Caption</td>";
			echo "</tr><tr>";
            echo "<td>$pid</td><td>$poster</td><td>$pdate</td><td>$caption</td>";
			echo "</tr>\n";
			echo "</table>\n";

			echo '<form action="tagresponse.php" method="GET">';
			echo '<select name = "response">';
			echo "<option value=1>Accept</option>";
			echo "<option value=0>Reject</option>";
			echo "<input type= \"hidden\" name=\"pid\" value=\"$pid\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"poster\" value=\"$poster\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"pdate\" value=\"$pdate\"><class=\"submit\">";	
			echo "<input type= \"hidden\" name=\"caption\" value=\"$caption\"><class=\"submit\">";		
			echo "<input type= \"hidden\" name=\"tagger\" value=\"$tagger\"><class=\"submit\">";				
			echo '</select><input type="submit" value="Submit" />';
			echo '</form>';
			echo "<br><br>";
        }
        $stmt->close();
	$mysqli->close();
    }
}


echo '<a href="index.php">Go back</a>';
echo "\n";

?>
</body>
</html>