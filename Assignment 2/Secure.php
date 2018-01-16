<?php

require 'vendor/autoload.php';

$cfg = require_once 'config.php';

use \Toolkit\{
    Database, DbSession,
    User
};

$db = new Database($cfg);
new DbSession($db);

session_start();

$user = new User($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Secure Page</title>
    <style>
        section {width:250px}
    </style>
</head>
<body>
<h1>Assignment Task 2 - Secure Page</h1>
<section>
    <?php if ($user->isLoggedIn()) : ?>
    <p>Welcome <?= $user->name(); ?>. This is a secure page.</p>
    <?php else: ?>
    <p>You are not authorised to view content.</p>
    <?php endif; ?>
    <p><a href="Registration.php">Registration Page</a></p>
</section>
</body>
</html>
