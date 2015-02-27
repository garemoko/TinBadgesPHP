# TinBadgesPHP
A Tin Can Prototype for sending badges as attachments in signed statements.

This prototype implements the Open Badges recipies outlined here: https://github.com/ht2/BadgesCoP 

## Setup
Install this prototype on a PHP server and complete config.php with your LRS and other settings. 

## Statement Signing
This prototype digitally signs all statements and verifies the signatures of retireved statements. The following
files are included within the 'signing' directory to support this proceess:

### privkey.pem
A private key used to sign statements. In production systems, this private key should be stored at a location that is
not accessible from outside. 

### cacert.pem
A public certificate to be used by systems to verify signed statements. This certificate corressponds to the privkey.pem
private key. 

### hackerkey.pem
Another private key like privkey.pem. This private key does not correspond to the cacert.pem public certificate. It is the 'wrong' private key. Attempts to verify statements signed with hackerkey.pem using cacert.pem will fail. 

## UI components

### index.php
An introduction to the prototype. Collects user information, links to earn.php 

### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 
Includes stream.php and badges.php as side/bottom blocks or links to them. 

### stream.php
Displays a stream of badge statements including an Open Badges Conformant badge image and signed statement verification. 

### badges.php
Displays all Open Badges earned by the user in a dashboard. These are downloadable, Open Badges Conformant, Badges. 

## Resource components
The below resources are called by HTTP request. They each create a specific LRS query based on the 
parameters passed to them and their role and return data to the requestor. These resources are not
specific to the specific functionality of this prototype and systems implementing the Open Badges recipe
may wish to mirror these resources and/or their functionality. 

### assertions.php
Recieves statement id querystring paramater, querries the LRS for a matching statement and returns 
either assertion JSON or an error.

### attached-badge.php
Recieves statement id querystring paramater, querries the LRS for a matching statement and 
returns the first Open Badge attachment as a base 64 encoded png image. 

### badge-class.php
Recieves badge activity id, returns badge class json.

### badge-image.php
Recieves badge activity id, returns badge source image. 

### criteria.php
Receives a badge id, returns hard coded badge criteria as human readable plain text. This are
needs further specification and development. 

### issuer.php
Recieves issuer activity id, returns isuser json.

### verify-signed-statement.php
Recieves statement id and public key querystring paramaters, queries the LRS for a matching statement, 
verifies that statement and returns a success status.

## Contact
info@tincanapi.com
http://tincanapi.com/