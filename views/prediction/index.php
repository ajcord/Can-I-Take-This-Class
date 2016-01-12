<?php

require_once __DIR__."/../../templates/connect_mysql.php";
require_once __DIR__."/../../models/Course.php";
require_once __DIR__."/../../models/Predictor.php";

$subject_code = $_GET["subjectcode"];
$course_num = intval($_GET["coursenumber"]);
$registration_date = new DateTime($_GET["date"]);

$formatted_date = strftime("%x", $registration_date->getTimestamp());

$course = new Course($dbh, $subject_code, $course_num);
$predictor = new Predictor($dbh, $course, $registration_date);

$result = $predictor->getOverallLikelihood();

/**
 * Returns a string representing a percentage with error.
 */
function percent_string($percent, $error) {

    $lower_bound = round(max($percent - $error, 0)*100);
    $upper_bound = round(min($percent + $error, 1)*100);

    if ($lower_bound == $upper_bound) {
        return round($lower_bound)."%";
    } else {
        return $lower_bound."&ndash;".$upper_bound."%";
    }
}

$overall = $result["on_date"]["percent"];
$overall_error = $result["on_date"]["error"];
$overall_after = $result["after_date"]["percent"];
$overall_after_error = $result["after_date"]["error"];

$overall_pct = percent_string($overall, $overall_error);
$overall_after_pct = percent_string($overall_after, $overall_after_error);
?>

<? $use_highcharts = true; include __DIR__."/../../templates/header.php" ?>

<div class="jumbotron text-center">

<? if ($overall >= 0.90): ?>

    <h1>Yes <span class="label label-success"><?= $overall_pct ?></span></h1>
    <p>
        You have a very good chance of getting into getting into <?= $course ?>.
        &#x1f60e; &#x1f389;
    </p>

<? elseif ($overall >= 0.70): ?>

    <h1>Probably <span class="label label-success"><?= $overall_pct ?></span></h1>
    <p>
        You have a decent chance of getting into <?= $course ?>.
        &#x1f603; &#x1f44d;
    </p>

<? elseif ($overall >= 0.40): ?>

    <h1>Maybe <span class="label label-warning"><?= $overall_pct ?></span></h1>
    <p>
        Your odds aren't great, but you might still get into <?= $course ?>.
    </p>

<? elseif ($overall >= 0.20): ?>

    <h1>Probably not <span class="label label-danger"><?= $overall_pct ?></span></h1>
    <p>
        Don't count on getting into <?= $course ?>.
        &#x1f615;
    </p>

<? else: ?>

    <h1>Nope <span class="label label-danger"><?= $overall_pct ?></span></h1>
    <p>
        You will almost certainly not get into <?= $course ?> on your
        registration date.
        &#x1f641;
    </p>

<? endif ?>

<? if ($overall < 0.60): ?>
    <? if ($overall_after > ($overall + 0.10)): ?>
        <p>
            However, you have a <?= $overall_after_pct ?> chance of getting in
            by the start of the semester, so keep trying!
        </p>
    <? endif ?>
<? endif ?>

</div>

<h2>Breakdown by section</h2>

<?php
$result = $predictor->getItemizedLikelihood();
?>

<table class="table table-hover">
    <caption>
        Your chances of getting into each type of section in the class,
        both on your registration date and after it.
    </caption>
    <thead>
        <tr>
            <td>Type</td>
            <td>On <?= $formatted_date ?></td>
            <td>After <?= $formatted_date ?></td>
        </tr>
    </thead>
    <tbody>
<? foreach (array_keys($result["on_date"]) as $type): ?>
    <?php
        $section = $result["on_date"][$type]["percent"];
        $section_error = $result["on_date"][$type]["error"];
        $section_after = $result["after_date"][$type]["percent"];
        $section_after_error = $result["after_date"][$type]["error"];

        $section_pct = percent_string($section, $section_error);
        $section_after_pct = percent_string($section_after, $section_after_error);
    ?>
    <? if ($section >= 0.70): ?>
        <tr class="success">
    <? elseif ($section >= 0.40): ?>
        <tr class="warning">
    <? else: ?>
        <tr class="danger">
    <? endif ?>
            <td><?= $type ?></td>
            <td><?= $section_pct ?></td>
            <td><?= $section_after_pct ?></td>
        </tr>
<? endforeach ?>
    </tbody>
</table>

<!-- Chart of past semesters -->
<h2>Previous semesters</h2>

<?php
$result = $course->getAllWeeklyAvailability();
?>

<ul class="nav nav-tabs" id="chart-tabs" role="tablist">
    <? foreach (array_keys($result) as $sem): ?>
        <li role="presentation">
            <a href="#<?= $sem ?>-pane" role="tab" data-toggle="tab"><?= $sem ?></a>
        </li>
    <? endforeach ?>
</ul>


<script>

// Setup Bootstrap tabs
$("#chart-tabs a").click(function (e) {
    e.preventDefault();
    $(this).tab("show");
});

// Reflow the chart when tab is selected
$(function() {
    $("a[data-toggle='tab']").on("shown.bs.tab", function (e) {
        $(e.target.hash + " > div").highcharts().reflow();
    });
});

Highcharts.setOptions({
    lang: {
        noData: "No data for the given class"
    }
});

// Select the first tab
$(document).ready(function() {
    $("#chart-tabs > li:first > a").tab("show");
});

/**
 * Returns whether the viewport is small or extra small.
 *
 * @return     {boolean}  True if the screen is small or extra small, else false
 */
function isSmallScreen() {
    return $(".device-sm").is(":visible");
}
</script>

<div class="tab-content" id="chart-tab-panels">
<? foreach ($result as $sem => $sections): ?>

        <?php
            
            $semester = new Semester($dbh, $sem);
            $instruction_week = $semester->getInstructionWeek();
            $last_week = $semester->getNumWeeks();

            $series_arr = [];
            foreach ($sections as $type => $data) {
                $series_arr[] = [
                    "name" => $type,
                    "data" => $data,
                ];
            }
            $series = json_encode($series_arr);
        ?>

        <div id="<?= $sem ?>-pane" role="tabpanel" class="tab-pane">
            <div id="<?= $sem ?>-chart" class="chart"></div>
        </div>

        <script>
            $("#<?= $sem ?>-chart").highcharts({
                chart: {
                    type: "spline"
                },
                title: {
                    text: "<?= $course ?>"
                },
                subtitle: {
                    text: "<?= $sem ?>"
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
                        from: <?= $instruction_week ?>,
                        to: <?= $last_week ?>,
                        color: "rgba(68, 170, 213, 0.2)",
                        label: {
                            text: "Classes in session"
                        }
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
                    },
                    series: {
                        marker: {
                            enabled: false
                        }
                    }
                },
                series: <?= $series ?>
            });
        </script>

<? endforeach ?>
</div>

<div class="device-sm visible-sm-block visible-xs-block"></div>

<? include __DIR__."/../../templates/footer.php" ?>