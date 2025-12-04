<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Flyokai\SymfonyConsole\Input\InputOptionException;
use Flyokai\SymfonyConsole\Input\QuestionFactory;
use Flyokai\SymfonyConsole\Input\InputState as InputState;
use Flyokai\SymfonyConsole\InputDefinition\RequiredOption;

class RequiredOptionHandler
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

    /**
     * @param RequiredOption $option
     * @return void
     *
     * @throws InputOptionException
     */
    public function handleOption(RequiredOption $option): void
    {
        if ($option->acceptValue() && !$option->isNegatable()) {
            $option->isValueRequired()
                ? $this->handleRequiredOption($option)
                : $this->handleOptionalOption($option);
        }
    }

    public function handleRequiredOption(RequiredOption $option): void
    {
        $optName = $option->getName();
        $value = $this->input->getOption($optName);
        if (!$value) {
            if ($this->input->isInteractive()) {
                $inputAmplifiers = $option->getQuestionAmplifier('input');
                $inputTemplate = $inputAmplifiers['template'] ?? 'Please enter "%s":';
                $newValue = $this->question->ask($this->input, $this->output, QuestionFactory::requiredQuestion(
                    sprintf($inputTemplate, $option->getDescription()),
                    amplifiers: $inputAmplifiers
                ));
                $this->input->setOption($optName, $newValue);
            } else {
                throw InputOptionException::missingRequiredOption($optName);
            }
        }
        $this->validateOption($option);
    }

    /**
     * @throws InputOptionException
     */
    public function handleOptionalOption(RequiredOption $option): void
    {
        $optName = $option->getName();
        $value = $this->input->getOption($optName);
        $backupValue = $option->getBackupValue($this->input);
        if ($value === null) {
            $this->handleRequiredOption($option);
        } elseif ($value === false) {
            $confirm = true;
            if ($this->input->isInteractive()) {
                $confirmationAmplifiers = $option->getQuestionAmplifier('confirmation');
                if ($backupValue) {
                    $confirmationTemplate = $confirmationAmplifiers['template']
                        ?? 'Use default value "'.$backupValue.'" for "%s"?';
                } else {
                    $confirmationTemplate = $confirmationAmplifiers['template'] ?? 'Use empty value for "%s"?';
                }
                $confirm = $this->question->ask($this->input, $this->output, QuestionFactory::confirmation(
                    sprintf($confirmationTemplate, $option->getDescription()),
                    amplifiers: $confirmationAmplifiers
                ));
            }
            if (!$confirm) {
                $option->setBackupValue($backupValue = null);
                $this->handleRequiredOption($option);
            }
        }
        if (!$value && $backupValue) {
            $value = $backupValue;
            $this->input->setOption(
                $optName,
                $backupValue
            );
        }
        if ($value) $this->validateOption($option);
    }

    /**
     * @param string[] $optionNames
     * @return void
     *
     * @throws InputOptionException
     */
    public function handleOptions(array $optionNames): void
    {
        foreach ($optionNames as $optionName) {
            $option = $this->inputDefinition->getOption($optionName);
            if ($option instanceof RequiredOption) {
                $this->handleOption($option);
            }
        }
    }

    protected function validateOption(RequiredOption $option): void
    {
        if (($amplifier = $option->getQuestionAmplifier('input'))
            && isset($amplifier['validator'])
        ) {
            ($amplifier['validator'])($this->input->getOption($option->getName()));
        }
    }
}
