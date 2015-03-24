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
$course_data = array();
foreach ($parsed->courses->course as $c) {
    //This monstrosity gets the count of CRNs for each enrollment status for the specified class. It only gets the most recent data for each CRN.
    $sql = "select latest.enrollmentstatus as status, count(latest.enrollmentstatus) as num from ".
                "(select * from (select * from availability order by timestamp desc) as sorted group by crn, semester) as latest ".
                "inner join (select crn, semester from sections where subjectcode=\"".$dept."\" and coursenumber=".$c["id"].") as sections ".
                "using(crn, semester) group by status";
    // // The below query is replaced with the above for efficiency
    // $sql = "select availability.enrollmentstatus as status, count(availability.enrollmentstatus) as num from ".
    //             "(select availability.crn, max(timestamp) timestamp, enrollmentstatus, semester from ".
    //                 "availability inner join ".
    //                     "(select crn from sections where subjectcode=\"".$dept."\" and coursenumber=".$c["id"].") ".
    //                 "as sections using(crn) group by availability.crn order by max(timestamp) desc) ".
    //             "as t inner join availability using(crn, semester, timestamp) group by status";
    
    $retval = mysql_query($sql);
    if (!$retval) {
        die("Could not get availability data: ".mysql_error());
    }

    $enrollment_data = array();
    while($row = mysql_fetch_assoc($retval)) {
        array_push($enrollment_data, $row);
    }

    $this_course_data = array(
        "name" => (string)$c,
        "availability" => $enrollment_data,
    );
    array_push($course_nums, $c["id"]);
    array_push($course_data, $this_course_data);
}

mysql_close($link);

$return_data = array_combine($course_nums, $course_data);
echo json_encode($return_data);
?>