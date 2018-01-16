<?php

namespace Toolkit;

class DobValidator extends Validator {

    public function validate()
    {
        $dob = date('Y-m-d', strtotime(str_replace('/', '-', $this->value)));
        $dateNow =  date("Y-m-d");

        if ($dob > $dateNow) {
            $this->errorMessage = "Sorry! You have entered a date in the future.";
        }
        else if (time() < strtotime('+18 years', strtotime($dob))) {
            $this->errorMessage = "Sorry! You have to be over the age of 18 to register.";
        }
    }
}
