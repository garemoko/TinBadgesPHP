<?php
/*
Copyright 2015 Rustici Software

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

//TODO: consider giving the prototype user and interface to specify this data and even upload a badge image to the LRS. 
    // Note: this will need to consider how badge-definition.php will provide a canonical definition, or if that
    // requirement can be removed. badge-definition.php is currently used as the activity id for badges.

$badgeList = array(
    "1" => array(
        "tinCanDefinition" =>  array(
            "name" => array("en-US"=>"Example Tin Badge number one"),
            "description" => array("en-US"=>"The first example Tin Badge"),
            "type" => "http://activitystrea.ms/schema/1.0/badge",
            "extensions" => array(
                "http://specification.openbadges.org/xapi/extensions/badgeclass.json" => array(
                    "@id" => $CFG->wwwroot . "/resources/badge-class.php?activity-id="
                        . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=1"),
                    "image" => $CFG->wwwroot . "/resources/badge-image.php?activity-id="
                        . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=1"),
                    "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=1",
                    "issuer" => $CFG->wwwroot ."/resources/issuer.php?activity-id=" . urlencode("http://tincanapi.com")
                )
            )
        ),
        "sourceImage" => $CFG->wwwroot ."/badges/badge-one.png",
        "badgeCriteria" => array(
            array(
                "verb" => new \TinCan\Verb(array("id"=> "http://adlnet.gov/expapi/verbs/experienced")),
                "activity" => new \TinCan\Activity(array("id"=> $CFG->wwwroot)),
            ),
            array(
                "verb" => new \TinCan\Verb(array("id"=> "http://adlnet.gov/expapi/verbs/interacted")),
                "activity" => new \TinCan\Activity(array("id"=> $CFG->wwwroot . "/buttons?"
                    . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"))),
            )
        )
    ),
    "2" => array(
        "tinCanDefinition" => array(
            "name" => array("en-US"=>"Example Tin Badge number two"),
            "description" => array("en-US"=>"The second example Tin Badge"),
            "type" => "http://activitystrea.ms/schema/1.0/badge",
            "extensions" => array(
                "http://specification.openbadges.org/xapi/extensions/badgeclass.json" => array(
                    "@id" => $CFG->wwwroot . "/resources/badge-class.php?activity-id="
                        . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"),
                    "image" => $CFG->wwwroot . "/resources/badge-image.php?activity-id="
                        . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"),
                    "criteria" => $CFG->wwwroot ."/resources/criteria.php?badge-id=2",
                    "issuer" => $CFG->wwwroot ."/resources/issuer.php?activity-id=" . urlencode("http://tincanapi.com")
                )
            )
        ),
        "sourceImage" => $CFG->wwwroot ."/badges/badge-two.png",
        "badgeCriteria" => array(
            array(
                "verb" => new \TinCan\Verb(array("id"=> "http://adlnet.gov/expapi/verbs/experienced")),
                "activity" => new \TinCan\Activity(array("id"=> $CFG->wwwroot)),
            ),
            array(
                "verb" => new \TinCan\Verb(array("id"=> "http://adlnet.gov/expapi/verbs/interacted")),
                "activity" => new \TinCan\Activity(array("id"=> $CFG->wwwroot . "/buttons?"
                    . urlencode($CFG->wwwroot . "/resources/badge-defintion.php?badge-id=2"))),
            )
        )
    )
);
