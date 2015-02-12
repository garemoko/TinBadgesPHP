<?php

/*
### badge-class.php
Recieves querystring paramaters, returns hard coded badge class json.
*/
include "../config.php";
include "../includes/badges-lib.php";

//TODO: get the badge definitions from the LRS rather than from $badgeDefinitions to simulate the idea that
// the badge class.php does not need to rely on hard coded badge definitions. 
include "../includes/badge-definitions.php";

header('Content-Type: application/json');

if (isset($_GET["badge-id"])){
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

if (isset($badgeDefinitions[$badgeId])){
    $badgeDefinition = $badgeDefinitions[$badgeId];
    $badgeClassDatum = $badgeClassData[$badgeId];
    echo json_encode(
        array(
            "name" => getAppropriateLanguageMapValue($badgeDefinition["name"]),
            "description" => getAppropriateLanguageMapValue($badgeDefinition["description"]),
            "image" => $badgeClassDatum["image"],
            "criteria" => $badgeClassDatum["criteria"],
            "issuer" => $badgeClassDatum["issuer"]
        ), 
        JSON_UNESCAPED_SLASHES
    );
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}

