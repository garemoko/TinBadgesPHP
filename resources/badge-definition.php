<?php

/*
### badge-class.php
Recieves querystring paramaters, returns hard coded badge class json.
*/
include "../config.php";
include "../includes/badge-definitions.php";
header('Content-Type: application/json');

if (isset($_GET["badge-id"])){
    $badgeId = $_GET["badge-id"];
} else {
    header("HTTP/1.1 400 Bad Request");
    http_response_code(400);
    die();
}


if (isset($badges[$badgeId])){
    echo json_encode($badgeList[$badgeId]["tinCanDefinition"], JSON_UNESCAPED_SLASHES);
} else {
    header("HTTP/1.1 404 Not Found");
    http_response_code(404);
    die();
}