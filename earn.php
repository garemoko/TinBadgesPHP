<?php

/*
### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 
Includes stream.php and badges.php as side/bottom blocks or links to them. 
*/

include "includes/head.php";
include "config.php";
require ("TinCanPHP/autoload.php");

$userEmail = $_POST["email"];
$userName = $_POST["name"];


if (isset($_POST["badge"])){
//In a production example, this page would include security checks. 
    $badge = $_POST["badge"];

    $lrs = new \TinCan\RemoteLRS();
    $lrs
        ->setEndPoint($CFG->endpoint)
        ->setAuth($CFG->login,$CFG->pass)
        ->setversion($CFG->version);

    //issue a statement to say the user launched this prototype. 

    $statement = array( 
        "actor" => array(
            "mbox"=> "mailto:".$userEmail,
            "name"=> $userName
        ), 
        "verb" => array(
            "id" => "http://standard.openbadges.org/xapi/verbs/earned.json",
            "display" => array(
                "en-US" => "earned",
                "en-GB" => "earned",
            ),
        ),
        "object" => array(
            "id" =>  "http://example.com/badge/1", //TODO: id of badge goes here. 
            "definition" => array(
                "type" => "http://activitystrea.ms/schema/1.0/badge"
            )
        ),
        "context" => array(
            "extensions" => array(
                "http://standard.openbadges.org/xapi/extensions/badgeassertion.json" => array(
                    "@id" => "http://example.com/assertion/1" //TODO: URL of hosted badge assertion goes here
                )
            ),
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
    );

//TODO: add badge as attachment
//TODO: sign statement

        try {
            $lrs->saveStatement($statement);
        }
        catch (Exception $e) {
            //TODO: handle error
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
        <img ?>
        <form action="earn.php" method="post">
            <input type="hidden" class="form-control" id="name" name="name" value="<?php echo $userName ?>">
            <input type="hidden" class="form-control" id="email" name="email" value="<?php echo $userEmail ?>">
            <input type="hidden" class="form-control" id="badge" name="badge" value="badge1">
            <button type="submit" class="btn btn-primary">Earn!</button>
        </form>
    </div>
    <div class="col-md-4 panel panel-default">
        <?php include "badges.php"; ?>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-12 panel panel-default">
        <?php include "stream.php"; ?>
    </div>
</div>

<?
include "includes/foot.php";