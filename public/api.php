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

    //Get the list of sections in this class
    $section_list_sql = "select crn, sectiontype as type from sections ".
                            "where subjectcode='$subject_code' and ".
                            "coursenumber=$course_num and semester='$sem'";

    $section_list_retval = mysql_query($section_list_sql)
        or die("Could not get availability data: ".mysql_error());

    $this_class = array();
    while ($section_row = mysql_fetch_assoc($section_list_retval)) {

        $crn = $section_row["crn"];
        $type = $section_row["type"];

        //Get the most recent data for this section
        $enrollment_sql = "select enrollmentstatus as status from ".
                                "(select * from availability where crn=$crn and semester='$sem' order by timestamp desc) ".
                            "as sorted group by crn, semester limit 1";

        $enrollment_retval = mysql_query($enrollment_sql)
            or die("Could not get availability data: ".mysql_error());

        $enrollment_row = mysql_fetch_assoc($enrollment_retval);
        $status = $enrollment_row["status"];
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

        $this_class[$type][$status_str] += 1;
    }

    $courses_data[$course] = $this_class;
}

echo json_encode($courses_data);

mysql_close($link);

?>