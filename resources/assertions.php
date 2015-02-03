<?php

/*
### assertions.php
Recieves querystring paramaters, querries the LRS for a matching statement and returns either assertion JSON or an error. 
*/
include "../config.php";
require ("../TinCanPHP/autoload.php");
include "../includes/badges-lib.php";

header('Content-Type: application/json');

if (isset($_GET["statement"]) && isset($_GET["endpoint"]) && isset($_GET["auth"])){
    $statementId = urldecode($_GET["statement"]);
    $endpoint = urldecode($_GET["endpoint"]);
    $auth = urldecode($_GET["auth"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

$lrs = new \TinCan\RemoteLRS();;
$lrs
    ->setEndPoint($endpoint)
    ->setAuth($auth)
    ->setversion($CFG->version);

$statementResponse = $lrs->retrieveStatement($statementId);

//TODO: validate that the actor uses mbox
//TODO: support other IFIs

if ($statementResponse->success){
    $statement = $statementResponse->content;
    $assertion = statementToAssertion($statement);
    echo json_encode($assertion, JSON_UNESCAPED_SLASHES);
} else{
    //TODO: return whatever error code the OB spec demands?
}

