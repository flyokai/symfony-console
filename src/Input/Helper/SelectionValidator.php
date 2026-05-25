<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Flyokai\SymfonyConsole\Input\ValidationException;

class SelectionValidator
{
    /**
     * @var string[]
     */
    protected array $keys;
    /**
     * @var string[]
     */
    protected array $values;

    protected string $selectionString;

    /**
     * @param array<string, string> $selectionOptions
     */
    public function __construct(
        protected array $selectionOptions
    )
    {
        $this->keys = array_keys($this->selectionOptions);
        $this->values = array_values($this->selectionOptions);
        $this->selectionString = implode(', ', array_map(fn ($k, $v) => "$k ($v)", $this->keys, $this->values));
    }

    public function validate(string|null $value): string
    {
        $value = strtolower(trim((string)$value));
        if (!in_array($value, array_keys($this->selectionOptions))) {
            throw new ValidationException(sprintf(
                'Only these values acceptable: %s',
                $this->selectionString()
            ));
        }

        return $value;
    }

    public function selectionString(): string
    {
        return $this->selectionString;
    }
}
