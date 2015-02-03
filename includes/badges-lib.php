<?php

/*
### badges-lib.php
Functions relating to baking and signing of badges. 
*/

function statementToAssertion($statement){
    global $CFG;
    $assertion = array(
        "uid" => $statement->getId(),
        "recipient" => array(
            "identity" => 'sha256$' . hash('sha256', substr($statement->getActor()->getMbox(),7) . $CFG->badge_salt),
            "type" => "email",
            "hashed" => true,
            "salt" => $CFG->badge_salt
        ),
        "badge" => $statement->getObject()->getDefinition()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"]["@id"],
        "verify" => $statement->getResult()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeassertion.json"]["@id"],
        "issuedOn" => $statement->getTimestamp()
        //TODO: (optional) "evidence" =>,
    );
    return $assertion;
}