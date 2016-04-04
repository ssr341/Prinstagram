<!DOCTYPE html>
<!-- Example Blog written by Raymond Mui -->
<html>
<title>Prinstagram</title>

<?php

include ("connectdb.php");

if(!isset($_SESSION["username"])) {
  echo "Welcome to Prinstagram! You are not logged in. <br /><br >\n";
  echo '<a href="login.php">Login</a> to your account.<br><br><a href="register.php">Register</a> if you don\'t have an account yet.';
  echo "\n";
}
else {
  $username = htmlspecialchars($_SESSION["username"]);
  echo "Welcome $username. You are logged in.<br /><br />\n";
  echo '<a href="view.php?username=';
  echo htmlspecialchars($_SESSION["username"]);
  echo '">Go to your profile</a><br> 
		<a href="post.php">Post a photo</a><br> 
		<a href="tagview.php"> View tags that need your approval </a><br>   
		<a href="addfriend.php">Add a friend to one of your FriendGroups</a><br>  
		<a href="defriend.php">Remove friends from your FriendGroups</a><br><br>
		<a href="logout.php">Logout</a>';
  echo "\n";
}

?>

</html>