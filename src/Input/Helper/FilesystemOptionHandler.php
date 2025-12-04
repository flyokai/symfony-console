<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Amp\Injector\Meta\ParameterAttribute\FactoryParameter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Flyokai\SymfonyConsole\Input\InputOptionException;
use Flyokai\SymfonyConsole\Input\QuestionFactory;
use Flyokai\SymfonyConsole\Input\InputState as InputState;
use Flyokai\SymfonyConsole\Input\ValidationException;
use Flyokai\SymfonyConsole\InputDefinition\RequiredOption;
use Flyokai\SymfonyConsole\InputDefinition\DirectoryOption;
use Flyokai\SymfonyConsole\InputDefinition\FileOption;

class FilesystemOptionHandler
{
    protected QuestionHelper $question;
    protected InputInterface $input;
    protected InputDefinition $inputDefinition;
    protected OutputInterface $output;

    /**
     * @param InputState $state
     * @param \Closure(RequiredOption, InputState): DirectoryInputValidator $directoryInputValidatorFactory
     * @param \Closure(FileOption, InputState): FileInputValidator $fileInputValidatorFactory
     * @param \Closure(FileOption, InputState): ExistingFileInputValidator $existingFileInputValidatorFactory
     */
    public function __construct(
        protected InputState $state,
        #[FactoryParameter(DirectoryInputValidator::class)] protected \Closure $directoryInputValidatorFactory,
        #[FactoryParameter(FileInputValidator::class)] protected \Closure $fileInputValidatorFactory,
        #[FactoryParameter(ExistingFileInputValidator::class)] protected \Closure $existingFileInputValidatorFactory,
    )
    {
        $this->input = $this->state->input;
        $this->output = $this->state->output;
        $this->question = $this->state->question;
        $this->inputDefinition = $this->state->inputDefinition;
    }

    public function handleDirectoryOption(DirectoryOption $option): void
    {
        $optName = $option->getName();
        $value = $this->input->getOption($optName);
        $dirValidator = ($this->directoryInputValidatorFactory)($option, $this->state);
        if ($value === false) {
            $confirm = true;
            if ($this->input->isInteractive()) {
                $confirm = $this->question->ask($this->input, $this->output, QuestionFactory::confirmation(
                    sprintf('Use default "%s" for "%s"?',
                        $option->getBackupValue($this->input),
                        $option->getDescription()
                    ),
                    amplifiers: $option->getQuestionAmplifier('confirmation')
                ));
            }
            if (!$confirm) {
                $value = $this->requestDirectoryInput($dirValidator, $option);
            } else {
                $value = $this->handleDirectoryValue($dirValidator, $option, $option->getBackupValue($this->input));
            }
        } elseif ($value === null) {
            $value = $this->requestDirectoryInput($dirValidator, $option);
        } else {
            $value = $this->handleDirectoryValue($dirValidator, $option, $value);
        }
        $this->input->setOption($optName, $value);
    }

    protected function requestDirectoryInput(
        DirectoryInputValidator $dirValidator, DirectoryOption $option
    ): string
    {
        $amplifiers = $option->getQuestionAmplifier('input');
        $validator = $amplifiers['validator']??null;
        $amplifiers['validator'] = static function (string|null $value) use($dirValidator, $validator): string {
            $value = $dirValidator->validate($value);
            return $validator ? $validator($value) : $value;
        };
        $value = $this->question->ask($this->input, $this->output, QuestionFactory::requiredQuestion(
            sprintf('Please enter "%s":', $option->getDescription()),
            amplifiers: $amplifiers
        ));
        return $value;
    }

    protected function handleDirectoryValue(
        DirectoryInputValidator $dirValidator, DirectoryOption $option, string $value
    ): string
    {
        try {
            $value = $dirValidator->validate($value);
        } catch (ValidationException $e) {
            $validationError = InputOptionException::validationError($option->getName(), $e->getMessage(), previous: $e);
            if (!$this->input->isInteractive()) {
                throw $validationError;
            }
            $this->output->writeln($validationError->getMessage());
            $value = $this->requestDirectoryInput($dirValidator, $option);
        }
        return $value;
    }

