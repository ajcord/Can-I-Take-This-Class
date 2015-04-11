<?php
session_start();

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

include "../templates/connect_mysql.php";

$change_email = $_POST["change_email"];
$change_password = $_POST["change_password"];
$delete_account = $_POST["delete_account"];

$email = mysql_real_escape_string($_POST["email"]);
$old_password = mysql_real_escape_string($_POST["old_password"]);
$new_password = mysql_real_escape_string($_POST["new_password"]);
$password = mysql_real_escape_string($_POST["password"]);

if (isset($change_email)) {
    //Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        mysql_close($link);
        header("location: account.php?status=invalid_email");
        die();
    }

    //Update the email address on record
    $id = $_SESSION["id"];
    $sql = "update users set email='$email' where id='$id'";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error changing email: ".mysql_error());
    }

    mysql_close($link);
    $_SESSION["email"] = $email;
    header("location: account.php?status=changed_email");
} else if (isset($change_password)) {
    //Verify the old password
    $id = $_SESSION["id"];
    $sql = "select password from users where id='$id'";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error authenticating: ".mysql_error());
    }

    $row = mysql_fetch_assoc($retval);
    $hash = $row["password"];
    if (!password_verify($old_password, $hash)) {
        //Invalid password
        mysql_close($link);
        header("location: account.php?status=password_error");
        die();
    }

    //Validate password
    if (strlen($new_password) < 8) {
        //Password is too short
        mysql_close($link);
        header("location: account.php?status=invalid_password");
        die();
    }

    //Update the password on record
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "update users set password='$hash' where id='$id'";
    $retval = mysql_query($sql);

    if (!$retval) {
        mysql_close($link);
        header("location: account.php?status=change_password_error");
        die();
    }

    mysql_close($link);
    header("location: account.php?status=changed_password");
    die();
} else if (isset($delete_account)) {
    //Verify the password
    $id = $_SESSION["id"];
    $sql = "select password from users where id='$id'";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error authenticating: ".mysql_error());
    }

    $row = mysql_fetch_assoc($retval);
    $hash = $row["password"];
    if (!password_verify($password, $hash)) {
        //Invalid password
        mysql_close($link);
        header("location: account.php?status=password_error");
        die();
    }


    //TODO: Remove the user and their records from all tables
    $sql = "delete from users where id='$id'";
    $retval = mysql_query($sql);

    if (!$retval) {
        mysql_close($link);
        header("location: account.php?status=delete_account_error");
        die();
    }

    mysql_close($link);
    session_destroy();
    header("location: login.php?status=deleted_account");
    die();
}

?>