<?php
session_start();


session_destroy();

//  Redirect to login page after logout
header("Location: login.php");
exit();
?>
