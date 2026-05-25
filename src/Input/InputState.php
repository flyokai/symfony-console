<?php

namespace Flyokai\SymfonyConsole\Input;

use Amp\Injector\Meta\ParameterAttribute\FactoryParameter;
use Flyokai\DataMate\Helper\SelfFactory;
use Flyokai\Generic\State;
use Flyokai\SymfonyConsole\Input\Helper\RequiredArgumentHandler;
use Flyokai\SymfonyConsole\Input\Helper\RequiredOptionHandler;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @implements State<InputInterface>
 */
class InputState implements State
{
    use SelfFactory;

    protected RequiredOptionHandler $requiredOptionHandler;
    protected RequiredArgumentHandler $requiredArgumentHandler;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $question
     * @param InputDefinition $inputDefinition
     * @param \Closure(string): HelperInterface $helper
     * @param \Closure(InputState): RequiredOptionHandler $requiredOptionHandlerFactory
     * @param \Closure(InputState): RequiredArgumentHandler $requiredArgumentHandlerFactory
     */
    public function __construct(
        public readonly InputInterface $input,
        public readonly OutputInterface $output,
        public readonly QuestionHelper $question,
        public readonly InputDefinition $inputDefinition,
        public readonly \Closure $helper,
        #[FactoryParameter(RequiredOptionHandler::class)] protected \Closure $requiredOptionHandlerFactory,
        #[FactoryParameter(RequiredArgumentHandler::class)] protected \Closure $requiredArgumentHandlerFactory,
    )
    {
        $this->requiredOptionHandler = ($this->requiredOptionHandlerFactory)($this);
        $this->requiredArgumentHandler = ($this->requiredArgumentHandlerFactory)($this);
    }

    public function get(): InputInterface
    {
        return $this->input;
    }

    public function getRequiredOptionHandler(): RequiredOptionHandler
    {
        return $this->requiredOptionHandler;
    }

    public function getRequiredArgumentHandler(): RequiredArgumentHandler
    {
        return $this->requiredArgumentHandler;
    }

}
