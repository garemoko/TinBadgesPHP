<?php

/*
### badge-class.php
Recieves badge activity id, returns badge class json.
*/
include "../config.php";
include "../includes/badges-lib.php";
require ("../TinCanPHP/autoload.php");
include "../includes/tincan-lib.php";

header('Content-Type: application/json');

if (isset($_GET["activity-id"])){
    $badgeId = urldecode($_GET["activity-id"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$lrs = new \TinCan\ExtendedRemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass);

$activityDefResponse = $lrs->retrieveFullActivityObject($badgeId);

if ($activityDefResponse->success){
    $badgeDefinition = $activityDefResponse->content->getDefinition();
    $badgeClassData = $badgeDefinition->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"];
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}

echo json_encode(
    array(
        "name" => getAppropriateLanguageMapValue($badgeDefinition->getName()->asVersion("1.0.0")),
        "description" => getAppropriateLanguageMapValue($badgeDefinition->getDescription()->asVersion("1.0.0")),
        "image" => $badgeClassData["image"],
        "criteria" => $badgeClassData["criteria"],
        "issuer" => $badgeClassData["issuer"]
    ), 
    JSON_UNESCAPED_SLASHES
);


