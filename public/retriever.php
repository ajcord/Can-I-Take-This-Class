<?php
$sem = $_GET["sem"];
$year = $_GET["year"];
$dept = $_GET["dept"];

$data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/".$year."/".$sem."/".$dept.".xml");
$parsed = new SimpleXMLElement($data);
$course_nums = array();
$course_names = array();
foreach ($parsed->courses->course as $c) {
    // echo $parsed["id"] . " " . $c["id"] . ": " . $c . "\n";
    array_push($course_names, (string)$c);
    array_push($course_nums, $c["id"]);
}
$return_data = array_combine($course_nums, $course_names);
echo json_encode($return_data);
?>