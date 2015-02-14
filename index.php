<?php

/*
### index.php
An introduction to the prototype. Collects user information, links to earn.php 
*/

include "config.php";
include 'includes/head.php';

include "includes/badge-definitions.php";
require ("TinCanPHP/autoload.php");
include "includes/tincan-lib.php";

//The below requires can be removed once the statement signing features have been merged into TinCanPHP
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/SignerInterface.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/PublicKey.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RSA.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Signer/RS256.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Encoder.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/Base64/Base64UrlSafeEncoder.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWT.php";
require_once "TinCanPHP/vendor/namshi/jose/src/Namshi/JOSE/JWS.php";

$lrs = new \TinCan\RemoteLRS();
$tinCanPHPUtil = new \TinCan\Util();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass)
    ->setversion($CFG->version);

//Store the badges to be used in this prototype in the LRS. 
//TODO: store issuer details in the LRS

foreach ($badgeList as $badge => $badgeData) {

    $statementId = $tinCanPHPUtil->getUUID();

    $badgeActivity = array(
        "id" =>  $CFG->wwwroot . "/resources/badge-defintion.php?badge-id=".$badge,
        "definition" => $badgeData["tinCanDefinition"]
    );

    $statement = new \TinCan\statement(
        array(
            "id"=> $statementId,
            "timestamp" => (new \DateTime())->format('c'), 
            "actor" => array(
                "name" => "Tin Badges Prototype Admin",
                "account" => array(
                    "name" => "admin",
                    "homePage" => $CFG->wwwroot,
                )
            ), 
            "verb" => array(
                "id" => "http://standard.openbadges.org/xapi/verbs/created-badge-class.json",
                "display" => array(
                    "en" => "created Open Badge",
                ),
            ),
            "object" => $badgeActivity,
            "context" => array(
                "contextActivities" => array(
                    "category" => array(
                        array( //TODO: Host metadata at standard.openbadges.org and update this to version 1 for release version of recipe
                            "id" => "http://standard.openbadges.org/xapi/recipe/base/0",
                            "definition" => array(
                                "type" => "http://id.tincanapi.com/activitytype/recipe"
                            )
                        )
                    )
                ),
                "extensions" => array(
                    "http://id.tincanapi.com/extension/jws-certificate-location" => $CFG->wwwroot ."/signing/cacert.pem"
                )
            )
        )
    );

    //Note: in a real system, the private key should NOT be available via the web.
    if (isset($_POST["fakesig"])){
        $privKey = "file://signing/hackerkey.pem";
    } else {
        $privKey = "file://signing/privkey.pem";
    }

    $statement->sign($privKey, $CFG->privateKeyPassPhrase);

    
    $response = $lrs->saveStatement($statement);
    if (!$response->success){
        echo ("<p class='alert alert-danger' role='alert'>Error communicating with the LRS Statement API. Please check your configuration settings.</p>");
        echo ("<p class='alert alert-info' role='alert'><b>Error code:</b> " . $response->httpResponse["status"] . "<br/>");
        echo ("<b>Error content:</b> " . $response->content . "</p>");
    }

    //Store badge image in Activity Profile API. This is retireved by resources/badge-image.php whenever a client looks up the badge image
    $getActivityProfileImageResponse = $lrs->retrieveActivityProfile(
        $badgeActivity, 
        "http://standard.openbadges.org/xapi/activiy-profile/badgeimage.json"
    );
    if ($getActivityProfileImageResponse->success){
        $activityProfileImageEtag = $getActivityProfileImageResponse->content->getEtag();

        $setActivityProfileImageResponse = $lrs->saveActivityProfile(
            $badgeActivity, 
            "http://standard.openbadges.org/xapi/activiy-profile/badgeimage.json", 
            file_get_contents($badgeData["sourceImage"]),
            array(
                "etag" => $activityProfileImageEtag,
                "contentType" => "application/json"
            )
        );
        if (!$setActivityProfileImageResponse->success){
            echo ("<p class='alert alert-danger' role='alert'>Error storing badge metadata. </p>");
            echo ("<p class='alert alert-info' role='alert'><b>Error code:</b> " . $setActivityProfileImageResponse->httpResponse["status"] . "<br/>");
            echo ("<b>Error content:</b> " . $setActivityProfileImageResponse->content . "</p>");
        }
    } else {
        echo ("<p class='alert alert-danger' role='alert'>Error retrieving activity profile eTag. </p>");
        echo ("<p class='alert alert-info' role='alert'><b>Error code:</b> " . $getActivityProfileImageResponse->httpResponse["status"] . "<br/>");
        echo ("<b>Error content:</b> " . $getActivityProfileImageResponse->content . "</p>");
    }
}

?>

<p>The TinBadges prototype illsutrates statement signing, attachments and Open Badges. 
Get started by entering user details below:
</p>

<form action="earn.php" method="post">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" id="name" name="name" value="Default Example Name">
    </div>
    <div class="form-group">
        <label for="email">Email address:</label>
        <input type="text" class="form-control" id="email" name="email" value="<?php echo $tinCanPHPUtil->getUUID(); ?>@example.com">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?
include 'includes/foot.php';