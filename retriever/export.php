<?php

include "../templates/connect_mysql.php";

$sem = "fa15";

echo "Querying data...\n";

$sql = "select subjectcode, coursenumber, crn, timestamp, enrollmentstatus from ".
            "(select * from availability where semester='$sem') as data ".
            "inner join (select * from sections where semester='$sem') as classes ".
            "using(crn) order by subjectcode, coursenumber, timestamp";

$retval = mysql_unbuffered_query($sql) or die("Could not export: ".mysql_error());

echo "Writing data to dump.csv...\n";

$file = fopen("dump.csv", 'w');

while ($row = mysql_fetch_row($retval)) {
    fputcsv($file, $row);
}

fclose($file);

echo "Exported data to dump.csv\n";

mysql_close($link);

?>