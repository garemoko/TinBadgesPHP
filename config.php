<?php
global $CFG; 
$CFG = new stdClass();

$CFG->endpoint = "http://cloud.scorm.com/ScormEngineInterface/TCAPI/public/";
$CFG->login = "";
$CFG->pass = "";
$CFG->version = "1.0.0"; 

$CFG->readonly_login = "";
$CFG->readonly_pass = "";

$CFG->wwwroot = "http://localhost:8888/TinBadgesPHP";

$CFG->badge_salt = "badge_salt";

$CFG->rebakeBadgeToDisplay= true; //If false get the badge from the attachment.  

?>
