<?php
/*
### badges.php
Displays all Open Badges earned by the user in a dashboard. These are downloadable. 
*/

//get all badge related statements about the current user.

$queryCFG = array(
    "agent" => new \TinCan\Agent(array("mbox"=> "mailto:".$userEmail)),
    "verb" => new \TinCan\Verb(array("id"=> "http://standard.openbadges.org/xapi/verbs/earned.json")),
    "activity" => new \TinCan\Activity(array("id"=> "http://standard.openbadges.org/xapi/recipe/base/0")),
    "related_activities" => "true",
    //"limit" => 1, //Use this to test the "more" statements feature
    "format"=>"canonical" 
);

if ($CFG->rebakeBadgeToDisplay) {
    $queryCFG["attachments"] = "false";
} else{
    $queryCFG["attachments"] = "true";
}

$options = array(
    'headers' => array(
        'Accept-language: ' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] . ', *'
    )
);

//get the most recent earn of each badge
$unqiueEarnStatements = $lrs->getStatementsWithUniqueActivitiesFromStatementQuery($queryCFG, $options);

?>

<h3><?php echo $userName ?>'s Badges</h3>
<p>You have earned these badges:</p>

<?php 
    foreach ($unqiueEarnStatements as $unqiueEarnStatement){
        $displayBadge = null;
        if (!$CFG->rebakeBadgeToDisplay && $unqiueEarnStatement->getAttachments() != null) {
            foreach ($unqiueEarnStatement->getAttachments() as $attachment){
                if ($attachment->getUsageType() == "http://standard.openbadges.org/xapi/attachment/badge.json"){
                    $displayBadge = $attachment->getContent(); 
                }
            }
        }

        if ($displayBadge == null) {
            $assertion = statementToAssertion($unqiueEarnStatement);
            $opts = array(
                  'http'=>array(
                        'header'=>"Accept-language: ". $_SERVER['HTTP_ACCEPT_LANGUAGE']
                  )
            );
            $context = stream_context_create($opts);
            $badgeClass = json_decode(file_get_contents($unqiueEarnStatement->getObject()->getDefinition()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"]["@id"], false, $context));
            $badgeImageURL = $badgeClass->image;
            $displayBadge = bakeBadge($badgeImageURL, $assertion);
        } 

        echo "<img class='open-badge-100 pull-left' src='data:image/png;base64," . base64_encode($displayBadge) . "' />";
    }
?>