    public function handleFileOption(FileOption $option): void
    {
        $optName = $option->getName();
        $value = $this->input->getOption($optName);
        $backupValue = $option->getBackupValue($this->input);
        if ($option->shouldExist()) {
            $fileValidator = ($this->existingFileInputValidatorFactory)($option, $this->state);
        } else {
            $fileValidator = ($this->fileInputValidatorFactory)($option, $this->state);
        }
        if ($value === false) {
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
                $value = $this->requestFileInput($fileValidator, $option);
            } else {
                if (!$value && $backupValue) {
                    $value = $backupValue;
                    $this->input->setOption(
                        $optName,
                        $backupValue
                    );
                }
                if ($value || $option->isValueRequired()) {
                    $value = $this->handleFileValue($fileValidator, $option, $value);
                }
            }
        } elseif ($value === null) {
            $value = $this->requestFileInput($fileValidator, $option);
        } else {
            $value = $this->handleFileValue($fileValidator, $option, $value);
        }
        $this->input->setOption($optName, $value);
    }

    protected function requestFileInput(
        FileInputValidator $fileValidator, FileOption $option
    ): string
    {
        $amplifiers = $option->getQuestionAmplifier('input');
        $validator = $amplifiers['validator']??null;
        $validatorFactory = $amplifiers['validatorFactory']??null;
        $validator = $this->createValidator($validator, $validatorFactory);
        $amplifiers['validator'] = static function (string|null $value) use($fileValidator, $validator): string {
            $value = $fileValidator->validate($value);
            return $validator ? $validator($value) : $value;
        };
        $value = $this->question->ask($this->input, $this->output, QuestionFactory::requiredQuestion(
            sprintf('Please enter "%s":', $option->getDescription()),
            amplifiers: $amplifiers
        ));
        return $value;
    }

    private function createValidator(?\Closure $validator=null, ?\Closure $validatorFactory=null): ?\Closure
    {
        if ($validatorFactory) {
            $input = $this->input;
            $validator = static function (string|null $value) use($input, $validatorFactory, $validator): string {
                $value = $validatorFactory($input)($value);
                return $validator ? $validator($value) : $value;
            };
        }
        return $validator;
    }

    protected function handleFileValue(
        FileInputValidator $fileValidator, FileOption $option, string|null $value
    ): string
    {
        try {
            $value = $fileValidator->validate($value);
            $amplifiers = $option->getQuestionAmplifier('input');
            $validator = $amplifiers['validator']??null;
            $validatorFactory = $amplifiers['validatorFactory']??null;
            $validator = $this->createValidator($validator, $validatorFactory);
            if ($validator) {
                $value = $validator($value);
            }
        } catch (ValidationException $e) {
            $validationError = InputOptionException::validationError($option->getName(), $e->getMessage(), previous: $e);
            if (!$this->input->isInteractive()) {
                throw $validationError;
            }
            $this->output->writeln($validationError->getMessage());
            $value = $this->requestFileInput($fileValidator, $option);
        }
        return $value;
    }

    /**
     * @param string[] $optionNames
     * @return void
     */
    public function handleDirectoryOptions(array $optionNames): void
    {
        foreach ($optionNames as $optionName) {
            $option = $this->inputDefinition->getOption($optionName);
            if ($option instanceof DirectoryOption) {
                $this->handleDirectoryOption($option);
            }
        }
    }

    /**
     * @param string[] $optionNames
     * @return void
     */
    public function handleFileOptions(array $optionNames): void
    {
        foreach ($optionNames as $optionName) {
            $option = $this->inputDefinition->getOption($optionName);
            if ($option instanceof FileOption) {
                $this->handleFileOption($option);
            }
        }
    }
}
