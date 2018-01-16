<?php

namespace Toolkit;

/**
 * Class EmailValidator
 * @package Toolkit
 */
class UsernameValidator extends Validator
{


    public function validate()
    {
        if (strlen($this->value) > 20) {
            $this->errorMessage = 'Name has too many characters. Must be 20 or less.';
        } else if($this->checkUsernameAlreadyExists() == !null) {
            $this->errorMessage = 'Username already exists please choose another one.';
        }

    }

    private function checkUsernameAlreadyExists()
    {
        $cfg = require __DIR__ . '/../config.php';
        $db = new Database($cfg);
        new DbSession($db);
        $parameters = array(
            'fields' => array('username'),
            'table' => 'userdetails',
            'conditions' => array(
                array('username' => strtoupper($this->value), 'join' => '=')
            )
        );
        return $db->select($parameters);
    }

}