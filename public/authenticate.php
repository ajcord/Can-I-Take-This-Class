<?php

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

include "../templates/connect_mysql.php";


$email = mysql_real_escape_string($_POST["email"]);
$password = mysql_real_escape_string($_POST["password"]);
$next = $_POST["next"];

$sql = "select id, password from users where email='$email'";

$retval = mysql_query($sql);
if (!$retval) {
    die("Error logging in: ".mysql_error());
}

$count = mysql_num_rows($retval);
if ($count == 1) { //Exactly one user can be associated with an email address
    $row = mysql_fetch_assoc($retval);
    $hash = $row["password"];
    $id = $row["id"];

    if (password_verify($password, $hash)) {
        //The password was correct
        //Check whether the password needs to be rehashed
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            //Create a new hash and replace the old one
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "update users set password='$new_hash' where id='$id'";
            mysql_query($sql); //Assume it worked
        }

        //Set the session variables
        if (!session_start()) {
            //Session error
            header("location: login.php?status=session_error");
            die();
        }
        $_SESSION["id"] = $id;
        $_SESSION["email"] = $email;
        mysql_close($link);
        header("location: $next?status=login");
        die();
    } else {
        //Invalid password
        mysql_close($link);
        header("location: login.php?status=login_error");
        die();
    }
} else {
    //Invalid email
    mysql_close($link);
    header("location: login.php?status=login_error");
    die();
}

?>