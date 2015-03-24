<?php

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");

$sem = $_GET["sem"];
$year = $_GET["year"];
$dept = $_GET["dept"];

$data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/".$year."/".$sem."/".$dept.".xml");
$parsed = new SimpleXMLElement($data);
$course_nums = array();
$course_names = array();
foreach ($parsed->courses->course as $c) {
    // echo $parsed["id"] . " " . $c["id"] . ": " . $c . "\n";
    array_push($course_nums, $c["id"]);
    array_push($course_names, (string)$c);

    //This monstrosity gets the count of CRNs for each enrollment status for the specified class. It only gets the most recent data for each CRN.
    // $sql = "select availability.enrollmentstatus, count(availability.enrollmentstatus) from ".
    //             "(select availability.crn, max(timestamp) timestamp, enrollmentstatus, semester from ".
    //                 "availability inner join ".
    //                     "(select crn from sections where subjectcode=\"".$dept."\" and coursenumber=".$c["id"].") ".
    //                 "as sections using(crn) group by availability.crn order by max(timestamp) desc) ".
    //             "as t inner join availability using(crn, semester, timestamp) group by enrollmentstatus";
    $sql = "select availability.enrollmentstatus, count(availability.enrollmentstatus) from (select availability.crn, max(timestamp) timestamp, enrollmentstatus, semester from availability inner join (select crn from sections where subjectcode=\"CS\" and coursenumber=125) as sections using(crn) group by availability.crn order by max(timestamp) desc) as t inner join availability using(crn, semester, timestamp) group by enrollmentstatus";
    $retval = mysql_query($sql);
    if (!$retval) {
        die("Could not get availability data: ".mysql_error());
    }
    // var_dump($retval);
    echo $c." ".$c["id"].":\n";
    $table = mysqli_fetch_all($retval);
    var_dump($table);
}

mysql_close($link);

$return_data = array_combine($course_nums, $course_names);
echo json_encode($return_data);
?>