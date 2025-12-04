<?php

namespace Flyokai\SymfonyConsole\InputDefinition;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Completion\Suggestion;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

class RequiredOption extends InputOption
{
    /**
     * @param string|string[]|null                $shortcut
     * @param int|null                            $mode
     * @param string|bool|int|float|string[]|null $default
     * @param list<string>|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues
     * @param mixed|\Closure(InputInterface):string $backupValue
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
        protected mixed $backupValue = null,
        protected array $questionAmplifiers = []
    )
    {
        parent::__construct($name, $shortcut, $mode, $description, $default, $suggestedValues);
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

    /**
     * @param mixed|\Closure(InputInterface):string $backupValue
     * @return $this
     */
    public function setBackupValue(mixed $backupValue): RequiredOption
    {
        $this->backupValue = $backupValue;
        return $this;
    }
}
