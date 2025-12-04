<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use Flyokai\SymfonyConsole\Input\ValidationException;

class ExistingFileInputValidator extends FileInputValidator
{
    public function validate(string|null $path): string
    {
        Assertions::assertNotNull($path);
        $path = rtrim((string)$path, '/\\');
        set_error_handler(
            $this->filesystemErrorHandler(...),
            E_WARNING
        );
        try {
            if (!is_file($path)) {
                throw new ValidationException(
                    sprintf('"%s" is not a regular file or does not exist. Please enter file path for "%s":',
                        $path,
                        $this->option->getDescription()
                    )
                );
            }
            if (!is_readable($path)) {
                throw new ValidationException(sprintf('File "%s" is not readable.', $path));
            }
            if ($this->option->isWritable()) {
                try {
                    touch($path);
                } catch (FilesystemException $e) {
                    throw new ValidationException(sprintf('File "%s" is not writable.', $path));
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
