<?php

namespace Toolkit;

class User
{
    private $db;
    private $name = '';

    public function __construct(Database $db)
    {
        $this->db = $db;
        if (isset($_SESSION['username'])) {
            $this->name = $_SESSION['username'];
        }
    }

    public function isLoggedIn() {
        return ! empty($this->name);
    }

    public function saveAndLogin() {
        $this->name = $_SESSION['username'] = $_POST['username'];
        $passGen = new PasswordGenerator($_POST['password']);
        $parameters = array (
            'fields' => array ('username', 'password', 'email', 'url', 'dob'),
            'table' => 'userdetails',
            'records' => array (
                array (strtoupper($_POST['username']), $passGen->generateHash(), $_POST['email'], $_POST['url'],
                    date('Y-m-d', strtotime(str_replace('/', '-', $_POST['dob'])))
                )
            )
        );
        return $this->db->insert($parameters);
    }

    public function logOff() {
        $this->name = '';

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

    public function name() {
        return $this->name;
    }
}