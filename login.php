<!DOCTYPE html>
<!-- Taken from Example Blog written by Raymond Mui -->
<html>
<title>Login</title>

<?php

include "connectdb.php";

//if the user is already logged in, redirect them back to homepage
if(isset($_SESSION["username"])) {
  echo "You are already logged in. \n";
  echo "You will be redirected in 3 seconds or click <a href=\"index.php\">here</a>.\n";
  header("refresh: 3; index.php");
}
else {
  //if the user have entered both entries in the form, check if they exist in the database
  if(isset($_GET["username"]) && isset($_GET["password"])) {
  $input_user = $_GET["username"];
  $input_pass = $_GET["password"];
  $tmp = md5($input_pass);	

    //check if entry exists in database
    if ($stmt = $mysqli->prepare("select username, password from person where username = ? and password = ?")) {
      $stmt->bind_param("ss", $input_user, $tmp);
      $stmt->execute();
      $stmt->bind_result($input_user, $tmp);
	    //if there is a match set session variables and send user to homepage
        if ($stmt->fetch()) {
		  $_SESSION["username"] = $input_user;
		  $_SESSION["password"] = $input_pass;
		  $_SESSION["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"]; //store clients IP address to help prevent session hijack
          echo "Login successful. \n";
          echo "You will be redirected in 3 seconds or click <a href=\"index.php\">here</a>.";
          header("refresh: 3; index.php");
        }
		//if no match then tell them to try again
		else {
		  sleep(1); //pause a bit to help prevent brute force attacks
		  echo "Your username or password is incorrect.<br>Click <a href=\"login.php\">here</a> to try again.";
		}
      $stmt->close();
	  $mysqli->close();
    }  
  }
  //if not then display login form
  else {
    echo "Enter your username and password below: <br /><br />\n";
    echo '<form action="login.php" method="GET">';
	echo "\n";
    echo 'Username: <input type="text" name="username" /><br />';
	echo "\n";
    echo 'Password: <input type="password" name="password" /><br />';
	echo "\n";
    echo '<input type="submit" value="Submit" />';
	echo "\n";
    echo '</form>';
	echo "\n";
	echo '<br /><a href="index.php">Go back</a>';
  }
}
?>

</html>