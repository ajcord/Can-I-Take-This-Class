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

$sql = "select id, password from users where email='$email'";

$retval = mysql_query($sql);
if (!$retval) {
    die("Error logging in: ".mysql_error());
}

$count = mysql_num_rows($retval);
if ($count == 1) {
    $row = mysql_fetch_assoc($retval);
    $hash = $row["password"];

    if (password_verify($password, $hash)) {
        if (!session_start()) {
            //Session error
            header("location: login.php?status=session_error");
        }
        $_SESSION["id"] = $row["id"];
        $_SESSION["email"] = $email;
        header("location: $next?status=login");
    } else {
        //Invalid password
        header("location: login.php?status=login_error");
    }
} else {
    //Invalid email
    header("location: login.php?status=login_error");
}
?>