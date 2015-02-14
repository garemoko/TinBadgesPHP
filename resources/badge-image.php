<?php

/*
### badge-image.php
Recieves badge activity id, returns badge source image. 
*/
include "../config.php";
include "../includes/badges-lib.php";
require ("../TinCanPHP/autoload.php");
include "../includes/tincan-lib.php";

header('Content-Type: image/png');

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

$getActivityProfileResponse = $lrs->retrieveActivityProfile(
    array("id" => $badgeId), 
    "http://standard.openbadges.org/xapi/activiy-profile/badgeimage.json"
);

if ($getActivityProfileResponse->success){
    echo $getActivityProfileResponse->content->getContent();
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}

