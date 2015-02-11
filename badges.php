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
    "format"=>"exact" //we don't need activity defitinions
);

if ($CFG->rebakeBadgeToDisplay) {
    $queryCFG["attachments"] = "false";
} else{
    $queryCFG["attachments"] = "true";
}

$queryStatementsResponse = $lrs->queryStatements($queryCFG);

$queryStatements = $queryStatementsResponse->content->getStatements();
$moreStatementsURL = $queryStatementsResponse->content->getMore(); 


while (!is_null($moreStatementsURL)){
    $moreStatementsResponse = $lrs->moreStatements($moreStatementsURL);
    $moreStatements = $moreStatementsResponse->content->getStatements();
    $moreStatementsURL = $moreStatementsResponse->content->getMore(); 

    //Note: due to the structure of the arrays, array_merge does not work as expected. 
    foreach ($moreStatements as $moreStatement){ 
        array_push($queryStatements, $moreStatement);
    }
}



//get the most recent earn of each badge
$unqiueEarnStatements = array();
foreach ($queryStatements as $queryStatement){
    $thisBadgeId = $queryStatement->getObject()->getId();
    if (!isset($unqiueEarnStatements[$thisBadgeId])){
        $unqiueEarnStatements[$thisBadgeId] = $queryStatement;
    }
}
/*
    In a live system, we might want to only consider statements with a verfiied signature. For this prototype, 
    that's not implemented in order to allow badges not generated by this prototype to be displayed. 
*/

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
            $badgeClass = json_decode(file_get_contents($unqiueEarnStatement->getObject()->getDefinition()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"]["@id"]));
            $badgeImageURL = $badgeClass->image;
            $displayBadge = bakeBadge($badgeImageURL, $assertion);
        } 

        echo "<img class='open-badge-100 pull-left' src='data:image/png;base64," . base64_encode($displayBadge) . "' />";
    }
?>