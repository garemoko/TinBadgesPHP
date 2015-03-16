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

### attached-badge.php
Recieves statement id querystring paramater, querries the LRS for a matching statement and 
returns the first Open Badge attachment as a base 64 encoded png image
*/
require "../config.php";
require ("../TinCanPHP/autoload.php");

if (isset($_GET["statement"])) {
    $statementId = urldecode($_GET["statement"]);
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    echo("No statement id specified. You must specify a statement id with the statement querystring parameter.");
    die();
}

/* 
I considered including LRS detals in the assertion querystring so this page could work with any LRS but concluded 
that this was too big a security risk. Each LRS or LRS-using badging application can easily implement 
this resource in order to support LRS-hosted badge assertions. 
*/

$lrs = new \TinCan\RemoteLRS();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->readonly_login, $CFG->readonly_pass);

$statementResponse = $lrs->retrieveStatement($statementId, array('attachments' => true));


if ($statementResponse->success) {
    $attachments = $statementResponse->content->getAttachments();
    foreach ($attachments as $attachment) {
        if ($attachment->getUsageType() == "http://specification.openbadges.org/xapi/attachment/badge.json") {
            //TODO: validate the content type is a png

            //Return a base64 encoded image to be displayed in an HTML page.
            echo base64_encode($attachment->getContent());

            //To return an unencoded image, use:
            //header('Content-Type: image/png');
            //echo $attachment->getContent();

            die();
        }
    }
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    echo(
        "A statement id ". $statementId . " does not have an attached Open Badge."
    );
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    echo(
        "A statement id ". $statementId . " not found.<br/> "
        ."LRS response: " . $statementResponse->httpResponse["status"] . " " . $statementResponse->content
    );
}
