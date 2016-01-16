<?php

include __DIR__."/../templates/connect_mysql.php";

$sem = "fa15";

echo "Querying data...\n";

$sql = "select subjectcode, coursenumber, crn, timestamp, enrollmentstatus from ".
            "(select * from availability where semester=':sem') as data ".
            "inner join (select * from sections where semester=':sem') as classes ".
            "using(crn) order by subjectcode, coursenumber, timestamp";

$stmt = $dbh->prepare($sql);
$stmt->bindParam(":sem", $sem);
$stmt->execute();

echo "Writing data to dump.csv...\n";

$file = fopen("dump.csv", 'w');

while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    fputcsv($file, $row);
}

fclose($file);

echo "Exported data to dump.csv\n";

?>