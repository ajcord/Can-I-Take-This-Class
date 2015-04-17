<?php

//Connect to MySQL
include "../templates/connect_mysql.php";	//connects to database



$cour = $_GET["courses"];		
$date = $_GET["date"];


$sem = "fa15";
$courses_data = array();

//find way to split string list of classes based upon commas
$course_list = explode(",", $cour);			//takes course string and splits based on comma--returns array of strings
foreach($course_list as $course){		//insdie some database query(statistical algorithm)---maybe get list of crns in datase that match the crn and course ex split CS 225 into department cs and course 225
	//variables subject code & course number, take substring from end of word, subject code start of wrod to 3 from end and course number is 3 from end to actual end 

$subject_code = substr ($course,0,strlen($course)-3);
$course_num = substr ($course,strlen($course)-3); 
echo $subject_code . " " . $course_num. "\n";			//

	//list of all avialabity data for all semesters for this class...cs 255...all sections...then availability data
	//2 queries total...build these into json object from there
    
    // Get enrollment data

// select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus)
// from (select * from (select * from availability order by timestamp desc)
// as sorted group by crn, semester) as latest inner join (select crn, semester,
// sectiontype, name from sections where subjectcode="CS" and semester="fa15" and
//coursenumber=225) as sections using(crn, semester) group by type, status;

    $sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count from ".
                "(select * from ".
                    "(select * from availability order by timestamp desc) ".
                "as sorted group by crn, semester) as latest ".
            "inner join (select crn, semester, sectiontype, name from sections ".
                "where subjectcode=\"".$subject_code."\" and coursenumber=\"".$course_num."\" and semester=\"".$sem."\") as sections ".
            "using(crn, semester) group by type, status";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Could not get availability data: ".mysql_error());
    }

    $this_class = array();
    while($row = mysql_fetch_assoc($retval)) {
        // var_dump($row);
        $type = $row["type"];
        $status = $row["status"];
        $count = $row["count"];
        if (!isset($this_class, $type)) {
            $type_arr = array();
            $this_class[$type] = $type_arr;
            // array_push($this_class, $type_arr);
        }

        //Insert the status into the type array
        $status_str = "";
        switch ($status) {
            case "0":
                $status_str = "CLOSED";
                break;
            case "1":
                $status_str = "OPEN";
                break;
            case "2":
                $status_str = "RESTRICTED";
                break;
            case "3":
                $status_str = "CROSSLIST";
                break;
            default:
                $status_str = "UNKNOWN";
                break;
        }
        $this_class[$type][$status_str] = intval($count);
    }

    $courses_data[$course] = $this_course;
}

// // Get course data
// $sql = "select coursenumber as num, name from sections where subjectcode=\"".$subj."\" and semester=\"".$sem."\" group by num";

// $retval = mysql_query($sql);
// if (!$retval) {
//     die("Could not get course data: ".mysql_error());
// }

// $course_data = array();
// while($row = mysql_fetch_assoc($retval)) {
//     array_push($course_data, $row);
// }





// $return_data = array();
// $status_arr = array();
// foreach ($enrollment_data as $e) {
//     $num = $e["num"];
//     $status = $e["status"];
//     $status_arr[$num][$status]++;
// }

// foreach ($course_data as $c) {
//     $this_course_data = array(
//         "num" => $c["num"],
//         "name" => $c["name"],
//         "status" => $status_arr[$c["num"]],
//     );
//     array_push($return_data, $this_course_data);
// }

echo json_encode($courses_data);
?>