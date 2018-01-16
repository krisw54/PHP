<?php

namespace Toolkit;

/**
 * Class EmailValidator
 * @package Toolkit
 */
class UrlValidator extends Validator {

    public function validate()
    {
        if (! filter_var($this->value, FILTER_VALIDATE_URL)) {
            $this->errorMessage = "The URL was not of the correct format.";
        }

        $pos = strrpos($this->value, ".");

        if ($pos === false) {
            $this->errorMessage = "The URL was not of the correct format, no domain detected.";
        } else {
            if (strlen(substr($this->value, $pos)) <= 2) {
                $this->errorMessage = "The URL was not of the correct format, the domain isn't correct";
            }
        }
    }
}