<?php

/*
### verify-signed-statement.php
Recieves statement id and public key querystring paramaters, queries the LRS for a matching statement, verifies that statement and returns a sucess status
*/
include "../config.php";
require ("../TinCanPHP/autoload.php");

if (isset($_GET["statement"])){
    $statementId = urldecode($_GET["statement"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}



$lrs = new \TinCan\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->readonly_login,$CFG->readonly_pass);

$statementResponse = $lrs->retrieveStatement($statementId, array('attachments' => true));

if ($statementResponse->success){
    $statement = $statementResponse->content;

    $authority = $statement->getAuthority(); //get the cert matching the authority presented in the statement.
    $getAgentProfileResponse = $lrs->retrieveAgentProfile($authority, "http://id.tincanapi.com/agent-profile/jws-certificate-location");

    if ($getAgentProfileResponse->success){
        $publicKeyLocation = $getAgentProfileResponse->content->getContent();
        $publicKey = file_get_contents($publicKeyLocation);
        if ($publicKey !== false){
            header('Content-Type: application/x-pem-file');
            echo $publicKey;
        } else {
            header('Content-Type: application/json');
            echo json_encode(
                array(
                    'success' => false, 
                    'reason' => 'No certificate found at specified location.'
                ), 
                JSON_UNESCAPED_SLASHES
            );
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(
            array(
                'success' => false, 
                'reason' => 'No certificate location found for authority.'
            ), 
            JSON_UNESCAPED_SLASHES
        );
    }

} else{
    header('Content-Type: application/json');
    echo json_encode(
        array(
            'success' => false, 
            'reason' => 'Target statement not found.'
        ), 
        JSON_UNESCAPED_SLASHES
    );
}