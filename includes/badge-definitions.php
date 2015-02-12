<?php

//TODO: consider allowing the prototype user to specify this data and even upload a badge image to the LRS. 

$badgeDefinitions = array(
    "1" => array(
        "name" => array("en"=>"Example Tin Badge number one", "fr"=>"Exemple Tin Badge numéro un"),
        "description" => array("en"=>"The first example Tin Badge", "fr"=>"Le premier exemple Tin Badge"),
        "type" => "http://activitystrea.ms/schema/1.0/badge",
        "extensions" => array(
            "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=1"
            )
        )
    ),
    "2" => array(
        "name" => array("en"=>"Example Tin Badge number two"),
        "description" => array("en"=>"The second example Tin Badge"),
        "type" => "http://activitystrea.ms/schema/1.0/badge",
        "extensions" => array(
            "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=2"
            )
        )
    )
);

//TODO: store the badge class specifc data in the Activity Profile API
$badgeClassData = array(
    "1" => array(
        "image" => $CFG->wwwroot ."/badges/badge-one.png",
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=1",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    ),
    "2" => array(
        "image" => $CFG->wwwroot ."/badges/badge-two.png",
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=2",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    )
);