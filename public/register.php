<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron">
        <h1>Create an account</h1>
        <h4>Already have an account? <a href="login.php">Log in</a></h4>
        <br><br>
<?php
$status = $_GET["status"];
if ($status == "duplicate") {
    echo "<div class='alert alert-danger' role='alert'>".
        "That email address is already in use.".
        "</div>";
} else if ($status == "invalid_email") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Please enter a valid email address.".
        "</div>";
} else if ($status == "invalid_password") {
    echo "<div class='alert alert-danger' role='alert'>".
        "Your password must be at least 8 characters.".
        "</div>";
}
?>
        <form class="form-horizontal" action="create_account.php" method="POST">
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
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
            <br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>