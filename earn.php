<?php

/*
### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 
Includes stream.php and badges.php as side/bottom blocks or links to them. 
*/

include "includes/head.php";
include "includes/badges-lib.php";
include "includes/bakerlib.php"; //from Moodle
include "config.php";
require ("TinCanPHP/autoload.php");

//TODO: redirect the user to index.php if email and name are not provided
    //TODO: include a validation message
$userEmail = $_POST["email"];
$userName = $_POST["name"];

$lrs = new \TinCan\RemoteLRS();
$tinCanPHPUtil = new \TinCan\Util();
$lrs
    ->setEndPoint($CFG->endpoint)
    ->setAuth($CFG->login,$CFG->pass)
    ->setversion($CFG->version);

if (isset($_POST["badge"])){
//In a production example, this page would include security checks. 
    $badge = $_POST["badge"];

    //issue a statement to say the user launched this prototype. 

    $statementId = $tinCanPHPUtil->getUUID();

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
            "object" => array(
                "id" =>  $CFG->wwwroot . "/resources/badge-defintion.php?badge-id=1",
                "definition" => array( //TODO: add badge name and description?
                    "name" => array("en"=>"Example Tin Badge number one"),
                    "description" => array("en"=>"The first example Tin Badge"),
                    "type" => "http://activitystrea.ms/schema/1.0/badge",
                    "extensions" => array(
                        "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                            "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=1"
                        )
                    )
                )
            ),
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
                )
            )
        )
    );

    //Build the badge
    $assertion = statementToAssertion($statement);

//TODO: rename these variables
    $sourcePNG = file_get_contents($CFG->wwwroot ."/badges/badge-one.png");

    $metadatahandler = new PNG_MetaDataHandler($sourcePNG);

    if ($metadatahandler->check_chunks("iTXt", "openbadge")) {
        $badgePNG = $metadatahandler->add_chunks("iTXt", "openbadges", json_encode($assertion, JSON_UNESCAPED_SLASHES));
    } else {
        //TODO: error - there's a problem with the input image. Is this already a baked Open Badge? It should be a normal png.
    }

    //TODO: add badge as attachment
    //TODO: sign statement

    $lrs->saveStatement($statement);
    //TODO: handle errors

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
                <img src="badges/badge-one.png" class="open-badge-150 center-block">
                <form action="earn.php" method="post">
                    <input type="hidden" class="form-control" id="name" name="name" value="<?php echo $userName ?>">
                    <input type="hidden" class="form-control" id="email" name="email" value="<?php echo $userEmail ?>">
                    <input type="hidden" class="form-control" id="badge" name="badge" value="badge1">
                    <button type="submit" class="btn btn-primary earn-btn">Earn!</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4 panel panel-default">
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