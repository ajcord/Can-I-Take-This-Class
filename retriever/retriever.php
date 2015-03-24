<?php

//Connect to MySQL
$link = mysql_connect("engr-cpanel-mysql.engr.illinois.edu", "classmat_www", "ClassMaster");
if (!$link) {
    // die("Could not connect to MySQL: " . mysql_error());
}
mysql_select_db("classmat_411");

//Determine which term and year to query
$term = "fall";
$year = "2015";
$sem = substr($term, 0, 2) . substr($year, 2, 2);

echo "Starting retrieval at ".date("Y-m-d H:i:s")."\n\n";

//Get a list of all the departments
$catalog_data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/".$year."/".$term.".xml");
$catalog_parsed = new SimpleXMLElement($catalog_data);
foreach ($catalog_parsed->subjects->subject as $s) {
    $subject = $s["id"];

    echo $subject."...";

    //Get the schedule data
    try {
        $data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/schedule/".$year."/".$term."/".$subject.".xml?mode=cascade");
        $parsed = new SimpleXMLElement($data);
        echo "done\n";

        //Parse the XML data
        foreach ($parsed->cascadingCourses->cascadingCourse as $c) {
            foreach($c->detailedSections->detailedSection as $s) {
                $crn = $s["id"];
                $availability = $s->enrollmentStatus;
                $avail_num = 0;
                switch ($availability) {
                    case "Closed":
                        $avail_num = 0;
                        break;
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
                        $avail_num = -1;
                        break;
                }

                $course_num = $s->parents->course["id"];
                $section_num = $s->sectionNumber;
                $course_name = $c->label;
                // echo $crn." ".$sem." ".$subject." ".$course_num." ".$section_num." ".$course_name."\n";
                // echo "Updating records for ".$crn."... ";

                // Insert the data into MySQL
                $retval = mysql_query("insert into availability (crn, semester, enrollmentstatus) ".
                    "values (".$crn.", \"".$sem."\", ".$avail_num.")");
                if (!$retval) {
                    echo "could not enter availability data: ".mysql_error();
                }

                $retval = mysql_query("insert into sections (crn, semester, coursenumber, subjectcode, name) ".
                    "values (".$crn.", \"".$sem."\", ".$course_num.", \"".$subject."\", \"".$course_name."\")".
                    "on duplicate key update ".
                    "crn=values(crn), semester=values(semester), coursenumber=values(coursenumber), ".
                    "subjectcode=values(subjectcode), name=values(name)");
                if (!$retval) {
                    echo "could not enter section data: ".mysql_error();
                }

                echo "done\n";
            }
        }
    } catch (Exception $e) {
        echo "error:".$e->getMessage()."\n";
    }
}

mysql_close($link);
echo "\nFinished retrieval at ".date("Y-m-d H:i:s")."\n\n\n";
?>