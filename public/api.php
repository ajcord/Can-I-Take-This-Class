<?php

include "../templates/connect_mysql.php";	//connects to database

$cour = $_GET["courses"];		
$date = $_GET["date"];

$sem = "sp16";
$courses_data = array();

//find way to split string list of classes based upon commas
$course_list = explode(",", $cour);			//takes course string and splits based on comma--returns array of strings
foreach($course_list as $course){		//insdie some database query(statistical algorithm)---maybe get list of crns in datase that match the crn and course ex split CS 225 into department cs and course 225
	//variables subject code & course number, take substring from end of word, subject code start of wrod to 3 from end and course number is 3 from end to actual end 

    $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
    $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

	//list of all avialabity data for all semesters for this class...cs 255...all sections...then availability data
	//2 queries total...build these into json object from there
    
    // Get enrollment data

    $sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count from ".
                "(select * from ".
                    "(select * from availability order by timestamp desc) ".
                "as sorted group by crn, semester) as latest ".
            "inner join (select crn, semester, sectiontype, name from sections ".
                "where subjectcode='$subject_code' and coursenumber=$course_num and semester='$sem') as sections ".
            "using(crn, semester) group by type, status";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Could not get availability data: ".mysql_error());
    }

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