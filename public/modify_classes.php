<?php
session_start();

include "../templates/connect_mysql.php";

$add_course = $_GET["add_course"];
$delete_course = $_GET["delete_course"];

$course = mysql_real_escape_string($_GET["course"]);
$id = $_SESSION["id"];
$sem = "fa15";
$course = str_replace(' ', '', $course);
$subject_code = mysql_real_escape_string(strtoupper(substr($course, 0, strlen($course) - 3)));
$course_num = mysql_real_escape_string(substr($course, strlen($course) - 3));

if (isset($add_course)) {
    //Add the course to the user's wants
    $sql = "select count(crn) as num from sections where subjectcode='".$subject_code."' and coursenumber='".$course_num."' and semester='".$sem."'";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error checking course: ".mysql_error());
    }

    if ($row = mysql_fetch_assoc($retval)) {
        if (intval($row["num"]) == 0) {
            //This course does not exist
            mysql_close($link);
            header("location: my_classes.php?status=add_error");
        }
    }

    $sql = "insert into wants (userid, subjectcode, coursenumber, semester) values (".$id.", '".$subject_code."', ".$course_num.", '".$sem."')";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error adding course: ".mysql_error());
    }

    mysql_close($link);
    header("location: my_classes.php?status=added_course");
} else if (isset($delete_course)) {
    //Add the course to the user's wants
    $sql = "delete from wants where userid=".$id." and subjectcode='".$subject_code."' and coursenumber=".$course_num." and semester='".$sem."'";

    $retval = mysql_query($sql);
    if (!$retval) {
        die("Error deleting course: ".mysql_error());
    }

    if (mysql_affected_rows($retval) == 0) {
        //The course did not exist in the wants table
        mysql_close($link);
        header("location: my_classes.php?status=delete_error");
    }
    mysql_close($link);
    header("location: my_classes.php?status=deleted_course");
}

?>