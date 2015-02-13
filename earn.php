<?php

/*
### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 
Includes stream.php and badges.php as side/bottom blocks or links to them. 
*/

include "config.php";
if (!(isset($_POST["email"]) && isset($_POST["name"]))){
    header("Location: ". $CFG->wwwroot);
}

include "includes/head.php";
include "includes/badges-lib.php";
include "includes/bakerlib.php"; //from Moodle
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


$userEmail = $_POST["email"];
$userName = $_POST["name"];

$lrs = new \TinCan\RemoteLRS();
$tinCanPHPUtil = new \TinCan\Util();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass)
    ->setversion($CFG->version);

//TODO: Load badges (definition, class AND image) to the LRS on index.php. Get that stuff from the LRS here. 

if (isset($_POST["badge"])){
//In a production example, this page would include security checks. 
    $badge = $_POST["badge"];

    $statementId = $tinCanPHPUtil->getUUID();

    $badgeActivity = array(
        "id" =>  $CFG->wwwroot . "/resources/badge-defintion.php?badge-id=".$badge,
        "definition" => $badgeDefinitions[$badge]
    );

    $statement = new \TinCan\statement(
        array(
            "id"=> $statementId,
            "timestamp" => (new \DateTime())->format('c'), 
            "actor" => array(
                "mbox"=> "mailto:".$userEmail,
                "name"=> $userName
            ), 
            "verb" => array(
                "id" => "http://standard.openbadges.org/xapi/verbs/earned.json",
                "display" => array(
                    "en" => "earned",
                ),
            ),
            "object" => $badgeActivity,
            "result" => array(
                "extensions" => array(
                    "http://standard.openbadges.org/xapi/extensions/badgeassertion.json" => array(
                        "@id" => $CFG->wwwroot . "/resources/assertions.php?statement=" . urlencode($statementId)
                    )
                )
            ),
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

    //Build the badge
    $assertion = statementToAssertion($statement);

    $badgePNG = bakeBadge($badgeImages[$badge], $assertion);

    //echo ("<img src='data:image/png;base64,".base64_encode($badgePNG)."'>");

    $statement->setAttachments( array(
        array(
            "content" => $badgePNG,
            "contentType" => "image/png",
            "usageType" => "http://standard.openbadges.org/xapi/attachment/badge.json",
            "display" => $badgeDefinitions[$badge]["name"]
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

    //Store badge metadata in Activity Profile API. This is retireved by resources/badge-class.php whenever a client looks up the badge class
    $getActivityProfileResponse = $lrs->retrieveActivityProfile(
        $badgeActivity, 
        "http://standard.openbadges.org/xapi/activiy-profile/badgeclass.json"
    );
    if ($getActivityProfileResponse->success){
        $activityProfileEtag = $getActivityProfileResponse->content->getEtag();

        $setActivityProfileResponse = $lrs->saveActivityProfile(
            $badgeActivity, 
            "http://standard.openbadges.org/xapi/activiy-profile/badgeclass.json", 
            json_encode($badgeClassData[$badge],JSON_UNESCAPED_SLASHES),
            array(
                "etag" => $activityProfileEtag,
                "contentType" => "application/json"
            )
        );
        if (!$setActivityProfileResponse->success){
            echo ("<p class='alert alert-danger' role='alert'>Error storing badge metadata. </p>");
            echo ("<p class='alert alert-info' role='alert'><b>Error code:</b> " . $setActivityProfileResponse->httpResponse["status"] . "<br/>");
            echo ("<b>Error content:</b> " . $setActivityProfileResponse->content . "</p>");
        }
    } else {
        echo ("<p class='alert alert-danger' role='alert'>Error retrieving activity profile eTag. </p>");
        echo ("<p class='alert alert-info' role='alert'><b>Error code:</b> " . $getActivityProfileResponse->httpResponse["status"] . "<br/>");
        echo ("<b>Error content:</b> " . $getActivityProfileResponse->content . "</p>");
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
            file_get_contents($badgeImages[$badge]),
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

<div class="row">
    <div class="col-md-8 panel panel-default">
        <h2>Claim your badge, <?php echo $userName ?></h2>
        <p>
            This page similuates a user (<?php echo $userEmail ?>) logged into an LMS earning a badge. Badges can be earned by completing 
            some kind of signnificant achievement, but today you"ll earn a badge by clicking a button! 
        </p>
        <div class="row">
            <div class="col-md-3 text-center">
                <img src="<?php echo $badgeImages["3"] ?>" class="open-badge-150 center-block">
                <form action="earn.php" method="post">
                    <input type="hidden" class="form-control" id="name" name="name" value="<?php echo $userName ?>">
                    <input type="hidden" class="form-control" id="email" name="email" value="<?php echo $userEmail ?>">
                    <input type="hidden" class="form-control" id="badge" name="badge" value="3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="fakesig" name="fakesig"> Fake signature?
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary earn-btn">Earn!</button>
                </form>
            </div>
           <div class="col-md-3 text-center">
                <img src="<?php echo $badgeImages["2"] ?>" class="open-badge-150 center-block">
                <form action="earn.php" method="post">
                    <input type="hidden" class="form-control" id="name" name="name" value="<?php echo $userName ?>">
                    <input type="hidden" class="form-control" id="email" name="email" value="<?php echo $userEmail ?>">
                    <input type="hidden" class="form-control" id="badge" name="badge" value="2">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="fakesig" name="fakesig"> Fake signature?
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary earn-btn">Earn!</button>
                </form>
            </div>
            <div class="col-md-6">
                <p>
                    Use the fake signature button to simulate a hacker attempting to issue a badge to <?php echo $userEmail ?>. Badges issued with a 
                    fake signature will be displayed in the bagde stream below with an <span class='label label-danger'>Invalid Signature</span> tag.
                    They will not appear in <?php echo $userName ?>'s Badge Block. 
                </p>
                <p>
                    All <strong>earned</strong> badges 
                    displayed on this page are downloadable Open Badges, however badges attached to statements with invalid signatures will not
                    verify when uploaded to an Open Badges Backpack. (Also note: the Backpack needs HTTP access the resources folder of this
                    prototype in order to verify any badges).
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-md-offset-1 panel panel-default">
        <?php include "badges.php"; ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 panel panel-default">
        <?php include "stream.php"; ?>
    </div>
</div>

<?
include "includes/foot.php";