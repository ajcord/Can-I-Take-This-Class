<?php

// TODO: have a separate user for public (which can only select)
// than for retrieving (which can insert/delete)
include __DIR__."/../../config.php";

$dbh = null;

try {
    $dbh = new PDO("mysql:host={$db_config["server"]};dbname={$db_config["database"]}", $db_config["user"], $db_config["pass"]);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

?>