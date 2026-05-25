<?php

namespace Flyokai\SymfonyConsole\Input;

class InputOptionException extends \Exception
{
    public function __construct(
        public readonly string $optionName,
        string                 $message = "",
        int                    $code = 0,
        ?\Throwable            $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    public static function missingRequiredOption(string $optionName): self
    {
        return new self($optionName, sprintf('Missing required option: "%s"', $optionName));
    }
    public static function validationError(string $optionName, string $message, ?\Throwable $previous = null): self
    {
        return new self(
            optionName: $optionName,
            message: sprintf('Option "%s" validation error: %s', $optionName, $message),
            previous: $previous,
        );
    }
}
