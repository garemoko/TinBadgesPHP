<?php

/*
### badge-class.php
Recieves querystring paramaters, returns hard coded badge class json.
*/
include "../config.php";
header('Content-Type: application/json');

if (isset($_GET["badge-id"])){
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

//TODO: combine badge data for badge-class.php and badge-definition.php in a separate file and return the appropriate format
$badges = array(
    "1" => array(
        "name" => array("en"=>"Example Tin Badge number one"),
        "description" => array("en"=>"The first example Tin Badge"),
        "type" => "http://activitystrea.ms/schema/1.0/badge"
        "extensions" => array(
            "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=1"
            )
        )
    )
);

if (isset($badges[$badgeId])){
    echo json_encode($badges[$badgeId], JSON_UNESCAPED_SLASHES);
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}