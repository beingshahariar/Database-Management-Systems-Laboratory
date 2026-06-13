<?php

include 'connect.php';

session_start();
session_unset();
session_destroy();

// redirect rider to login page
header('location:../rider/rider_login.php');
exit;

?>
