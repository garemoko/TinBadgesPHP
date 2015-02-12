<?php
//TODO: consider storing badge class data in the LRS either as a statement or in the Activity Profile API

$badgeDefinitions = array(
    "1" => array(
        "name" => array("en"=>"Example Tin Badge number one"),
        "description" => array("en"=>"The first example Tin Badge"),
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