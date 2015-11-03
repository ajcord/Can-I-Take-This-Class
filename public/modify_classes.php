<?php
session_start();

include "../templates/connect_mysql.php";

$add_course = $_GET["add_course"];
$delete_course = $_GET["delete_course"];

$course = mysql_real_escape_string($_GET["course"]);
$id = $_SESSION["id"];
$sem = "sp16";
$course = str_replace(' ', '', $course);
$subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
$course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

if (isset($add_course)) {

    //Add the course to the user's wants
    $check_sql = "select count(crn) as num from sections ".
                    "where subjectcode='$subject_code' and ".
                    "coursenumber='$course_num' and semester='$sem'";
    $check_retval = mysql_query($check_sql)
        or die("Error checking course: ".mysql_error());

    if ($row = mysql_fetch_assoc($check_retval)) {
        if (intval($row["num"]) == 0) {
            //This course does not exist
            mysql_close($link);
            header("location: my_classes.php?status=add_error");
        }
    }

    $add_sql = "insert into wants (userid, subjectcode, coursenumber, semester) ".
                    "values ($id, '$subject_code', '$course_num', '$sem')";
    $add_retval = mysql_query($add_sql)
        or die("Error adding course: ".mysql_error());

    mysql_close($link);
    header("location: my_classes.php?status=added_course");

} else if (isset($delete_course)) {

    //Delete the course from the user's wants
    $delete_sql = "delete from wants where userid=$id and ".
                        "subjectcode='$subject_code' and ".
                        "coursenumber='$course_num' and semester='$sem'";
    $delete_retval = mysql_query($delete_sql)
        or die("Error deleting course: ".mysql_error());

    if (mysql_affected_rows() == 0) {
        //The course did not exist in the wants table
        mysql_close($link);
        header("location: my_classes.php?status=delete_error");
    } else {
        mysql_close($link);
        header("location: my_classes.php?status=deleted_course");
    }
}

?>