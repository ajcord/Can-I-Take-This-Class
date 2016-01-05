<?php

include "../../config.php";

$link = mysql_connect($db_config["server"], $db_config["user"], $db_config["pass"])
            or die("Could not connect to MySQL: " . mysql_error());

mysql_select_db($db_config["database"]);

?>