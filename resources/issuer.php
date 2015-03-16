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
v "../config.php";
require "../includes/badges-lib.php";
require ("../TinCanPHP/autoload.php");
require ("../TinBadges/Baker.php");
require ("../TinBadges/RemoteLRS.php");
require ("../TinBadges/Util.php");

header('Content-Type: application/json');

if (isset($_GET["activity-id"])) {
    $issuerId = urldecode($_GET["activity-id"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    echo("No badge id specified. You must specify a badge id with the activity-id querystring parameter.");
    die();
}

$util = new \TinBadges\Util();
$lrs = new \TinBadges\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login, $CFG->pass);

$activityDefResponse = $lrs->retrieveFullActivityObject($issuerId);

if ($activityDefResponse->success) {
    $badgeDefinition = $activityDefResponse->content->getDefinition();
    $badgeClassData = $badgeDefinition
        ->getExtensions()
        ->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"];
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    echo(
        "Issuer id ". $issuerId . " not found.<br/> "
        ."LRS response: " . $statementResponse->httpResponse["status"] . " " . $statementResponse->content
    );
    die();
}

echo json_encode(
    array(
        "name" => $util->getAppropriateLanguageMapValue($badgeDefinition->getName()->asVersion("1.0.0")),
        "description" => $util->getAppropriateLanguageMapValue($badgeDefinition->getDescription()->asVersion("1.0.0")),
        "url" => $issuerId
    ),
    JSON_UNESCAPED_SLASHES
);
