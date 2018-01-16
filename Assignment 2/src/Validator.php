<?php

namespace Toolkit;


abstract class Validator {

    protected $errorMessage = '';
    protected $value;
    protected $required;

    /**
     * EmailValidator constructor.
     * @param String $value
     * @param bool $required
     */
    public function __construct(String $value, bool $required = false)
    {
        $this->value = trim($value);
        $this->required = $required;

        if (empty($this->value)) {
            if ($this->required) {
                $this->errorMessage = "This is a required field";
            }
        } else {
            $this->validate();
        }
    }

    abstract public function validate();

    public function hasError()
    {
        return ! empty($this->errorMessage);
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function getSanitisedValue()
    {
        return htmlentities($this->value, ENT_QUOTES);
    }
}