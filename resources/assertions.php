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
Recieves statement id querystring paramater, querries the LRS for a matching statement 
and returns either assertion JSON or an error. 
*/
require "../config.php";
require ("../TinCanPHP/autoload.php");
require ("../TinBadges/Baker.php");
require ("../TinBadges/RemoteLRS.php");
require ("../TinBadges/Util.php");

require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/SignerInterface.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/PublicKey.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RSA.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RS256.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Encoder.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Base64UrlSafeEncoder.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWT.php";
require_once "../TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWS.php";

header('Content-Type: application/json');

if (isset($_GET["statement"])) {
    $statementId = urldecode($_GET["statement"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}

/* 
I considered including LRS detals in the assertion querystring so this page could work with any LRS but concluded 
that this was too big a security risk. Each LRS or LRS-using badging application can easily 
implement this resource in order to support LRS-hosted badge assertions. 
*/
$lrs = new \TinBadges\RemoteLRS();
$baker = new \TinBadges\Baker();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login, $CFG->pass);

$statementResponse = $lrs->retrieveStatement($statementId, array('attachments' => true));

if ($statementResponse->success) {
    $statement = $statementResponse->content;
    if ($baker->verifyBadgeStatement($statement)["success"]) {
        $assertion = $baker->statementToAssertion($statement);
        echo json_encode($assertion, JSON_UNESCAPED_SLASHES);
    } else {
        //Signature did not verify
        //TODO: return whatever error code the OB spec demands?
        var_dump($baker->verifyBadgeStatement($statement));
    }
} else {
    //Statement not found.
    //TODO: return whatever error code the OB spec demands?
}
