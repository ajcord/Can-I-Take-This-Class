<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron">
        <h1>Log in</h1>
        <h4>Don't have an account? <a href="register.php">Get one!</a></h4>
        <br><br>
<?php
$status = $_GET["status"];
if ($status == "login_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Your email address or password is incorrect.".
        "</div>";
} else if ($status == "session_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "There was an error logging in. Please try again.".
        "</div>";
} else if ($status == "reset_sent") {
    echo "<div class='alert alert-info' role='alert'>".
        "If the email address you entered is correct, an email containing password reset instructions has been sent to it.".
        "</div>";
} else if ($status == "reset_password_success") {
    echo "<div class='alert alert-success' role='alert'>".
        "Password reset successfully".
        "</div>";
} else if ($status == "reset_password_error") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Password reset failed!".
        "</div>";
} else if ($status == "invalid_reset_token") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Invalid password reset link. You may have used an expired reset link.".
        "</div>";
}
?>
        <form class="form-horizontal" action="authenticate.php" method="POST">
            <div class="form-group">
                <label for="inputEmail" class="col-sm-2">Email:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail" name="email">
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword" class="col-sm-2">Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputPassword" name="password">
                </div>
            </div>
            <input type="hidden" name="next" value="index.php">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary">Login</button>
                <a href="forgot_password.php" class="btn btn-link">Forgot password</a>
            </div>
            <br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>