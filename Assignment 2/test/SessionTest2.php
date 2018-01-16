<?php
include __DIR__ . '/../config.php';
include __DIR__ . '/../vendor/autoload.php';


use \Toolkit\{DbSession, Database};

global $cfg;
new DbSession(new Database($cfg));

session_start();

if(isset($_POST['kill_session'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
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
<form method="post">
    <p><input type="submit" name="kill_session" value="Kill Session"></p>
</form>
<a href="SessionTest.php">Go to page 1</a>
</body>
</html>