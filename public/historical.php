<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("location: login.php?status=not_logged_in&next=my_classes.php");
}
$use_highcharts = true;
include "../templates/header.php";
?>

<div class="container">
    <div class="jumbotron">
        <h1>Historical data</h1>
        <br><br>
        <div class="row">
            <div class="col-md-6">
                <h2>Search for a class or subject</h2>
                <div class="input-group col-md-12">
                    <input id="course-field" name="course" type="text" class="form-control input-lg" placeholder="CS 225" />
                    <span class="input-group-btn">
                        <button id="add-course-button" class="btn btn-info btn-lg" type="submit" name="add_course">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>