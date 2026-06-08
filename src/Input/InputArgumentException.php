<?php

namespace Flyokai\SymfonyConsole\Input;

class InputArgumentException extends \Exception
{
    public function __construct(
        public readonly string $argumentName,
        string                 $message = "",
        int                    $code = 0,
        ?\Throwable            $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    public static function missingRequiredArgument(string $argumentName): self
    {
        return new self($argumentName, sprintf('Missing required argument: "%s"', $argumentName));
    }
}
