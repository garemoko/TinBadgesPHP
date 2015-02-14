<?php
global $CFG; 
$CFG = new stdClass();

$CFG->wwwroot = "http://localhost:8888/TinBadgesPHP";

//Tin Can config
$CFG->version = "1.0.0"; 
$CFG->endpoint = "https://cloud.scorm.com/tc/LP9LRJRM6M/sandbox/";
$CFG->login = "YvIMOiHt7Gch50Eq1MI";
$CFG->pass = "SrSum1dBYi4ysmac9v0";

$CFG->readonly_login = "YvIMOiHt7Gch50Eq1MI";
$CFG->readonly_pass = "SrSum1dBYi4ysmac9v0";

//Statement Signing
$CFG->privateKeyPassPhrase = "magic"; //Note: if you change this, you will also need to create a new private key (privkey.pem) in the "signing" folder. 

//Open Badges
$CFG->badge_salt = "badge_salt";
$CFG->rebakeBadgeToDisplay= true; //If false get the badge from the attachment. 
?>
