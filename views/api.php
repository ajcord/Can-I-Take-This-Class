<?php

require_once __DIR__."/../templates/connect_mysql.php";
require_once __DIR__."/../models/Course.php";
require_once __DIR__."/../models/Predictor.php";

/**
 * Returns an error message.
 */
function handle_error($message) {

    $result = [
        "error" => $message,
    ];
    echo json_encode($result);
    exit;
}

$courses = [];

$smallest_course = [
    "on_date" => [
        "percent" => 1,
        "error" => 0,
    ],
    "after_date" => [
        "percent" => 1,
        "error" => 0,
    ],
];

$course_list = explode(",", $_GET["courses"]);

foreach ($course_list as $course_str) {

    // Parse course into subject code and course number
    if (!preg_match("/^([A-Za-z]{2,4})[ ]?(\d{3})$/", $course_str, $matches)) {
        handle_error("Invalid course");
    }

    $subject_code = $matches[1];
    $course_num = intval($matches[2]);

    // Parse registration date
    $parsed_date = strtotime($_GET["date"]);
    if ($parsed_date === false || $parsed_date < strtotime("2015-04-06")) {
        handle_error("Invalid date");
    }

    $registration_date = new DateTime($_GET["date"]);
    $formatted_date = strftime("%x", $registration_date->getTimestamp());

    // Initialize the models
    $course = new Course($dbh, $subject_code, $course_num);
    $predictor = new Predictor($dbh, $course, $registration_date);

    if (!$course->exists()) {
        handle_error("Course does not exist");
    }

    // Get the predictions
    $overall = $predictor->getOverallLikelihood();
    $itemized = $predictor->getItemizedLikelihood();

    // Flip the column order of the sections to list the section type first
    $sections = [];
    foreach ($itemized["on_date"] as $type => $section) {
        $sections[$type]["on_date"] = $section;
    }
    foreach ($itemized["after_date"] as $type => $section) {
        $sections[$type]["after_date"] = $section;
    }

    $courses[$course_str] = [
        "overall" => $overall,
        "sections" => $sections,
    ];

    // TODO: this logic belongs to the model, not the view.
    if ($overall["on_date"]["percent"] <= $smallest_course["on_date"]["percent"]) {
        $smallest_course = $overall;
    }
}

$result = [
    "overall" => $smallest_course,
    "courses" => $courses,
];

echo json_encode($result);
?>