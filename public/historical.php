<?php
$use_highcharts = true;
include "../templates/header.php";
include "../templates/connect_mysql.php";
include "../templates/analyze.php";
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
                            <button id="search-button" class="btn btn-info btn-lg" type="submit">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
<?php if (!is_null($_GET["q"])): ?>
        <br><br>

<?php

$q = $_GET["q"];
$sem = $_GET["semester"];
$start_date = NULL;

//Get the start date of the given semester
$semesters_retval = get_semesters_before_date(date("Y-m-d"));

while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

    $curr_sem = $semester_row["semester"];

    //Print a list of links for each previous semester
    echo "<a href='?q=$q&semester=$curr_sem'>$curr_sem</a> ";

    if ($curr_sem == $sem) {
        $start_date = $semester_row["date"];
    }
}

?>

        <div id="chart-container">Loading...</div>

        <script>

<?php

if (is_null($q)) {
    return;
}

$parsed = split_course($q);
$subject_code = $parsed["subject"];
$course_num = $parsed["number"];

$series = array();
$series_list = array();

$enrollment_retval = query_semester($sem, $start_date, NULL, "everything",
                        $subject_code, $course_num, true);

while ($enrollment_row = mysql_fetch_assoc($enrollment_retval)) {

    $week = $enrollment_row["week"];
    $type = $enrollment_row["type"];
    $status = $enrollment_row["status"];
    $count = $enrollment_row["count"];

    $series[$type][$week] += $count;
}

$last_week = get_last_week($sem, $start_date)["week"];

//Fill in empty weeks with zeroes and cut off the last week
unset($series[$type][$last_week]);
foreach ($series as $type => $data) {
    for ($i  = 0; i < $last_week; $i++) {
        if (!array_key_exists($i, $data)) {
            $series[$type][$week] = 0;
        }
    }
    
    ksort($series[$type]);

    $row = ["name" => $type, "data" => $series[$type]];
    array_push($series_list, $row);
}

echo "var series = ".json_encode($series_list).";\n\n";


$chart_title = $subject_code." ".$course_num;
if (is_null($subject_code) && is_null($course_num)) {
    $chart_title = "University of Illinois";
}

?>

/**
 * Returns whether the viewport is small or extra small.
 *
 * @return     {boolean}  True if the screen is small or extra small, else false
 */
function isSmallScreen() {
    return $(".device-sm").is(":visible");
}

$(function () {
    $("#chart-container").highcharts({
        chart: {
            type: "spline"
        },
        title: {
            text: "<?php echo $chart_title ?>"
        },
        legend: {
            layout: (isSmallScreen() ? "horizontal" : "vertical"),
            align: (isSmallScreen() ? "center" : "right"),
            verticalAlign: (isSmallScreen() ? "bottom" : "middle"),
            floating: false,
            borderWidth: 1,
        },
        xAxis: {
            title: {
                text: "Week of registration"
            },
            allowDecimals: false
        },
        yAxis: {
            title: {
                text: "Number of available sections"
            },
            allowDecimals: false
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

<?php endif ?>
    </div>
</div>

<div class="device-sm visible-sm-block visible-xs-block"></div>

<?php include "../templates/footer.php"; ?>