<?php
/*
### badges.php
Displays all Open Badges earned by the user in a dashboard. These are downloadable. 
*/

//get all badge related statements about the current user.

$queryStatementsResponse = $lrs->queryStatements(
    array(
        "agent" => new \TinCan\Agent(array("mbox"=> "mailto:".$userEmail)),
        "verb" => new \TinCan\Verb(array("id"=> "http://standard.openbadges.org/xapi/verbs/earned.json")),
        "activity" => new \TinCan\Activity(array("id"=> "http://standard.openbadges.org/xapi/recipe/base/0")),
        "related_activities" => "true",
        "format"=>"ids" //we don't need activity defitinions
    )
);

$queryStatements = $queryStatementsResponse->content->getStatements();
$moreStatements = $queryStatementsResponse->content->getMore(); 

if (!is_null($moreStatements)){
    //TODO: fetch the more statements and add them on the end of the array
}

//get the most recent earn of each badge
$unqiueEarnStatements = array();
foreach ($queryStatements as $queryStatement){
    $thisBadgeId = $queryStatement->getObject()->getId();
    if (!isset($unqiueEarnStatements[$thisBadgeId])){
        $unqiueEarnStatements[$thisBadgeId] = $queryStatement;
    }
}

?>

<h3><?php echo $userName ?>'s Badges</h3>
<p>You have earned these badges:</p>

<?php 
    foreach ($unqiueEarnStatements as $unqiueEarnStatement){
        $attachedBadge = file_get_contents($CFG->wwwroot ."/badges/badge-one.png"); //TODO: get the badge attached to the statement.
        echo "<img class='open-badge-100 pull-left' src='data:image/png;base64," . base64_encode($attachedBadge) . "' />";
    }
?>