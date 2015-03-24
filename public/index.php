<?php include "../templates/header.php"; ?>

Enter a subject you would like to know about:

<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <span id="subj-name">Select one</span>
        <span class="caret"></span>
    </button>
    <ul id="subj-dropdown" class="dropdown-menu" role="menu">
<?php 
//Get a list of subjects for next semester
$data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/2015/fall.xml");
$parsed = new SimpleXMLElement($data);
foreach ($parsed->subjects->subject as $s) {
    echo "<li><a href=\"#\">".$s["id"]."</a></li>";
}
?>
    </ul>
</div>

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

<?php include "../templates/footer.php"; ?>