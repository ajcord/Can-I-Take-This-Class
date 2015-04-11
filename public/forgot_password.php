<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron">
        <h1>Reset password</h1>
        <br><br>
        Please enter your email address below. We will send you an email with instructions to reset your password.
        <br><br>
        <form class="form-horizontal" action="send_reset_email.php" method="POST">
            <div class="form-group">
                <label for="inputEmail" class="col-sm-2">Email:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="inputEmail" name="email">
                </div>
            </div>
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary">Send reset email</button>
                <a href="login.php" class="btn btn-default">Cancel</a>
            </div>
            <br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>