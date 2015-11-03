<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron">
        <h1>Create an account</h1>
        <h4>Already have an account? <a href="login.php">Log in</a></h4>
        <br><br>
<?php include "../templates/status_alert.php"; ?>
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
            <div class="form-group">
                <label for="inputRegistrationDate" class="col-sm-2">Registration date:</label>
                <div class="col-sm-10">
                    <input type="date" class="form-control" id="inputRegistrationDate" name="registration_date">
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