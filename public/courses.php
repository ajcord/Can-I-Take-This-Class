<?php include "../templates/header.php"; ?>

Subject:

<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <span id="subj-name">Select one</span>
        <span class="caret"></span>
    </button>
    <ul id="subj-dropdown" class="dropdown-menu" role="menu">
<?php
include "../templates/connect_mysql.php";

//Get a list of subjects for next semester
$sql = "select subjectcode from sections where semester='fa15' group by subjectcode";

$retval = mysql_query($sql);
if (!$retval) {
    die("Could not get subjects: ".mysql_error());
}

$course_data = array();
while($row = mysql_fetch_assoc($retval)) {
    echo "<li><a href='#'>".$row["subjectcode"]."</a></li>";
}

mysql_close($link);
?>
    </ul>
</div>

<div class="container-fluid" id="table-container">
    <div class="alert alert-info" role="alert" id="loading-alert">Loading...</div>
    <table class="table table-striped">
        <thead>
            <tr>
                <td>#</td>
                <td>Name</td>
                <td>% Open</td>
            </tr>
        </thead>
        <tbody id="courses-table">

        </tbody>
    </table>
</div>

<?php include "../templates/footer.php"; ?>