<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Flyokai\SymfonyConsole\Input\QuestionFactory;
use Flyokai\SymfonyConsole\Input\InputState as InputState;
use Flyokai\SymfonyConsole\Input\ValidationException;
use Flyokai\SymfonyConsole\InputDefinition\DirectoryOption;

class DirectoryInputValidator
{
    protected QuestionHelper $question;
    protected InputInterface $input;
    protected InputDefinition $inputDefinition;
    protected OutputInterface $output;

    public function __construct(
        protected DirectoryOption $option,
        protected InputState $state
    )
    {
        $this->input = $this->state->input;
        $this->output = $this->state->output;
        $this->question = $this->state->question;
        $this->inputDefinition = $this->state->inputDefinition;
    }

    public function validate(string|null $path): string
    {
        Assertions::assertNotNull($path);
        $path = rtrim((string)$path, '/\\');
        set_error_handler(
            $this->filesystemErrorHandler(...),
            E_WARNING
        );
        try {
            if (is_file($path)) {
                throw new ValidationException(
                    sprintf('"%s" is an existing file. Please enter directory path for "%s":',
                        $path,
                        $this->option->getDescription()
                    )
                );
            }
            if (!is_dir($path)) {
                $confirm = true;
                if ($this->input->isInteractive()) {
                    $confirm = $this->question->ask($this->input, $this->output, QuestionFactory::confirmation(
                        sprintf('Directory "%s" does not exists. Create?', $path)
                    ));
                }
                if ($confirm) {
                    mkdir($path, recursive: true);
                    return $path;
                } else {
                    throw new ValidationException(sprintf('Directory "%s" does not exists.', $path));
                }
            }
            if ($this->option->isWritable()) {
                $touch = $path.DS.bin2hex(random_bytes(16));
                try {
                    touch($touch);
                } catch (FilesystemException $e) {
                    throw new ValidationException(sprintf('Directory "%s" is not writable.', $path));
                } finally {
                    @unlink($touch);
                }
                return $path;
            }
        } catch (FilesystemException $e) {
            throw new ValidationException($e->getMessage(), $e->getCode(), $e);
        } finally {
            restore_error_handler();
        }
        return $path;
    }

    /**
     * @throws FilesystemException
     */
    protected function filesystemErrorHandler(int $error, string $message): bool
    {
        throw new FilesystemException($message, $error);
    }
}
