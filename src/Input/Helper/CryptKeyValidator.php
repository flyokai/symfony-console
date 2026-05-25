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
            new \Flyokai\Misc\CryptKey($value, $this->passPhrase, $this->keyPermissionsCheck);
        } catch (\LogicException $e) {
            throw new ValidationException($e->getMessage());
        }
        return $value;
    }
    /**
     * @param bool $keyPermissionsCheck pass false for public material (e.g. SSL cert,
     *     public RSA/EC key) where the strict 600/640/660 mode check is inappropriate.
     */
    public static function createValidatorFactory(?string $passOption=null, bool $keyPermissionsCheck=true): \Closure
    {
        return function (InputInterface $input) use ($passOption, $keyPermissionsCheck) {
            return self::createValidatorFromInput($input, $passOption, $keyPermissionsCheck);
        };
    }
    public static function createValidatorFromInput(InputInterface $input, ?string $passOption=null, bool $keyPermissionsCheck=true): \Closure
    {
        return self::createFromInput($input, $passOption, $keyPermissionsCheck)->validate(...);
    }
    public static function createFromInput(InputInterface $input, ?string $passOption=null, bool $keyPermissionsCheck=true): self
    {
        $passPhrase = null;
        if ($passOption && !empty($input->getOption($passOption))) {
            $passPhrase = $input->getOption($passOption);
        }
        return new self(passPhrase: $passPhrase, keyPermissionsCheck: $keyPermissionsCheck);
    }
}
