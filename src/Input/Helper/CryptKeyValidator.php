<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Flyokai\SymfonyConsole\Input\ValidationException;

class CryptKeyValidator
{
    public function __construct(
        protected readonly ?string $passPhrase = null,
        protected readonly bool $keyPermissionsCheck = true
    )
    {
    }
    public function validate(string|null $value): string
    {
        try {
            Assertions::assertNotNull($value);
            if (realpath($value)) {
                $value = realpath($value);
            }
            new \Wtsergo\Misc\CryptKey($value, $this->passPhrase, $this->keyPermissionsCheck);
        } catch (\LogicException $e) {
            throw new ValidationException($e->getMessage());
        }
        return $value;
    }
    public static function createValidatorFactory(?string $passOption=null): \Closure
    {
        return function (InputInterface $input) use ($passOption) {
            return self::createValidatorFromInput($input, $passOption);
        };
    }
    public static function createValidatorFromInput(InputInterface $input, ?string $passOption=null): \Closure
    {
        return self::createFromInput($input, $passOption)->validate(...);
    }
    public static function createFromInput(InputInterface $input, ?string $passOption=null): self
    {
        $passPhrase = null;
        if ($passOption && !empty($input->getOption($passOption))) {
            $passPhrase = $input->getOption($passOption);
        }
        return new self(passPhrase: $passPhrase);
    }
}
