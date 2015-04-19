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
        <form class="form-horizontal" action="modify_classes.php" method="POST">
            <label for="course-field">Add a class:</label>
            <input id="course-field" name="course" type="text" class="form-control input-lg" placeholder="CS 225" />
            <span class="input-group-btn">
                <button id="add-course-button" class="btn btn-info btn-lg" type="submit" name="add-course-btn">
                    <span class="glyphicon glyphicon-plus"></span>
                </button>
            </span>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>Delete</td>
                        <td>Course</td>
                        <td>Status</td>
                    </tr>
                </thead>
                <tbody id="courses-table">
<?php
include "../templates/connect_mysql.php";

//Get the user id
$id = $_SESSION["id"];

//Get a list of courses the user wants
$sem = "fa15";
$sql = "select subjectcode, coursenumber from wants where userid=".$id." and semester='".$sem."'";

$retval = mysql_query($sql);
if (!$retval) {
    die("Could not get wants: ".mysql_error());
}

$course_data = array();
while($row = mysql_fetch_assoc($retval)) {
    $subject_code = $row["subjectcode"];
    $course_num = $row["coursenumber"];

    //Append the remove link
    echo "<tr><td><a href='#'>X</a></td>";
    //Append the course name
    echo "<td>".$subject_code." ".$course_num."</td><td></td></tr>";

    //Get the most recent data for this class
    $sql2 = "select sectiontype as type, enrollmentstatus as status, count(enrollmentstatus) as count from ".
                "(select * from ".
                    "(select * from availability order by timestamp desc) ".
                "as sorted group by crn, semester) as latest ".
            "inner join (select crn, semester, sectiontype, name from sections ".
                "where subjectcode=\"".$subject_code."\" and coursenumber=\"".$course_num."\" and semester=\"".$sem."\") as sections ".
            "using(crn, semester) group by type, status";

    $retval2 = mysql_query($sql2);
    if (!$retval2) {
        die("Could not get availability data: ".mysql_error());
    }

    $this_class = array();
    while ($row2 = mysql_fetch_assoc($retval2)) {
        // var_dump($row);
        $type = $row2["type"];
        $status = $row2["status"];
        $count = $row2["count"];
        if (!isset($this_class, $type)) {
            $type_arr = array();
            $this_class[$type] = $type_arr;
        }

        //Insert the status into the type array
        // $status_str = "";
        // switch ($status) {
        //     case "0":
        //         $status_str = "Closed";
        //         break;
        //     case "1":
        //         $status_str = "Open";
        //         break;
        //     case "2":
        //         $status_str = "Open (Restricted)";
        //         break;
        //     case "3":
        //         $status_str = "CrossListOpen";
        //         break;
        //     default:
        //         $status_str = "Unknown";
        //         break;
        // }
        $this_class[$type][$status] = intval($count);
    }

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
                    $restricted += $count;
                    break;
            }
        }

        $open_width = $open/$total*100;
        $restricted_width = $restricted/$total*100;
        $closed_width = $closed/$total*100;


        //Append the section type row
        echo "<tr><td></td><td>".$type."</td><td>";
        echo "<div class='progress'>";
        echo "<div class='progress-bar progress-bar-success' style='width:".$open_width."%;'></div>";
        echo "<div class='progress-bar progress-bar-warning' style='width:".$restricted_width."%;'></div>";
        echo "<div class='progress-bar progress-bar-danger' style='width:".$closed_width."%;'></div>";
        echo "</div>";
        echo "</td></tr>";
    }

    // echo "</tr>";
}

mysql_close($link);
?>

                </tbody>
            </table>
            <br><br><br>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>