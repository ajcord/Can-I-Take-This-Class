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
        <form class="form-horizontal" action="historical.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <h2>Search for a class or subject</h2>
                    <div class="input-group col-md-12">
                        <input id="search-field" name="q" type="text" class="form-control input-lg" placeholder="e.g. CS 225, PHYS, etc." />
                        <span class="input-group-btn">
                            <button id="search-button" class="btn btn-info btn-lg" type="submit" name="search">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </form>

<?php

$search = $_GET["q"];
if (is_null($search)) {
    return;
}

include "../templates/connect_mysql.php";
include "../templates/analyze.php";

$parsed = split_course($q);
$subject_code = $parsed["subject_code"];
$course_num = $parsed["course_num"];

$weeks = array();
$series = array();

$semesters_retval = get_semesters_before_date($date);

while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

    $sem = $semester_row["semester"];
    $start_date = $semester_row["date"];

    $enrollment_retval = query_semester($sem, $start_date, NULL, "everything",
                            $subject_code, $course_num, true);

    while ($enrollment_row = mysql_fetch_assoc($enrollment_retval)) {

        $week = $enrollment_row["week"];
        $type = $enrollment_row["type"];
        $status = $enrollment_row["status"];
        $count = $enrollment_row["count"];

        //Ugly way of listing all weeks in the x axis
        if (!in_array($week, $weeks)) {
            $weeks[$week] = $week;
        }

        $series[$type][$week] += $count;
    }
}

var_dump($series);

?>
    </div>
</div>

<?php include "../templates/footer.php"; ?>