<?php

include "../templates/connect_mysql.php";

$courses = $_GET["courses"];		
$date = mysql_real_escape_string(urldecode($_GET["date"]));

include "../templates/analyze.php";

echo json_encode($courses_data);

mysql_close($link);

?>