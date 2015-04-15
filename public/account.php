<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("location: login.php?status=not_logged_in&next=account.php");
}
include "../templates/header.php";
?>

<div class="container">
    <div class="jumbotron">
        <h1>Account settings</h1>
        <br><br>
<?php
$status = $_GET["status"];
if ($status == "invalid_email") {
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
} else if ($status == "delete_account_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Account deletion failed!".
        "</div>";
}
?>
        <form class="form-horizontal" action="modify_account.php" method="POST">
            <div class="form-group">
                <label for="inputEmail" class="col-sm-2">New email:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail" name="email">
                </div>
            </div>
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary" name="change_email" value="1">Change email</button>
            </div>
            <br><br><br>
        </form>
        <form class="form-horizontal" action="modify_account.php" method="POST">
            <div class="form-group">
                <label for="inputOldPassword" class="col-sm-2">Old password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputOldPassword" name="old_password">
                </div>
            </div>
            <div class="form-group">
                <label for="inputNewPassword" class="col-sm-2">New password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputNewPassword" name="new_password">
                </div>
            </div>
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary" name="change_password" value="1">Change password</button>
            </div>
            <br><br><br>
        </form>
        <form class="form-horizontal" action="modify_account.php" method="POST">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">Delete this account</button>
            </div>
            <br><br>
            <div class="modal fade" id="deleteModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Delete account</h4>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete your account? You cannot undo this action.<br><br>
                            Enter your password to confirm.<br><br>
                            <label for="inputPassword" class="col-sm-2">Password:</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword" name="password">
                            </div>
                            <br><br><br>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" name="delete_account" value="1">Permanently delete my account</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>