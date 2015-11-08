<?php

/**
 * Gets a list of registration probabilities.
 *
 * @param    string courses The comma-separated list of courses (e.g. "CS125,CS225")
 * @param    string date The registration date (e.g. "2015-11-02")
 * 
 * @return   array A map of courses & their constituent section types to probabilties
 */

//Find the semester corresponding to the given date
$current_semester_sql = "select * from registrationdates where date <= '$date' ".
                            "order by date desc limit 1";

$current_semester_retval = mysql_query($current_semester_sql)
    or die("Could not get current semester: ".mysql_error());

$current_semester_row = mysql_fetch_assoc($current_semester_retval);

//Calculate the offset into the registration period
try {
    $days_after_registration = date_diff(new DateTime($current_semester_row["date"]),
        new DateTime($date));
} catch (Exception $e) {
    die("{\"error\":\"Unable to parse date: $date\"}");
}

//Find all the semesters prior to the given date
$semesters_sql = "select distinct t1.semester, t1.date from registrationdates as t1 ".
                    "inner join registrationdates as t2 ".
                    "on t1.date < t2.date where t2.date <= '$date' order by date";

$semesters_retval = mysql_query($semesters_sql)
    or die("Could not get semester dates: ".mysql_error());

$courses_data = array();
$course_list = explode(",", $courses);

while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

    $sem = $semester_row["semester"];
    $start_date = $semester_row["date"];
    $adjusted_date = new DateTime($start_date);
    $adjusted_date = date_add($adjusted_date, $days_after_registration);
    $adjusted_date = date_format($adjusted_date, "Y-m-d");

    foreach($course_list as $course) {

        $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
        $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

        foreach(["on_date", "after_date"] as $stat) {

            //Get data for all section types of the class for the week surrounding the registration date
            $enrollment_sql = "";

            if ($stat == "on_date") {
                $enrollment_sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count ".
                                        "from sections inner join availability using(crn, semester) ".
                                        "where subjectcode='$subject_code' and coursenumber='$course_num' ".
                                        "and semester='$sem' and ".
                                        "timestamp<date_add('$adjusted_date', interval 4 day) and ".
                                        "timestamp>date_sub('$adjusted_date', interval 3 day) ".
                                        "group by sectiontype, enrollmentstatus";
            } else {
                $enrollment_sql = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count ".
                                        "from sections inner join availability using(crn, semester) ".
                                        "where subjectcode='$subject_code' and coursenumber='$course_num' ".
                                        "and semester='$sem' and ".
                                        "timestamp>='$adjusted_date' ".
                                        "group by sectiontype, enrollmentstatus";
            }

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

                //Guard against possibly having no available sections
                $available = 0;
                if (isset($num_available_sections[$type])) {
                    $available = $num_available_sections[$type];
                }

                $courses_data[$course][$stat][$type] += $available / $total;

                //Weight old semesters lower by multiplying by 1/2.
                //Except the first semester, to make sure the percent sums to 1.
                if ($sem != "fa15") {
                    $courses_data[$course][$stat][$type] *= 0.5;
                }
            }
        }
    }
}

// For convenience, save the semester of interest
$sem = $current_semester_row["semester"];

?>