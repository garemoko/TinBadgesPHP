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

### badge-class.php
Recieves querystring paramaters, returns hard coded badge class json.
This is used to get data into the LRS inititally.
Use badge-class.php to get this data from the LRS for subsequent use.
*/
require "../config.php";
require "../includes/badge-definitions.php";
header('Content-Type: application/json');

if (isset($_GET["badge-id"])) {
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    echo("No badge id specified. You must specify a badge id with the badge-id querystring parameter.");
    die();
}


if (isset($badges[$badgeId])) {
    echo json_encode($badgeList[$badgeId]["tinCanDefinition"], JSON_UNESCAPED_SLASHES);
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    echo(
        "Definition for badge id ". $badgeId . " not found.<br/> "
    );
    die();
}
