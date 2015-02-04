<?php

/*
### assertions.php
Recieves querystring paramaters, querries the LRS for a matching statement and returns either assertion JSON or an error. 
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

//TODO: Currently the page will work with any LRS, which is nice but could be a security issue. 
    //Add a whitelist of allowed LRS endpoints to config.php
    //Think of a plan to support Learning Locker which shares an endpoint between multi-tenanted LRSs
        //White listing auth key/secret combinations?

$lrs = new \TinCan\RemoteLRS();;
$lrs
    ->setEndPoint($endpoint)
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass)

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

