<?php

namespace Toolkit;


class NameValidator extends Validator {

    public function validate()
    {
        if (strlen($this->value) > 25) {
            $this->errorMessage = 'Name has too many characters. Must be 25 or less.';
        }
    }
}