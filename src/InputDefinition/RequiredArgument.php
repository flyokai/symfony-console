<?php

namespace Flyokai\SymfonyConsole\InputDefinition;

use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Completion\Suggestion;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class RequiredArgument extends InputArgument
{
    /**
     * @param string $name
     * @param int|null $mode
     * @param string $description
     * @param string|bool|int|float|array|null $default
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues
     * @param mixed|\Closure(InputInterface):string $backupValue
     * @param array<string, array<string, mixed>> $questionAmplifiers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $name,
        ?int $mode = null,
        string $description = '',
        float|array|bool|int|string|null $default = null,
        array|\Closure $suggestedValues = [],
        protected bool $inputRequired = true,
        protected mixed $backupValue = null,
        protected array $questionAmplifiers = []
    )
    {
        parent::__construct($name, $mode, $description, $default, $suggestedValues);
    }

    /**
     * @param string $name
     * @return array<string, mixed>
     */
    public function getQuestionAmplifier(string $name): array
    {
        return array_key_exists($name, $this->questionAmplifiers) ? $this->questionAmplifiers[$name] : [];
    }

    public function getBackupValue(InputInterface $input): mixed
    {
        return $this->backupValue instanceof \Closure ? ($this->backupValue)($input) : $this->backupValue;
    }

    public function isInputRequired(): bool
    {
        return $this->inputRequired;
    }

}
