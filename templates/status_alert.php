<?php

function createAlert($type, $message) {
    echo "<div class='alert alert-$type' role='alert'>$message</div>";
}

$status = $_GET["status"];

if ($status == "duplicate") {

    createAlert("danger", "The email address you entered is already in use.");

} else if ($status == "invalid_email") {

    createAlert("danger", "The email address you entered is invalid.");

} else if ($status == "changed_email") {

    createAlert("success", "Your email address was changed successfully.");

} else if ($status == "password_error") {

    createAlert("danger", "The password you entered was not correct.");

} else if ($status == "invalid_password") {

    createAlert("danger", "Your new password must be at least 8 characters.");

} else if ($status == "change_password_error") {

    createAlert("danger", "Password change failed!");

} else if ($status == "changed_password") {

    createAlert("success", "Your password was changed successfully.");

} else if ($status == "invalid_registration_date") {

    createAlert("danger", "The registration date you entered is invalid.");

} else if ($status == "changed_registration_date") {

    createAlert("success", "Your registration date was changed successfully.");

} else if ($status == "delete_account_error") {

    createAlert("danger", "Account deletion failed!");

} else if ($status == "deleted_course") {

    createAlert("info", "The course has been deleted from your list.");

} else if ($status == "added_course") {

    createAlert("success", "The course has been added to your list.");

} else if ($status == "delete_error") {

    createAlert("danger", "The course you requested to delete is not in your list.");

} else if ($status == "add_error") {

    createAlert("danger", "The course does not exist.");

}
?>