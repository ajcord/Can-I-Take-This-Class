<?php

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");


$email = mysql_real_escape_string($_POST["email"]);
$password = mysql_real_escape_string($_POST["password"]);
$next = $_POST["next"];

//TODO: validate email and password

$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "insert into users (email, password) values ('$email', '$hash')";

$retval = mysql_query($sql);
if (!$retval) {
    //Account probably already exists
    header("location: register.php?status=duplicate");
} else {
    header("location: $next?status=newuser");
}
?>