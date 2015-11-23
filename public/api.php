<?php

include "../templates/connect_mysql.php";
include "../templates/analyze.php";

$courses = mysql_real_escape_string($_GET["courses"]);
$courses_data = array();
$course_list = explode(",", $courses);
$date = mysql_real_escape_string(urldecode($_GET["date"]));

try {
    $days_after_registration = get_offset_into_registration($date);
} catch (Exception $e) {
    return ["error" => "Unable to parse date: $date"];
}

$n = array();

foreach($course_list as $course) {

    $split = split_course($course);
    $subject_code = $split["subject"];
    $course_num = $split["number"];

    $semesters_retval = get_semesters_before_date($date, $subject_code, $course_num);

    while ($semester_row = mysql_fetch_assoc($semesters_retval)) {

        $sem = $semester_row["semester"];
        $start_date = $semester_row["date"];
        $adjusted_date = adjust_date($start_date, $days_after_registration);

        foreach(["on_date", "after_date"] as $stat) {

            $enrollment_retval = query_semester($sem, $start_date, $adjusted_date,
                                    $stat, $subject_code, $course_num, false);

            $num_available_sections = array();
            $total_sections = array();

            while ($enrollment_row = mysql_fetch_assoc($enrollment_retval)) {

                $type = $enrollment_row["type"];
                $status = $enrollment_row["status"];
                $count = $enrollment_row["count"];

                $total_sections[$type] += $count;
                $n[$course][$type][$stat] += $count;

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

                $courses_data[$course][$type][$stat]["percent"] += $available / $total;

                //Weight old semesters lower by multiplying by 1/2.
                //Except the first semester, to make sure the percent sums to 1.
                //
                //TODO: use counter instead. Does not take into account
                //new classes.
                if ($sem != "fa15") {
                    $courses_data[$course][$type][$stat]["percent"] *= 0.5;
                    $n[$course][$type][$stat] *= 0.5;
                }
            }

            //Calculate the standard error for each stat
            foreach ($total_sections as $type => $total) {

                $p = $courses_data[$course][$type][$stat]["percent"];
                $n_weighted = $n[$course][$type][$stat];
                $courses_data[$course][$type][$stat]["error"] = sqrt($p*(1-$p)/$n_weighted);
            }
        }
    }
}

echo json_encode($courses_data);

mysql_close($link);

?>