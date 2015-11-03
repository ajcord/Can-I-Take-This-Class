<?php
$status = $_GET["status"];
if ($status == "duplicate") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The email address you entered is already in use.".
        "</div>";
} else if ($status == "invalid_email") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The email address you entered is invalid.".
        "</div>";
} else if ($status == "changed_email") {
    echo "<div class='alert alert-success' role='alert'>".
        "Your email address was changed successfully.".
        "</div>";
} else if ($status == "password_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The password you entered was not correct.".
        "</div>";
} else if ($status == "invalid_password") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Your new password must be at least 8 characters.".
        "</div>";
} else if ($status == "change_password_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Password change failed!".
        "</div>";
} else if ($status == "changed_password") {
    echo "<div class='alert alert-success' role='alert'>".
        "Your password was changed successfully.".
        "</div>";
} else if ($status == "invalid_registration_date") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The registration date you entered is invalid.".
        "</div>";
} else if ($status == "changed_registration_date") {
    echo "<div class='alert alert-success' role='alert'>".
        "Your registration date was changed successfully.".
        "</div>";
} else if ($status == "delete_account_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Account deletion failed!".
        "</div>";
} else if ($status == "deleted_course") {
    echo "<div class='alert alert-info' role='alert'>".
        "The course has been deleted from your list.".
        "</div>";
} else if ($status == "added_course") {
    echo "<div class='alert alert-success' role='alert'>".
        "The course has been added to your list.".
        "</div>";
} else if ($status == "delete_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The course you requested to delete is not in your list.".
        "</div>";
} else if ($status == "add_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "The course does not exist.".
        "</div>";
}
?>