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
