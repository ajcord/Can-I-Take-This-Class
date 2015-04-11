<?php

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

include "../templates/connect_mysql.php";


$email = mysql_real_escape_string($_POST["email"]);
$password = mysql_real_escape_string($_POST["password"]);
$next = $_POST["next"];

//Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    mysql_close($link);
    header("location: register.php?status=invalid_email");
    die();
}

//Validate password
if (strlen($password) < 8) {
    //Password is too short
    mysql_close($link);
    header("location: register.php?status=invalid_password");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "insert into users (email, password) values ('$email', '$hash')";

$retval = mysql_query($sql);

if (!$retval) {
    //Account probably already exists
    mysql_close($link);
    header("location: register.php?status=duplicate");
} else {
    //Account created. Login.
    $sql = "select id from users where email='$email'";
    $retval = mysql_query($sql); //Assume it worked
    $row = mysql_fetch_assoc($retval);
    $id = $row["id"];

    //Set the session variables
    if (!session_start()) {
        //Session error
        mysql_close($link);
        header("location: login.php?status=session_error");
        die();
    }
    $_SESSION["id"] = $id;
    $_SESSION["email"] = $email;
    mysql_close($link);
    header("location: $next?status=newuser");
}


?>