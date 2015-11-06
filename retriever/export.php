<?php

include "../templates/connect_mysql.php";

$sem = "fa15";

$sql = "select subjectcode, coursenumber, crn, timestamp, enrollmentstatus from ".
            "(select * from availability where semester='$sem') as data ".
            "inner join (select * from sections where semester='sem') as classes ".
            "using(crn)";

$retval = mysql_query($sql) or die("Could not export: ".mysql_error());

echo "Writing data to dump.csv...";

$file = fopen("dump.csv", 'w');

while ($row = mysql_fetch_row($retval)) {
    fputcsv($file, $row);
}

fclose($f);

echo "Exported data to dump.csv";

?>