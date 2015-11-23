<?php

/**
 * Gets a list of all semesters before the given date.
 *
 * @param      string  $date   The date to check in the form YYYY-MM-DD
 *
 * @return     mixed           The query result
 */
function get_semesters_before_date($date) {

    $semesters_sql = "select distinct t1.semester, t1.registrationdate as date from semesters as t1 ".
                        "inner join semesters as t2 ".
                        "on t1.registrationdate < t2.registrationdate ".
                        "where t2.registrationdate <= '$date' order by registrationdate";

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
    $current_semester_sql = "select semester, registrationdate as date from semesters ".
                                "where registrationdate <= '$date' ".
                                "order by registrationdate desc limit 1";

    $current_semester_retval = mysql_query($current_semester_sql)
        or die("Could not get current semester: ".mysql_error());

    $current_semester_row = mysql_fetch_assoc($current_semester_retval);

    //Calculate the offset into the registration period
    return date_diff(new DateTime($current_semester_row["date"]), new DateTime($date));
}

/**
 * Gets the date and week number of the last data for the given semester.
 *
 * @param      string  $sem    The semester to check
 * @param      string  $date   The beginning of the registration period
 *                             in YYYY-MM-DD form
 *
 * @return     array           An array of the form ["week" => lastWeekNum, "date" => lastDate]
 */
function get_last_week($sem, $date) {

    //Fetch an example CRN to make the availability query much faster
    $crn_sql = "select crn from sections where semester='$sem' limit 1";

    $crn_retval = mysql_query($crn_sql)
        or die("Could not get a CRN: ".mysql_error());

    $crn = mysql_fetch_assoc($crn_retval)["crn"];

    $last_week_sql = "select floor(datediff(max(timestamp), '$date')/7) as week ".
                        "from availability where semester='$sem' and crn='$crn'";

    $last_week_retval = mysql_query($last_week_sql)
        or die("Could not get last week: ".mysql_error());

    return mysql_fetch_assoc($last_week_retval);
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
    if (ctype_digit(substr($course, strlen($course) - 3))) {

        $subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
        $course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

    } else if (strlen($course) > 0) {

        //Remove non-alpha characters from subject and assume number is 0
        preg_replace("/[^A-Za-z]/", "", $course);
        $subject_code = mysql_real_escape_string(strtoupper($course));
        $course_num = NULL;

    } else {

        $subject_code = NULL;
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
 * @param      bool    $only_open      Whether to return all statuses or just the number of open ones
 *
 * @return     mixed                   The query result
 */
function query_semester($sem, $start_date, $adjusted_date = NULL, $stat = "on_date",
                        $subject_code = NULL, $course_num = NULL, $only_open = false) {

    $enrollment_sql = "select ";

    if ($stat == "everything") {
        $enrollment_sql .= "floor(datediff(timestamp, '$start_date')/7) as week, ";
    }

    $enrollment_sql .= "sectiontype as type, ";

    if (!$only_open) {
        $enrollment_sql .= "enrollmentstatus as status, ";
    }

    $enrollment_sql .= "count(enrollmentstatus) as count ".
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

    if ($only_open) {
        $enrollment_sql .= "and enrollmentstatus<>0 ";
    }

    $enrollment_sql .= "group by ";

    if (is_null($adjusted_date)) {
        $enrollment_sql .= "week, ";
    }

    $enrollment_sql .= "sectiontype";

    if (!$only_open) {
        $enrollment_sql .= ", enrollmentstatus ";
    }

    $enrollment_sql .= " order by ";

    if ($stat == "everything") {
        $enrollment_sql .= "week, ";
    }

    $enrollment_sql .= "type";

    if (!$only_open) {
        $enrollment_sql .= ", status";
    }

    $enrollment_retval = mysql_query($enrollment_sql)
        or die("Could not get availability data: ".mysql_error());

    return $enrollment_retval;
}

?>