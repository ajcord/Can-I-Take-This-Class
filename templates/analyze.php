<?php

/**
 * Gets a list of all semesters before the given date.
 *
 * @param      string  $date   The date to check in the form YYYY-MM-DD
 *
 * @return     mixed           The query result
 */
function get_semesters_before_date($date) {

    $semesters_sql = "select distinct t1.semester, t1.date from registrationdates as t1 ".
                        "inner join registrationdates as t2 ".
                        "on t1.date < t2.date where t2.date <= '$date' order by date";

    $semesters_retval = mysql_query($semesters_sql)
        or die("Could not get semester dates: ".mysql_error());

    return $semesters_retval;
}

/**
 * Gets the number of days into the registration period of the given date.
 *
 * @param      string  $date   The date to calculate
 *
 * @return     DateInterval     The offset from the beginning of the previous
 *                              registration period
 */
function get_offset_into_registration($date) {

    //Find the semester corresponding to the given date
    $current_semester_sql = "select * from registrationdates where date <= '$date' ".
                                "order by date desc limit 1";

    $current_semester_retval = mysql_query($current_semester_sql)
        or die("Could not get current semester: ".mysql_error());

    $current_semester_row = mysql_fetch_assoc($current_semester_retval);

    //Calculate the offset into the registration period
    return date_diff(new DateTime($current_semester_row["date"]), new DateTime($date));
}

/**
 * Adjusts a date by the given offset.
 *
 * @param      string  $start_date  The start date
 * @param      DateInterval  $offset      The number of days to add
 *
 * @return     string       The offset date in the form YYYY-MM-DD
 */
function adjust_date($start_date, $offset) {

    $adjusted_date = date_add(new DateTime($start_date), $offset);
    return date_format($adjusted_date, "Y-m-d");
}

/**
 * Splits a course string into its subject code and course number.
 *
 * @param      string  $course  The course string
 *
 * @return     array    An array containing the subject and number
 */
function split_course($course) {

    //Check if the number is present or not
    if (is_int(substr($course, strlen($course) - 3))) {

        $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
        $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

    } else {

        //Remove non-alpha characters from subject and assume number is 0
        preg_replace("/[^A-Za-z]/", "", $course);
        $subjectcode = mysql_real_escape_string($course);
        $course_num = NULL;

    }

    return ["subject" => $subject_code, "number" => $course_num];
}

/**
 * Queries enrollment data for the given parameters.
 *
 * @param      string  $sem            The semester to query
 * @param      string  $start_date     The start date of the registration period
 * @param      string  $adjusted_date  The date to request, or NULL if not applicable
 * @param      string  $stat           The statistic to request:
 *                                          "on_date" for data surrounding the date (default)
 *                                          "after_date" for data following the date
 *                                          "everything" for everything
 * @param      string  $subject_code   The subject to request, or NULL for all subjects
 * @param      int     $course_num     The course number to request, or NULL for all courses in the subject
 *
 * @return     mixed                   The query result
 */
function query_semester($sem, $start_date, $adjusted_date = NULL, $stat = "on_date",
                        $subject_code = NULL, $course_num = NULL) {

    $enrollment_sql = "select ";

    if ($stat == "everything") {
        $enrollment_sql .= "floor(datediff(timestamp, '$start_date')/7) as week, ";
    }

    $enrollment_sql .= "sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count ".
                        "from sections inner join availability using(crn, semester) ".
                        "where semester='$sem' ";

    if (!is_null($subject_code)) {
        $enrollment_sql .= "and subjectcode='$subject_code' ";
        
        if (!is_null($course_num)) {
            $enrollment_sql .= "and coursenumber='$course_num' ";
        }
    }

    switch($stat) {
        case "on_date":
            $enrollment_sql .= "and timestamp<date_add('$adjusted_date', interval 4 day) ".
                               "and timestamp>date_sub('$adjusted_date', interval 3 day) ";
           break;
       case "after_date":
            $enrollment_sql .= "and timestamp>='$adjusted_date' ";
            break;
        case "everything":
            $enrollment_sql .= "and timestamp>='$start_date' ";
            break;
    }

    $enrollment_sql .= "group by ";

    if (is_null($adjusted_date)) {
        $enrollment_sql .= "week, ";
    }

    $enrollment_sql .= "sectiontype, enrollmentstatus order by ";

    if ($stat == "everything") {
        $enrollment_sql .= "week ";
    }

    $enrollment_sql .= "type, status";

    $enrollment_retval = mysql_query($enrollment_sql)
        or die("Could not get availability data: ".mysql_error());

    return $enrollment_retval;
}

?>