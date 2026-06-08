<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Flyokai\SymfonyConsole\Input\ValidationException;

class Assertions extends \Flyokai\DataMate\Helper\Assertions
{
    protected static function createValidationException($message)
    {
        return new ValidationException($message);
    }
}
