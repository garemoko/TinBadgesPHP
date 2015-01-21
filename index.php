<?php

/*
### index.php
An introduction to the prototype. Collects user information, links to earn.php 
*/
include 'includes/head.php';
?>

<p>The TinBadges prototype illsutrates statement signing, attachments and Open Badges. 
Get started by entering user details below:
</p>

<form action="earn.php" method="post">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="form-group">
        <label for="email">Email address:</label>
        <input type="text" class="form-control" id="email" name="email">
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?
include 'includes/foot.php';