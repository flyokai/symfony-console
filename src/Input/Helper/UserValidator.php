<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Flyokai\DataMate\ValidationException;

class UserValidator
{
    public const PASS_MIN_LENGTH = 8;
    public const UNAME_MIN_LENGTH = 3;

    /**
    * @throws ValidationException
    */
    public static function validatePassword(string|null $value): string
    {
        Assertions::assertNotNull($value);
        if (!preg_match('/^\S+$/', $value)) {
            throw new ValidationException('Spaces are not allowed in password');
        }
        if (strlen($value) < self::PASS_MIN_LENGTH) {
            throw new ValidationException(sprintf(
                'Password must be at least %s characters long', self::PASS_MIN_LENGTH
            ));
        }
        return $value;
    }

    public static function validateUsername(string|null $value): string
    {
        Assertions::assertNotNull($value);
        if (!preg_match('/^[0-9a-zA-Z-_]+$/', $value)) {
            throw new ValidationException('Username has invalid characters');
        }
        if (strlen($value) < self::UNAME_MIN_LENGTH) {
            throw new ValidationException(sprintf(
                'Username must be at least %s characters long', self::UNAME_MIN_LENGTH
            ));
        }
        return $value;
    }

    public static function validateEmail(string|null $value): string
    {
        Assertions::assertNotNull($value);
        $validator = new \Laminas\Validator\EmailAddress();
        if (!$validator->isValid($value)) {
            throw new ValidationException(implode('; ', $validator->getMessages()));
        }
        return $value;
    }
}
