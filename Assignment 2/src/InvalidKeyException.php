<?php

namespace Toolkit;


use Throwable;

class InvalidKeyException extends \Exception {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (! $message) {
            $message = "Key does not exist.";
        }
        parent::__construct($message, $code, $previous);
    }
}