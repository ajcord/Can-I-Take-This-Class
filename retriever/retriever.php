<?php

$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
echo "Connected successfully";
mysql_close($link);

// $term = "fall";
// $year = "2015";
// $sem = substr($term, 0, 2) . substr($year, 2, 2);

// $data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/schedule/".$year."/".$term."/CS.xml?mode=cascade");
// $parsed = new SimpleXMLElement($data);

// foreach ($parsed->cascadingCourses->cascadingCourse as $c) {
//     foreach($c->detailedSections->detailedSection as $s) {
//         $crn = $s["id"];
//         $availability = $s->enrollmentStatus;
//         echo $crn.": ".$availability."\n";
//     }
// }
?>