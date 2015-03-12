<?php
/*
Copyright 2015 Rustici Software

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

### badge-image.php
Recieves badge activity id, returns badge source image. 
*/
require "../config.php";
require ("../TinCanPHP/autoload.php");
require ("../TinBadges/Baker.php");
require ("../TinBadges/RemoteLRS.php");
require ("../TinBadges/Util.php");

header('Content-Type: image/png');

if (isset($_GET["activity-id"])) {
    $badgeId = urldecode($_GET["activity-id"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$lrs = new \TinBadges\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login, $CFG->pass);

$getActivityProfileResponse = $lrs->retrieveActivityProfile(
    array("id" => $badgeId),
    "http://standard.openbadges.org/xapi/activiy-profile/badgeimage.json"
);

if ($getActivityProfileResponse->success) {
    echo $getActivityProfileResponse->content->getContent();
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}
