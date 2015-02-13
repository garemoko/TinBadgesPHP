<?php

//TODO: consider giving the prototype user and interface to specify this data and even upload a badge image to the LRS. 

$badgeDefinitions = array(
    "3" => array(
        "name" => array("en-US"=>"Example Tin Badge number one"),
        "description" => array("en-US"=>"The first example Tin Badge"),
        "type" => "http://activitystrea.ms/schema/1.0/badge",
        "extensions" => array(
            "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=3")
            )
        )
    ),
    "2" => array(
        "name" => array("en"=>"Example Tin Badge number two"),
        "description" => array("en"=>"The second example Tin Badge"),
        "type" => "http://activitystrea.ms/schema/1.0/badge",
        "extensions" => array(
            "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                "@id" => $CFG->wwwroot . "/resources/badge-class.php?badge-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2")
            )
        )
    )
);

$badgeClassData = array(
    "3" => array(
        "image" => $CFG->wwwroot . "/resources/badge-image.php?badge-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=3"),
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=3",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    ),
    "2" => array(
        "image" => $CFG->wwwroot . "/resources/badge-image.php?badge-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"),
        "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=2",
        "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
    )
);

$badgeImages = array(
    "3" => $CFG->wwwroot ."/badges/badge-one.png",
    "2" => $CFG->wwwroot ."/badges/badge-two.png"
);

