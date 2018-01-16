<?php
include __dir__ . '/../config.php';
include __dir__ . '/../vendor/autoload.php';


use \Toolkit\{DbSession, Database};

new DbSession(new Database($cfg));

session_start();
$_SESSION['username'] = 'someuser';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sessions Tutorial - Page 1</title>
</head>
<body>
<ul>
    <li>Username: <?php echo $_SESSION['username']; ?></li>
    <li>Session Id: <?php echo session_id(); ?></li>
</ul>
<a href="SessionTest2.php">Go to page 2</a>
</body>
</html>