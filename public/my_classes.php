<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("location: login.php?status=not_logged_in&next=my_classes.php");
}
include "../templates/header.php";
?>

<div class="container">
    <div class="jumbotron">
        <h1>My classes</h1>
        <br><br>
<?php include "../templates/status_alert.php" ?>
        <form class="form-horizontal" action="modify_classes.php" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <h2>Add a class</h2>
                    <div class="input-group col-md-12">
                        <input id="course-field" name="course" type="text" class="form-control input-lg" placeholder="e.g. CS 225" />
                        <span class="input-group-btn">
                            <button id="add-course-button" class="btn btn-info btn-lg" type="submit" name="add_course">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <h2>My classes</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td class="col-md-1"></td>
                        <td class="col-md-7">Course</td>
                        <td class="col-md-4">Status</td>
                    </tr>
                </thead>
                <tbody id="courses-table">
<?php
include "../templates/connect_mysql.php";

//Get the user id
$id = $_SESSION["id"];

//Determine which term and year to query
$sem_sql = "select semester from semesters where ".
                "date_add(now(), interval 7 day) >= registrationdate ".
                "order by registrationdate desc limit 1";
$sem_retval = mysql_query($sem_sql);
$sem = mysql_fetch_assoc($sem_retval)["semester"];

//Get a list of courses the user wants
$wanted_course_sql = "select subjectcode, coursenumber from wants where userid=$id and semester='$sem'";

$wanted_course_retval = mysql_query($wanted_course_sql)
    or die("Could not get wants: ".mysql_error());

$course_data = array();
while($wanted_course_row = mysql_fetch_assoc($wanted_course_retval)) {
    $subject_code = $wanted_course_row["subjectcode"];
    $course_num = $wanted_course_row["coursenumber"];

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

        $this_class[$type][$status] += 1;
    }

    //Figure out the color of the class row
    $are_any_completely_full = false;
    $are_any_only_restricted = false;
    foreach ($this_class as $type => $data) {
        //Get the total number of sections of this type
        $total = 0;
        $open = 0;
        $restricted = 0;
        $closed = 0;
        foreach ($data as $status => $count) {
            $total += $count;
            switch ($status) {
                case "0":
                    $closed += $count;
                    break;
                case "1":
                case "3":
                    $open += $count;
                    break;
                case "2":
                case "4":
                    $restricted += $count;
                    break;
            }
        }
        if ($open == 0) {
            if ($restricted == 0) {
                $are_any_completely_full = true;
            } else {
                $are_any_only_restricted = true;
            }
        }
    }

    if ($are_any_completely_full) {
        echo "<tr class='danger'>";
    } else if ($are_any_only_restricted) {
        echo "<tr class='warning'>";
    } else {
        echo "<tr class='success'>";
    }
    
    //Append the remove link
    $course_num_padded = str_pad($course_num, 3, '0', STR_PAD_LEFT);
    echo "<td><a href='modify_classes.php?course=$subject_code$course_num_padded&delete_course=1' class='btn btn-danger btn-xs'>Delete</a></td>";
    //Append the course name
    echo "<td>$subject_code $course_num</td>";

    if ($are_any_completely_full) {
        echo "<td><span class='label label-danger'>Hard</span></td>";
    } else if ($are_any_only_restricted) {
        echo "<td><span class='label label-warning'>Medium</span></td>";
    } else {
        echo "<td><span class='label label-success'>Easy</span></td>";
    }

    echo "</tr>";

    foreach ($this_class as $type => $data) {
        //Get the total number of sections of this type
        $total = 0;
        $open = 0;
        $restricted = 0;
        $closed = 0;
        foreach ($data as $status => $count) {
            $total += $count;
            switch ($status) {
                case "0":
                    $closed += $count;
                    break;
                case "1":
                case "3":
                    $open += $count;
                    break;
                case "2":
                case "4":
                    $restricted += $count;
                    break;
            }
        }

        $open_width = $open/$total*100;
        $restricted_width = $restricted/$total*100;
        $closed_width = $closed/$total*100;


        //Append the section type row
        echo "<tr><td></td><td>$type</td><td>";
        echo "<div class='progress'>";
        echo "<div class='progress-bar progress-bar-success' style='width:$open_width%;'></div>";
        echo "<div class='progress-bar progress-bar-warning' style='width:$restricted_width%;'></div>";
        echo "<div class='progress-bar progress-bar-danger' style='width:$closed_width%;'></div>";
        echo "</div>";
        echo "</td></tr>";
    }
}

mysql_close($link);
?>

                </tbody>
            </table>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>