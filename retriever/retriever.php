<?php

include __DIR__."/../templates/connect_mysql_retriever.php";


//Determine which term and year to query
$sem_sql = "select semester from semesters where ".
                "date_add(now(), interval 7 day) >= registrationdate ".
                "order by registrationdate desc limit 1";
$sem_retval = $dbh->query($sem_sql);
$sem = $sem_retval->fetch()["semester"];

$term = "fall";
$year = "20".substr($sem, 2, 2);
if (substr($sem, 0, 2) == "sp") {
    $term = "spring";
}

echo "Starting retrieval for $sem at ".date("Y-m-d H:i:s")."\n\n";

//Get a list of all the departments
$catalog_data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/schedule/$year/$term.xml");
$catalog_parsed = new SimpleXMLElement($catalog_data);
foreach ($catalog_parsed->subjects->subject as $subj) {
    $subject = $subj["id"];

    echo $subject."...";

    //Get the schedule data
    try {
        $data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/schedule/$year/$term/$subject.xml?mode=cascade");
        $parsed = new SimpleXMLElement($data);

        //Get all the sections currently in this subject (to compare later)
        $retval = $dbh->prepare("select crn from sections where subjectcode=:subject and semester=:sem");
        $retval->bindParam(":subject", $subject);
        $retval->bindParam(":sem", $sem);
        $retval->execute();

        $removed_crns = array();
        while($row = $retval->fetch()) {
            array_push($removed_crns, $row["crn"]);
        }

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
                    case "CrossListOpen (Restricted)":
                        $avail_num = 4;
                        break;
                    default: //Unknown
                        $avail_num = -1;
                        break;
                }

                $course_num = $s->parents->course["id"];
                $section_num = $s->sectionNumber;
                $course_name = $c->label;
                $section_type = "";
                if ($s->meetings->meeting) {
                    $section_type = $s->meetings->meeting[0]->type;
                }

                // Remove the section from the list of removed CRNs
                $removed_crns = array_diff($removed_crns, [$crn]);

                // Insert the data into MySQL
                $stmt = $dbh->prepare("insert into sections (crn, semester, coursenumber, subjectcode, name, sectiontype) ".
                    "values (:crn, :sem, :course_num, :subject, :course_name, :section_type)".
                    "on duplicate key update ".
                    "crn=values(crn), semester=values(semester), coursenumber=values(coursenumber), ".
                    "subjectcode=values(subjectcode), name=values(name), sectiontype=values(sectiontype)");

                $stmt->bindParam(":crn", $crn);
                $stmt->bindParam(":sem", $sem);
                $stmt->bindParam(":course_num", $course_num);
                $stmt->bindParam(":subject", $subject);
                $stmt->bindParam(":course_name", $course_name);
                $stmt->bindParam(":section_type", $section_type);
                $stmt->execute();
                
                $stmt = $dbh->prepare("insert into availability (crn, semester, enrollmentstatus) ".
                    "values (:crn, :sem, :avail_num)");

                $stmt->bindParam(":crn", $crn);
                $stmt->bindParam(":sem", $sem);
                $stmt->bindParam(":avail_num", $avail_num);
                $stmt->execute();
            }
        }

        // Delete all removed CRNs
        foreach ($removed_crns as $crn) {
            $retval = $dbh->prepare("delete from sections where crn=:crn and semester=:sem");
            $stmt->bindParam(":crn", $crn);
            $stmt->bindParam(":sem", $sem);
            $stmt->execute();
        }

        echo "done";
    } catch (Exception $e) {
        echo "error:".$e->getMessage();
    }

    echo "\n";
}

echo "\nFinished retrieval at ".date("Y-m-d H:i:s")."\n\n\n";
?>