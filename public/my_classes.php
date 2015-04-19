<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("location: login.php?status=not_logged_in&next=my_classes.php");
}
include "../templates/header.php";
?>

<div class="container">
    <div class="jumbotron">
        <h1>My classes</h1>
        <br><br>
        <form class="form-horizontal" action="modify_classes.php" method="POST">

<?php
//TODO: get their classes and display them
?>

            <br><br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>