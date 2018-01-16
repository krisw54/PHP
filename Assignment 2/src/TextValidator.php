<?php

namespace Toolkit;

/**
 * Class EmailValidator
 * @package Toolkit
 */
class TextValidator extends Validator {

    public function validate()
    {
        if (strlen($this->value) > 20) {
            $this->errorMessage = 'Password has too many characters. Must be 20 or less.';
        }
    }

}