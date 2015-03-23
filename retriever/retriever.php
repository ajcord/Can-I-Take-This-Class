<?php

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");

//Determine which term and year to query
$term = "fall";
$year = "2015";
$sem = substr($term, 0, 2) . substr($year, 2, 2);

//Get a list of all the departments
$catalog_data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/".$year."/".$term.".xml");
$catalog_parsed = new SimpleXMLElement($catalog_data);
foreach ($catalog_parsed->subjects->subject as $s) {
    $subject = $s["id"];

    echo $subject."\n";

    //Get the schedule data
    $data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/schedule/".$year."/".$term."/".$subject.".xml?mode=cascade");
    $parsed = new SimpleXMLElement($data);

    //Parse the XML data
    foreach ($parsed->cascadingCourses->cascadingCourse as $c) {
        foreach($c->detailedSections->detailedSection as $s) {
            $crn = $s["id"];
            $availability = $s->enrollmentStatus;
            $avail_num = 0;
            switch ($availability) {
                case "Closed":
                    $avail_num = 0;
                case "Open":
                    $avail_num = 1;
                    break;
                case "Open (Restricted)":
                    $avail_num = 2;
                    break;
                case "CrossListOpen":
                    $avail_num = 3;
                    break;
                default: //Unknown
                    $avail_num = 4;
                    break;
            }

            $course_num = $s->parents->course["id"];
            $section_num = $s->sectionNumber;
            $course_name = $c->label;
            // echo $crn." ".$sem." ".$subject." ".$course_num." ".$section_num." ".$course_name."\n";

            // Insert the data into MySQL
            $retval = mysql_query("insert into availability (crn, semester, enrollmentstatus) ".
                "values (".$crn.", \"".$sem."\", ".$avail_num.")");
            if (!$retval) {
                die("Could not enter data: ".mysql_error());
            }

            $retval = mysql_query("insert into sections (crn, semester, coursenumber, subjectcode, name) ".
                "values (".$crn.", \"".$sem."\", ".$course_num.", \"".$subject."\", \"".$course_name."\")".
                "on duplicate key update ".
                "crn=values(crn), semester=values(semester), coursenumber=values(coursenumber), ".
                "subjectcode=values(subjectcode), name=values(name)");
            if (!$retval) {
                die("Could not enter data: ".mysql_error());
            }
        }
    }
}

echo "Success\n";
mysql_close($link);
?>