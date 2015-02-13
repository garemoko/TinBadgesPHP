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

if (isset($_GET["badge-id"])){
    $badgeId = urldecode($_GET["badge-id"]);
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
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}

$getActivityProfileResponse = $lrs->retrieveActivityProfile(
    array("id" => $badgeId), 
    "http://standard.openbadges.org/xapi/activiy-profile/badgeclass.json"
);

if ($getActivityProfileResponse->success){
    $badgeClassData = json_decode($getActivityProfileResponse->content->getContent(),true);
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
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}

