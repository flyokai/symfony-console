<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Uri;
use Flyokai\SymfonyConsole\Input\ValidationException;

class UriValidator
{
    public function __construct(
        protected readonly array $allowedSchemes = [],
    )
    {
    }
    public function validate(string|null $value): string
    {
        Assertions::assertNotNull($value);
        try {
            $uri = Uri::new($value);
        } catch (SyntaxError $e) {
            throw new ValidationException($e->getMessage());
        }
        if (!in_array($uri->getScheme(), $this->allowedSchemes)) {
            throw new ValidationException(sprintf(
                'Only "%s" schemes supported. "%s" provided in "%s"',
                implode(",", $this->allowedSchemes),
                $uri->getScheme(),
                $value
            ));
        }
        if (!$uri->getHost()) {
            throw new ValidationException(
                sprintf('Host is missing in "%s"', $value)
            );
        }
        if (!$uri->getPath()) {
            $uri = $uri->withPath('/');
        }
        return $uri->toString();
    }
}
