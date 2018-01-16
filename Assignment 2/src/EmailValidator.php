<?php

namespace Toolkit;


/**
 * Class EmailValidator
 * @package Toolkit
 */
class EmailValidator extends Validator {

    public function validate()
    {
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = "The email was not of the correct format.";
        }
    }
}