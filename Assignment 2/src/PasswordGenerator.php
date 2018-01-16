<?php
/**
 * Created by PhpStorm.
 * User: Kris
 * Date: 19/12/2017
 * Time: 11:03
 */

namespace Toolkit;


class PasswordGenerator
{
    private $password = '';
    private $hash='';

    public function __construct($pass)
    {
        $this->password = $pass;
    }

    public function generateHash() {
        $this->hash =  password_hash($this->password, PASSWORD_BCRYPT);
        return $this->hash;
    }

    public function verifyPassword() {
        if (password_verify($this->password, $this->hash)) {
            return true;
        } else {
            return false;
        }
    }

}