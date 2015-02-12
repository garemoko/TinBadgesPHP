<?php

/*
### verify-signed-statement.php
Recieves statement id and public key querystring paramaters, queries the LRS for a matching statement, verifies that statement and returns a sucess status
*/
include "../config.php";
require ("../TinCanPHP/autoload.php");
include "../includes/badges-lib.php";

//The below requires can be removed once the statement signing features have been merged into TinCanPHP
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/SignerInterface.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/PublicKey.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RSA.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RS256.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Encoder.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Base64UrlSafeEncoder.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWT.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWS.php";

if (isset($_GET["statement"])){
    $statementId = urldecode($_GET["statement"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

header('Content-Type: application/json');

$lrs = new \TinCan\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->readonly_login,$CFG->readonly_pass);

$statementResponse = $lrs->retrieveStatement($statementId, array('attachments' => true));

if ($statementResponse->success){
    $statement = $statementResponse->content;

    $certlocation = $statement->getContext()->getExtensions()->asVersion("1.0.0")["http://id.tincanapi.com/extension/jws-certificate-location"];

    $cert = openssl_pkey_get_public(openssl_x509_read(file_get_contents($certlocation)));

    $verifyResponse = $statement->verify(
        array(
            'publicKey' => $cert
        )
    );
    echo json_encode($verifyResponse, JSON_UNESCAPED_SLASHES);
} else{
    echo json_encode(
        array(
            'success' => false, 
            'reason' => 'Target statement not found.'
        ), 
        JSON_UNESCAPED_SLASHES
    );
}
