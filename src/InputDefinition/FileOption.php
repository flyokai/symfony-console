<?php

namespace Flyokai\SymfonyConsole\InputDefinition;

use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Completion\Suggestion;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class FileOption extends RequiredOption
{
    /**
     * @param string|string[]|null                $shortcut
     * @param int|null                            $mode
     * @param string|bool|int|float|string[]|null $default
     * @param list<string>|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues
     * @param array<string, array<string, mixed>> $questionAmplifiers
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     */
    public function __construct(
        string $name,
        mixed $shortcut = null,
        ?int $mode = null,
        string $description = '',
        mixed $default = null,
        mixed $suggestedValues = [],
        mixed $backupValue = null,
        array $questionAmplifiers = [],
        protected bool $isWritable = true,
        protected bool $replaceExisting = false,
        protected bool $shouldExist = false,
    )
    {
        parent::__construct(
            $name, $shortcut, $mode, $description, $default, $suggestedValues, $backupValue, $questionAmplifiers
        );
    }

    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    public function isReplaceExisting(): bool
    {
        return $this->replaceExisting;
    }

    public function shouldExist(): bool
    {
        return $this->shouldExist;
    }
}
