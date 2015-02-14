<?php

//TODO: consider giving the prototype user and interface to specify this data and even upload a badge image to the LRS. 
    // Note: this will need to consider how badge-definition.php will provide a canonical definition, or if that requirement
    // can be removed. badge-definition.php is currently used as the activity id for badges. 

$badgeList = array(
    "1" => array(
        "tinCanDefinition" =>  array(
            "name" => array("en-US"=>"Example Tin Badge number one"),
            "description" => array("en-US"=>"The first example Tin Badge"),
            "type" => "http://activitystrea.ms/schema/1.0/badge",
            "extensions" => array(
                "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                    "@id" => $CFG->wwwroot . "/resources/badge-class.php?activity-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=1"),
                    "image" => $CFG->wwwroot . "/resources/badge-image.php?activity-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=1"),
                    "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=1",
                    "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
                )
            )
        ),
        "sourceImage" => $CFG->wwwroot ."/badges/badge-one.png"
    ),
    "2" => array(
        "tinCanDefinition" => array(
            "name" => array("en-US"=>"Example Tin Badge number two"),
            "description" => array("en-US"=>"The second example Tin Badge"),
            "type" => "http://activitystrea.ms/schema/1.0/badge",
            "extensions" => array(
                "http://standard.openbadges.org/xapi/extensions/badgeclass.json" => array(
                    "@id" => $CFG->wwwroot . "/resources/badge-class.php?activity-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"),
                    "image" => $CFG->wwwroot . "/resources/badge-image.php?activity-id=" . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"),
                    "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=2",
                    "issuer" => $CFG->wwwroot ."/resources/issuer-organization.json"
                )
            )
        ),
        "sourceImage" => $CFG->wwwroot ."/badges/badge-two.png"
    )
);

