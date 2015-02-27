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

### criteria.php
Receives a badge id, returns hard coded badge criteria as human readable plain text. 
*/

//TODO: something interesting with criteria. Perhaps it is a list of template statements? 
//Perhaps use LRMI (http://www.lrmi.net/) as recommended in the Open Badges spec?

header('Content-Type: text/plain');

if (isset($_GET["badge-id"])){
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$badgesCriteria = array(
    "1" => "Criteria for badge one. Lorem Ipsum."
);

if (isset($badgesCriteria[$badgeId])){
    echo $badgesCriteria[$badgeId];
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}