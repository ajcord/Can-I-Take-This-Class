<?php

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");

$term = $_GET["term"];
$year = $_GET["year"];
$subj = $_GET["subject"];
$sem = substr($term, 0, 2) . substr($year, 2, 2);

// Get course data
$sql = "select coursenumber as num, name from sections where subjectcode=\"".$subj."\" and semester=\"".$sem."\" group by num";

$retval = mysql_query($sql);
if (!$retval) {
    die("Could not get course data: ".mysql_error());
}

$course_data = array();
while($row = mysql_fetch_assoc($retval)) {
    array_push($course_data, $row);
}

// Get enrollment data
$sql = "select coursenumber as num, enrollmentstatus as status from ".
            "(select * from ".
                "(select * from availability order by timestamp desc) ".
            "as sorted group by crn, semester) as latest ".
        "inner join (select crn, semester, coursenumber, name from sections ".
            "where subjectcode=\"".$subj."\" and semester=\"".$sem."\") as sections ".
        "using(crn, semester) order by num";

$retval = mysql_query($sql);
if (!$retval) {
    die("Could not get availability data: ".mysql_error());
}

$enrollment_data = array();
while($row = mysql_fetch_assoc($retval)) {
    array_push($enrollment_data, $row);
}

mysql_close($link);

// $return_data = array(
//     "names" => $course_data,
//     "sections" => $enrollment_data,
// );
var_dump($course_data);
var_dump($enrollment_data);
// $return_data = array();

// $status_arr = array();
// foreach ($enrollment_data as $e) {
    
// }

// foreach ($course_data as $c) {
//     $this_course_data = array(
//         "num" => $c->num,
//         "name" => $c->name,
//         "status" => $this_status_arr,
//     );
//     array_push($return_data, $this_course_data);
// }

// echo json_encode($return_data);



// $data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/".$year."/".$sem."/".$dept.".xml");
// $parsed = new SimpleXMLElement($data);
// $course_nums = array();
// $course_data = array();
// foreach ($parsed->courses->course as $c) {
//     //This monstrosity gets the count of CRNs for each enrollment status for the specified class. It only gets the most recent data for each CRN.
//     // $sql = "select latest.enrollmentstatus as status, count(latest.enrollmentstatus) as num from ".
//     //             "(select * from (select * from availability order by timestamp desc) as sorted group by crn, semester) as latest ".
//     //             "inner join (select crn, semester from sections where subjectcode=\"".$dept."\" and coursenumber=".$c["id"].") as sections ".
//     //             "using(crn, semester) group by status";
//     // The below query is replaced with the above for efficiency
//     $sql = "select availability.enrollmentstatus as status, count(availability.enrollmentstatus) as num from ".
//                 "(select availability.crn, max(timestamp) timestamp, enrollmentstatus, semester from ".
//                     "availability inner join ".
//                         "(select crn from sections where subjectcode=\"".$dept."\" and coursenumber=".$c["id"].") ".
//                     "as sections using(crn) group by availability.crn order by max(timestamp) desc) ".
//                 "as t inner join availability using(crn, semester, timestamp) group by status";
    
//     $retval = mysql_query($sql);
//     if (!$retval) {
//         die("Could not get availability data: ".mysql_error());
//     }

//     $enrollment_data = array();
//     while($row = mysql_fetch_assoc($retval)) {
//         array_push($enrollment_data, $row);
//     }

//     $this_course_data = array(
//         "name" => (string)$c,
//         "availability" => $enrollment_data,
//     );
//     array_push($course_nums, $c["id"]);
//     array_push($course_data, $this_course_data);
// }

// mysql_close($link);

// $return_data = array_combine($course_nums, $course_data);
// echo json_encode($return_data);
?>