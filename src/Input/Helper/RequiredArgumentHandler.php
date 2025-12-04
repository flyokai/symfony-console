<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Flyokai\SymfonyConsole\Input\InputArgumentException;
use Flyokai\SymfonyConsole\Input\QuestionFactory;
use Flyokai\SymfonyConsole\Input\InputState as InputState;
use Flyokai\SymfonyConsole\InputDefinition\RequiredArgument;

class RequiredArgumentHandler
{
    protected QuestionHelper $question;
    protected InputInterface $input;
    protected InputDefinition $inputDefinition;
    protected OutputInterface $output;

    /**
     * @param InputState $state
     */
    public function __construct(
        protected InputState $state
    )
    {
        $this->input = $this->state->input;
        $this->output = $this->state->output;
        $this->question = $this->state->question;
        $this->inputDefinition = $this->state->inputDefinition;
    }

    public function handleArgument(RequiredArgument $argument): void
    {
        if ($argument->isInputRequired()) {
            $this->handleRequiredArgument($argument);
        }
    }

    public function handleRequiredArgument(RequiredArgument $argument): void
    {
        $argName = $argument->getName();
        $value = $this->input->getArgument($argName);
        if (!$value) {
            if ($this->input->isInteractive()) {
                $newValue = $this->question->ask($this->input, $this->output, QuestionFactory::requiredQuestion(
                    sprintf('Please enter "%s":', $argument->getDescription()),
                    amplifiers: $argument->getQuestionAmplifier('input')
                ));
                if ($argument->isArray()) {
                    $newValue = [$newValue];
                }
                $this->input->setArgument($argName, $newValue);
            } else {
                throw InputArgumentException::missingRequiredArgument($argName);
            }
        }
        $this->validateArgument($argument);
    }

    /**
     * @param string[] $argumentNames
     * @return void
     */
    public function handleArguments(array $argumentNames): void
    {
        foreach ($argumentNames as $argumentName) {
            $argument = $this->inputDefinition->getArgument($argumentName);
            if ($argument instanceof RequiredArgument) {
                $this->handleArgument($argument);
            }
        }
    }

    protected function validateArgument(RequiredArgument $argument): void
    {
        if (($amplifier = $argument->getQuestionAmplifier('input'))
            && isset($amplifier['validator'])
        ) {
            ($amplifier['validator'])($this->input->getArgument($argument->getName()));
        }
    }
}
