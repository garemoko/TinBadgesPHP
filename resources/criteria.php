<?php

/*
### criteria.php
Receives querystring paramaters, returns hard coded badge criteria as human readable plain text. 
*/

//TODO: something interesting with criteria. Perhaps it is a list of template statements? 
//Perhaps use LRMI (http://www.lrmi.net/) as recommended in the Open Badges spec?

header('Content-Type: text/plain');

if (isset($_GET["badge-id"])){
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$badgesCriteria = array(
    "1" => "Criteria for badge one. Lorem Ipsum."
);

if (isset($badgesCriteria[$badgeId])){
    echo $badgesCriteria[$badgeId];
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}