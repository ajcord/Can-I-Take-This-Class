<?php

include "../templates/connect_mysql.php";

$cour = $_GET["courses"];		
$date = $_GET["date"];

$sem = "sp16";
$courses_data = array();

$course_list = explode(",", $cour);
foreach($course_list as $course){

    $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
    $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));
    
    // Get enrollment data

    $sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count from ".
                "(select * from ".
                    "(select * from availability where semester='$sem' order by timestamp desc) ".
                "as sorted group by crn, semester) as latest ".
            "inner join (select crn, semester, sectiontype, name from sections ".
                "where subjectcode='$subject_code' and coursenumber=$course_num and semester='$sem') as sections ".
            "using(crn, semester) group by type, status";

    $retval = mysql_query($sql)
        or die("Could not get availability data: ".mysql_error());

    $this_class = array();
    while ($row = mysql_fetch_assoc($retval)) {
        $type = $row["type"];
        $status = $row["status"];
        $count = $row["count"];
        if (!isset($this_class, $type)) {
            $type_arr = array();
            $this_class[$type] = $type_arr;
        }

        //Insert the status into the type array
        $status_str = "";
        switch ($status) {
            case "0":
                $status_str = "Closed";
                break;
            case "1":
                $status_str = "Open";
                break;
            case "2":
                $status_str = "Open (Restricted)";
                break;
            case "3":
                $status_str = "CrossListOpen";
                break;
            case "4":
                $status_str = "CrossListOpen (Restricted)";
                break;
            default:
                $status_str = "Unknown";
                break;
        }
        $this_class[$type][$status_str] = intval($count);
    }

    $courses_data[$course] = $this_class;
}

echo json_encode($courses_data);
?>