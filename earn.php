<?php

/*
### earn.php
A simple page allowing the user to click a button and earn a badge. Issues a signed statement with a badge attachment. 
Includes stream.php and badges.php as side/bottom blocks or links to them. 
*/

include 'includes/head.php';

$userEmail = $_POST["email"];
$userName = $_POST["name"];

//issue a statement to say the user launched this prototype. 

?>

<div class="row">
    <div class="col-md-8 panel panel-default">
        <h2>Claim your badge, <?php echo $userName ?></h2>
        <p>
            This page similuates a user (<?php echo $userEmail ?>) logged into an LMS earning a badge. Badges can be earned by completing 
            some kind of signnificant achievement, but today you'll earn a badge by clicking a button! 
        </p>
        <img ?>
        <form action="earn.php" method="post">
            <input type="hidden" class="form-control" id="name" name="name" value="<?php echo $userName ?>">
            <input type="hidden" class="form-control" id="email" name="email" value="<?php echo $userName ?>">
            <button type="submit" class="btn btn-primary">Earn!</button>
        </form>
    </div>
    <div class="col-md-4 panel panel-default">
        <?php include 'badges.php'; ?>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-12 panel panel-default">
        <?php include 'stream.php'; ?>
    </div>
</div>

<?
include 'includes/foot.php';