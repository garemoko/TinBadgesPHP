<?php

/*
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
    $assertion = statementToAssertion($statement);
    echo json_encode($assertion, JSON_UNESCAPED_SLASHES);
} else{
    //TODO: return whatever error code the OB spec demands?
}

