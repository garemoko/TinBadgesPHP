# TinBadgesPHP
A Tin Can Prototype for sending badges as attachments in signed statements.

This prototype implements the Open Badges profile outlined here: https://github.com/ht2/BadgesCoP 

## UI components

### index.php
An introduction to the prototype and links (or possibly includes TBC) for other pages. May collect user information and issue a signed statement (TBC). 

### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 

### stream.php
Displays a stream of badge statements including the badge image and signed statement verification. 

### badges.php
Displays all Open Badges earned by the user in a dashboard. These are downloadable. 

## Reource components

### assertions.php
Recieves querystring paramaters, querries the LRS for a matching statement and returns either assertion JSON or an error. 

### badge-class.php
Recieves querystring paramaters, returns hard coded badge class json.

### criteria.php
Recieves querystring paramaters, returns hard coded badge criteria as human readable plain text. 

### issuer-organization.json
An example issuer organization to be used in badges. 

## Libs