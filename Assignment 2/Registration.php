<?php

require 'vendor/autoload.php';

$cfg = require_once 'config.php';

use \Toolkit\{
    Database, DbSession,
    ValidatorSet, EmailValidator, TextValidator, UrlValidator, DateValidator, DobValidator, UsernameValidator,
    User, FormToken
};

$db = new Database($cfg);
new DbSession($db);
new DbSession(new Database($cfg));

function create($field, $value) {
    switch ($field) {
        case 'username':
            return new UsernameValidator($value,true);
        case 'password':
            return new TextValidator($value, true);
        case 'email':
            return new EmailValidator($value, true);
        case 'url':
            return new UrlValidator($value);
        case 'dob':
            return new DobValidator($value, true);
        default:
            throw new Exception('Validator not found');
    }
}

$errorMessage = $usernameError = $passwordError = $emailError = $urlError = $dobError =
                $username = $email = $url = $dob = '';


session_start();

//Setup token system for security
$token = new FormToken();

$user = new User($db);

//Check for multiple submissions
if (isset($_COOKIE['FormSubmitted']) && !$user->isLoggedIn())
{
    die('It looks like you have already submitted this form!');
}

if($_POST) {
    if (isset($_POST['submit'])) {
        $expected = array('username', 'password', 'email', 'url', 'dob');

        //Setup allowed list
        $allowed = $expected;
        array_push($allowed,"submit", "token");

        //Check for valid fields
        foreach ($_POST as $key=>$item) {
            if (!in_array($key, $allowed)) {
                die("Hack attempt detected! Please use only the fields in the form");
            }
        }

        $validators = new ValidatorSet();

        foreach ($expected as $field) {
            $validators->addItem(create($field, $_POST[$field]), $field,$db);
        }

        $errors = $validators->getErrors();
        if ($errors) {
            foreach ($expected as $field) {
                if (isset($errors[$field])) {
                    ${$field . 'Error'} = '<span class="error">' . $errors[$field] . '</span>';
                }

                if ($field != 'password') {
                    ${$field} = 'value="' . $_POST[$field]. '"';
                }
            }

            $errorMessage = "There have been errors. Please review.";
        } else {
            if ($token->verifyFormToken('reg')) {
                if (!$user->saveAndLogin()) {
                    $errorMessage = "Something went wrong. Try a different username";
                }
                setcookie('FormSubmitted', '1');
            } else {
                echo "Hack attempt detected!";
            }
        }
    } else {
        $user->logOff();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registration Form</title>
    <style>
        fieldset {padding:3px}
        input {display:block; margin: 0 auto 10px auto}
        label {display:block; margin-bottom: 2px}
        button {display: block; margin: 5px 0}
        section {width:250px}
        .error { color: red; }
    </style>
</head>
<body>
<h1>Assignment Task 2 - Registration Form</h1>
<section>
    <form method="post" <?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>>
        <?php if ($user->isLoggedIn()) : ?>

        <button type="submit" name="logOff">Log Off</button>
        <p><a href="Secure.php">Secure Page</a></p>

        <?php else: ?>

        <fieldset>
            <legend>Enter your registration details</legend>

            <label for="username">Username: </label> <?= $usernameError; ?>
            <input type="text" maxlength="20" required name="username" id="username" <?= $username; ?>>

            <label for="password">Password: </label> <?= $passwordError; ?>
            <input type="password" required name="password" id="password">

            <label for="email">Email: </label> <?= $emailError; ?>
            <input type="email" required name="email" id="email" <?= $email; ?>>

            <label for="url">Webpage URL: </label> <?= $urlError; ?>
            <input type="url" name="url" id="url" <?= $url; ?>>

            <label for="dob">Date of birth: </label> <?= $dobError; ?>
            <input type="date" required name="dob" id="dob" <?= $dob; ?>>
        </fieldset>
            <input type="hidden" name="token" value="<?php echo $token->generateFormToken('reg'); ?>">
        <button type="submit" name="submit" formnovalidate>Submit Details</button>
        <p><?= $errorMessage ?></p>

        <?php endif; ?>
    </form>
</section>
</body>
</html>
