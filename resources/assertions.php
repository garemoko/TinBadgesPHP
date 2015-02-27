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

### assertions.php
Recieves statement id querystring paramater, querries the LRS for a matching statement and returns either assertion JSON or an error. 
*/
include "../config.php";
require ("../TinCanPHP/autoload.php");
include "../includes/badges-lib.php";

header('Content-Type: application/json');

if (isset($_GET["statement"])){
    $statementId = urldecode($_GET["statement"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

/* 
* I considered including LRS detals in the assertion querystring so this page could work with any LRS but concluded 
* that this was too big a security risk. Each LRS or LRS-using badging application can easily implement this resource in 
* order to support LRS-hosted badge assertions. 
*/

$lrs = new \TinCan\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass);

$statementResponse = $lrs->retrieveStatement($statementId);

if ($statementResponse->success){
    $statement = $statementResponse->content;
    if (verifyBadgeStatement($queryStatement)["success"]){
        $assertion = statementToAssertion($statement);
        echo json_encode($assertion, JSON_UNESCAPED_SLASHES);
    } else {
        //Signature did not verify
        //TODO: return whatever error code the OB spec demands?
    }
} else{
    //Statement not found. 
    //TODO: return whatever error code the OB spec demands?
}

