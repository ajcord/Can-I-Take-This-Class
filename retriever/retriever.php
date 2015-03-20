<?php
$data = file_get_contents("http://courses.illinois.edu/cisapp/explorer/catalog/2015/spring/CS.xml");
$parsed = new SimpleXMLElement($data);
foreach ($parsed->courses->course as $c) {
    echo $parsed["id"] . " " . $c["id"] . ": " . $c . "\n";
}
?>