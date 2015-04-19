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

            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>#</td>
                        <td>Name</td>
                        <td>% Open</td>
                    </tr>
                </thead>
                <tbody id="courses-table">
<?php
include "../templates/connect_mysql.php";

//Get the user id
$id = $_SESSION["id"];

//Get a list of courses the user wants
$sql = "select subjectcode, coursenumber from wants where userid=".$id." and semester='fa15'";

$retval = mysql_query($sql);
if (!$retval) {
    die("Could not get wants: ".mysql_error());
}

$course_data = array();
while($row = mysql_fetch_assoc($retval)) {
    echo "<tr><td>".$row["subjectcode"]." ".$row["coursenumber"]."</td></tr>";
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