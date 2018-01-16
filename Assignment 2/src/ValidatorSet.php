<?php

namespace Toolkit;


class ValidatorSet extends Collection {

    /**
     * @param $validator
     * @param null $key
     * @throws \Exception if type Validator object not passed as a parameter
     */
    public function addItem($validator, $key = null) {
        if ($validator instanceof Validator) {
            parent::addItem($validator, $key);
        } else {
            throw new \Exception("Object of type Validator expected.");
        }
    }

    public function getErrors() {
        $errors = array();
        foreach ($this->getValuesAsArray() as $key=>$validator) {
            if ($validator->hasError()) {
                $errors[$key] = $validator->getError();
            }
        }
        return $errors;
    }
}