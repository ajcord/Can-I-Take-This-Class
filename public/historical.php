<?php
$use_highcharts = true;
include "../templates/header.php";
include "../templates/connect_mysql.php";
include "../templates/analyze.php";

$q = mysql_real_escape_string($_GET["q"]);
$sem = mysql_real_escape_string($_GET["semester"]);
$semesters = array();
$start_date = NULL;
$instruction_date = NULL;

//If no semester is given, pick the latest one
$pick_last_semester = false;
if (is_null($_GET["semester"])) {
    $pick_last_semester = true;
}

//Get the start date of the given semester
$semesters_retval = get_semesters_before_date(date("Y-m-d"));

while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

    $curr_sem = $semester_row["semester"];
    $curr_start_date = $semester_row["date"];
    $curr_instruction_date = $semester_row["instructiondate"];

    array_push($semesters, $curr_sem);

    if ($curr_sem == $sem || $pick_last_semester) {
        $sem = $curr_sem;
        $start_date = $curr_start_date;
        $instruction_date = $curr_instruction_date;
    }
}

//Put the most recent semesters first
$semesters = array_reverse($semesters);

//If the semester given is not valid, pick the most recent one
if (is_null($start_date)) {
    $sem = $semester_row["semester"];
    $start_date = $semester_row["date"];
    $instruction_date = $semester_row["instructiondate"];
}

//Calculate which week instruction begins in
$instruction_week = floor(date_diff(new DateTime($instruction_date),
                                    new DateTime($start_date))->days/7);

?>

<div class="container">
    <div class="jumbotron">
        <form class="form-horizontal" action="historical.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <h2>Search for a class or subject</h2>
                    <div class="input-group col-md-12">
                        <input id="search-field" name="q" type="text" class="form-control input-lg" placeholder="e.g. CS 225, PHYS, etc." />
<?php if (!is_null($_GET["semester"])): ?>
                        <input type="hidden" name="semester" value="<?php echo $_GET['semester'] ?>" />
<?php endif ?>
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
        <br>
        <p>
        Semester:

<?php

//Print the semester links
foreach ($semesters as $curr_sem) {
    if ($curr_sem == $sem) {
        echo "<b>$curr_sem</b> ";
    } else {
        echo "<a href='?q=$q&semester=$curr_sem'>$curr_sem</a> ";
    }
}

?>
        </p>
        <div id="chart-container"></div>

<script>

//Hide the "no data" message temporarily
Highcharts.setOptions({
    lang: {
        noData: ""
    }
});

//Dummy chart for initial loading message
$("#chart-container").highcharts({
    title: {
        text: ""
    }
})

$("#chart-container").highcharts().showLoading();

</script>


<?php

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
foreach ($series as $type => $data) {
    unset($series[$type][$last_week]);
    for ($i  = 0; $i < $last_week; $i++) {
        if (!array_key_exists($i, $data)) {
            $series[$type][$i] = 0;
        }
    }
    
    ksort($series[$type]);

    $row = ["name" => $type, "data" => $series[$type]];
    array_push($series_list, $row);
}


$chart_title = $subject_code." ".$course_num;
if (is_null($subject_code) && is_null($course_num)) {
    $chart_title = "University of Illinois";
}

?>


<script>
/**
 * Returns whether the viewport is small or extra small.
 *
 * @return     {boolean}  True if the screen is small or extra small, else false
 */
function isSmallScreen() {
    return $(".device-sm").is(":visible");
}

//Hide the "no data" message temporarily
Highcharts.setOptions({
    lang: {
        noData: "No data for the given class"
    }
});

//Actual chart
$("#chart-container").highcharts({
    chart: {
        type: "spline"
    },
    title: {
        text: "<?php echo $chart_title ?>"
    },
    subtitle: {
        text: "<?php echo $sem ?>"
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
        allowDecimals: false,
        plotBands: [{
            from: <?php echo $instruction_week ?>,
            to: <?php echo $last_week ?>,
            color: "rgba(68, 170, 213, 0.2)",
            label: "Classes in session"
        }]
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
    series: <?php echo json_encode($series_list) ?>
});

</script>

<?php endif ?>
    </div>
</div>

<div class="device-sm visible-sm-block visible-xs-block"></div>

<?php include "../templates/footer.php"; ?>