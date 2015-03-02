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

### issuer.php
Recieves issuer activity id, returns isuser json.
*/
include "../config.php";
include "../includes/badges-lib.php";
require ("../TinCanPHP/autoload.php");
include "../includes/TinBadges.php";

header('Content-Type: application/json');

if (isset($_GET["activity-id"])){
    $issuerId = urldecode($_GET["activity-id"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$lrs = new \TinBadges\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass);

$activityDefResponse = $lrs->retrieveFullActivityObject($issuerId);

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
        "url" => $issuerId
    ), 
    JSON_UNESCAPED_SLASHES
);
