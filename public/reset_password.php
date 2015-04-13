<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron">
        <h1>Reset password</h1>
        <br><br>
        Please enter your new password below.
        <br><br>
<?php
$status = $_GET["status"];
if ($status == "invalid_password") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Your password must be at least 8 characters.".
        "</div>";
}
?>
        <form class="form-horizontal" action="do_reset_password.php" method="POST">
            <div class="form-group">
                <label for="inputPassword" class="col-sm-2">New password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="inputPassword" name="password">
                </div>
<?php
$token = $_GET["token"];
echo "<input type='hidden' value='$token' name='token'>";
?>
            </div>
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary">Reset password</button>
                <a href="login.php" class="btn btn-default">Cancel</a>
            </div>
            <br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>