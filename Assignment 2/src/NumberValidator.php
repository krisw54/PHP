<?php

namespace Toolkit;


class NumberValidator extends Validator {

    public function validate()
    {
        if ($this->value < 21 || $this->value > 44) {
            $this->errorMessage = "Number must be between 21 and 44 inclusive.";
        }
    }
}