<?php

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");

$term = mysql_real_escape_string($_GET["term"]);
$year = mysql_real_escape_string($_GET["year"]);
$subj = mysql_real_escape_string($_GET["subject"]);
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

// Build the return data
$return_data = array();
$status_arr = array();
foreach ($enrollment_data as $e) {
    $num = $e["num"];
    $status = $e["status"];
    $status_arr[$num][$status]++;
}

foreach ($course_data as $c) {
    $this_course_data = array(
        "num" => $c["num"],
        "name" => $c["name"],
        "status" => $status_arr[$c["num"]],
    );
    array_push($return_data, $this_course_data);
}

echo json_encode($return_data);
?>