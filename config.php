<?php
global $CFG; 
$CFG = new stdClass();

$CFG->wwwroot = "http://localhost:8888/TinBadgesPHP";

//Tin Can config
$CFG->version = "1.0.0"; 
$CFG->endpoint = "http://cloud.scorm.com/ScormEngineInterface/TCAPI/public/";
$CFG->login = "";
$CFG->pass = "";

$CFG->readonly_login = "";
$CFG->readonly_pass = "";

//Statement Signing
$CFG->privateKeyPassPhrase = "magic"; //Note: if you change this, you will also need to create a new private key (privkey.pem) in the "signing" folder. 

//Open Badges
$CFG->badge_salt = "badge_salt";
$CFG->rebakeBadgeToDisplay= true; //If false get the badge from the attachment. 
?>
