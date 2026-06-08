<?php

namespace Flyokai\SymfonyConsole\Input\Helper;

class HttpUrlValidator extends UriValidator
{
    public function __construct(
        protected readonly bool $httpsOptional = false
    )
    {
        $allowedSchemes = ['https'];
        if ($this->httpsOptional) {
            $allowedSchemes[] = 'http';
        }
        parent::__construct($allowedSchemes);
    }
}
