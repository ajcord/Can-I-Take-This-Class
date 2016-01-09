<?php

// TODO: write a router to do this better
require_once __DIR__."/../../templates/connect_mysql.php";
require_once __DIR__."/../../controllers/PredictionController.php";
require_once __DIR__."/../../models/Course.php";

$course = new Course($dbh, $_GET["subjectcode"], intval($_GET["coursenumber"]));
$registration_date = new DateTime($_GET["registrationdate"]);

$controller = new PredictionController($course, $registration_date);
$result = $controller->getOverallLikelihood();

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

<? include __DIR__."/../../templates/header.php" ?>

<div class="jumbotron text-center">

<? if ($overall >= 0.90): ?>
    <h1>Yes (<?= $overall_pct ?>)</h1>
<? elseif ($overall >= 0.60): ?>
    <h1>Probably (<?= $overall_pct ?>)</h1>
<? elseif ($overall >= 0.40): ?>
    <h1>Maybe (<?= $overall_pct ?>)</h1>
<? elseif ($overall >= 0.10): ?>
    <h1>Probably not (<?= $overall_pct ?>)</h1>
<? else: ?>
    <h1>No (<?= $overall_pct ?>)</h1>
<? endif ?>

<? if ($overall < 0.60): ?>
    <? if ($overall_after > ($overall + 0.10)): ?>
        <p>
            But it has a better chance of opening up later
            (<?= $overall_after_pct ?>). Keep trying!
        </p>
    <? else: ?>
        <p>
            It probably won't open up later during registration
            (<?= $overall_after_pct ?>).
        </p>
    <? endif ?>
<? endif ?>

</div>

<? include __DIR__."/../../templates/footer.php" ?>