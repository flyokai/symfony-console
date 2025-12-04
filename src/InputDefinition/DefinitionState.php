<?php

namespace Flyokai\SymfonyConsole\InputDefinition;

use Flyokai\DataMate\Helper\SelfFactory;
use Flyokai\Generic\State;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * @implements State<InputDefinition>
 */
class DefinitionState implements State
{
    use SelfFactory;
    public function __construct(
        protected InputDefinition $inputDefinition = new InputDefinition()
    )
    {
    }

    public function get(): InputDefinition
    {
        return $this->inputDefinition;
    }

}
