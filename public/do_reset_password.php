<?php

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

include "../templates/connect_mysql.php";

$token = mysql_real_escape_string($_POST["token"]);
$password = mysql_real_escape_string($_POST["password"]);

//Validate password
if (strlen($password) < 8) {
    //Password is too short
    mysql_close($link);
    header("location: reset_password.php?status=invalid_password&token=$token");
}

//Verify the password reset is valid
$sql = "select userid, timestamp from passwordresets where hash='$token' and timestamp > date_sub(now(), interval 4 hour)";

$retval = mysql_query($sql);
if (!$retval) {
    die("Error validating token: ".mysql_error());
}

$count = mysql_num_rows($retval);
if ($count == 1) { //Make sure the password reset exists
    $row = mysql_fetch_assoc($retval);
    $id = $row["userid"];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    //Update the user's password
    $sql = "update users set password='$hash' where id='$id'";
    $retval = mysql_query($sql);

    if (!$retval) {
        mysql_close($link);
        header("location: login.php?status=reset_password_error");
        die();
    }

    //Clear out the password reset token
    $sql = "delete from passwordresets where userid='$id'";
    $retval = mysql_query($sql); //Assume success

    mysql_close($link);
    header("location: login.php?status=reset_password_success");
    die();
} else {
    //Invalid token
    mysql_close($link);
    header("location: login.php?status=invalid_reset_token");
    die();
}

?>