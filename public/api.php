<?php

include "../templates/connect_mysql.php";

$cour = $_GET["courses"];		
$date = mysql_real_escape_string($_GET["date"]);

//Find the semester corresponding to the given date
$current_semester_sql = "select * from registrationdates where t2.date <= '$date' ".
                            "order by date desc limit 1";

$current_semester_retval = mysql_query($semesters_sql)
    or die("Could not get current semester: ".mysql_error());

$current_semester_row = mysql_fetch_assoc($current_semester_retval);

//Calculate the offset into the registration period
$days_after_registration = date_diff(new DateTime($date),
    new DateTime($current_semester_row["date"]));

//Find all the semesters prior to the given date
$semesters_sql = "select distinct t1.semester, t1.date from registrationdates as t1 ".
                    "inner join registrationdates as t2 ".
                    "on t1.date < t2.date where t2.date <= '$date' order by date";

$semesters_retval = mysql_query($semesters_sql)
    or die("Could not get semester dates: ".mysql_error());

$courses_data = array();
$course_list = explode(",", $cour);

while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

    $sem = $semester_row["semester"];
    $start_date = $semester_row["date"];
    $adjusted_date = new DateTime($start_date);
    $adjusted_date = $adjusted_date.add($days_after_registration);
    $adjusted_date = $adjusted_date.format("Y-m-d");

    foreach($course_list as $course) {

        $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
        $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

        //Get the most recent data for this class
        $enrollment_sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count ".
                                "from sections inner join availability using(crn, semester) ".
                                "where subjectcode='$subject_code' and coursenumber='$course_number' ".
                                "and semester='$sem' and ".
                                "timestamp<date_add('$adjusted_date', interval 4 day) and ".
                                "timestamp>date_sub('$adjusted_date', interval 3 day) ".
                                "group by sectiontype, enrollmentstatus";

        $enrollment_retval = mysql_query($enrollment_sql)
            or die("Could not get availability data: ".mysql_error());

        $num_available_sections = array();
        $total_sections = array();

        while ($enrollment_row = mysql_fetch_assoc($enrollment_retval)) {

            $type = $enrollment_row["type"];
            $status = $enrollment_row["status"];
            $count = $enrollment_row["count"];

            $total_sections[$type] += $count;
            if ($status != 0) {
                $num_available_sections[$type] += $count;
            }
        }

        foreach ($total_sections as $type => $total) {

            //Weight old semesters lower by multiplying by 1/2.
            //Except the first semester, to make sure the percent sums to 1.
            $factor = 0.5;
            if ($sem == "fa15") {
                $factor = 1;
            }

            //Guard against possibly having no available sections
            $available = 0;
            if (isset($num_available_sections[$type])) {
                $available = $num_available_sections[$type];
            }

            $courses_data[$course][$type] *= $factor;
            $courses_data[$course][$type] += $factor * $available / $total;
        }
    }
}

echo json_encode($courses_data);

mysql_close($link);

?>