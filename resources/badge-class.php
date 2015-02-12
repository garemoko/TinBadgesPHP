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

//TODO: use data from badge-definitions.php
        //TODO: Use Accept-language to return the canonical activity definition in the requested language, thus solving localization
        // of Badge classes in Open Badges

$badges = array(
    "1" => array(
        "name" => "Example Tin Badge number one",
        "description" => "The first example Tin Badge",
        "image" => $CFG->wwwroot ."/badges/badge-one.png",
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=1",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    ),
        "2" => array(
        "name" => "Example Tin Badge number two",
        "description" => "The second example Tin Badge",
        "image" => $CFG->wwwroot ."/badges/badge-two.png",
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=2",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    )
);

if (isset($badges[$badgeId])){
    echo json_encode($badges[$badgeId], JSON_UNESCAPED_SLASHES);
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}