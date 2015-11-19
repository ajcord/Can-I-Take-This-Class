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
        <br><br>
        <div id="chart-container"></div>
    </div>
</div>

<script>



<?php

$q = $_GET["q"];
if (is_null($q)) {
    return;
}

include "../templates/connect_mysql.php";
include "../templates/analyze.php";

$parsed = split_course($q);
$subject_code = $parsed["subject"];
$course_num = $parsed["number"];

$weeks = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26];
$series = array();
$series_list = array();

$semesters_retval = get_semesters_before_date(date("Y-m-d"));

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

        $series[$type][$week] += $count;
    }
}

//Fill in empty weeks with zeroes
foreach ($series as $type => $data) {
    foreach ($weeks as $week) {
        if (!array_key_exists($week, $data)) {
            $series[$type][$week] = 0;
        }
    }
    
    ksort($series[$type]);

    $row = ["name" => $type, "data" => $series[$type]];
    array_push($series_list, $row);
}

echo "var weeks = ".json_encode($weeks).";\n\n";
echo "var series = ".json_encode($series_list).";\n\n";

?>


$(function () {
    $("#chart-container").highcharts({
        chart: {
            type: "spline"
        },
        title: {
            text: "<?php echo $subject_code." ".$course_num ?>"
        },
        legend: {
            layout: "vertical",
            align: "left",
            verticalAlign: "top",
            x: 150,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || "#FFFFFF"
        },
        xAxis: {
            categories: weeks
        },
        yAxis: {
            title: {
                text: "Number of available sections"
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: " sections"
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        series: series
    });
});
</script>

<?php include "../templates/footer.php"; ?>