<?php

//Provide the PHP 5.5 password interface for old versions of PHP
require_once("../lib/password.php");

include "../templates/connect_mysql.php";

$email = mysql_real_escape_string($_POST["email"]);

$sql = "select id, password from users where email='$email'";

$retval = mysql_query($sql);
if (!$retval) {
    die("Error checking email: ".mysql_error());
}

$count = mysql_num_rows($retval);
if ($count == 1) { //Make sure the user exists
    //Generate the hash value
    $id = $row["id"];
    $password = $row["password"];
    $prehash = $id.$password.$email;
    $hash = password_hash($prehash, PASSWORD_DEFAULT);

    $sql = "insert into passwordresets (userid, hash) values ($id, '$hash')";
    $retval = mysql_query($sql); //Assume success

    $to = $email;
    $subject = "Your password reset email for ClassMaster";
    $body = "We recently received a request to reset the password for ".$email.
            " at ClassMaster.<br><br>".
            "To reset your password, please click here: ".
            "<a href=\"http://classmaster.web.engr.illinois.edu/reset_password.php?token=".$hash."\">Reset Password</a>".
            "<br><br>Regards,<br>ClassMaster";
    $headers  = "MIME-Version: 1.0\n".
                "Content-type: text/html; charset=iso-8859-1\n".
                "From: no-reply@classmaster.web.engr.illinois.edu";

    if (!mail($to, $subject, $body, $headers)) {
        //Sending failed
        mysql_close($link);
        header("location: forgot_password.php?status=reset_failed");
    }
}

mysql_close($link);
header("location: login.php?status=reset_sent");

?>