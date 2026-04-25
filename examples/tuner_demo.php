<?php
/**
 * symfony-console example — declarative input-definition + handler tuning, standalone.
 *
 * Demonstrates RequiredOption + RequiredOptionHandler on a tiny Symfony Console command.
 * Run interactively:
 *
 *   php vendor/flyokai/symfony-console/examples/tuner_demo.php
 *
 * Run non-interactively (must pass required options):
 *
 *   php vendor/flyokai/symfony-console/examples/tuner_demo.php greet --name=World --no-interaction
 */

require __DIR__ . '/../../../../vendor/autoload.php';

use Flyokai\SymfonyConsole\Input\Helper\RequiredOptionHandler;
use Flyokai\SymfonyConsole\InputDefinition\RequiredOption;
use Flyokai\SymfonyConsole\Input\InputState;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Greet extends Command
{
    protected static $defaultName = 'greet';

    protected function configure(): void
    {
        $this->setDescription('Greets someone — interactively prompts for --name if missing.');

        $this->addOption(new RequiredOption(
            name: 'name',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Who to greet',
            inputRequired: true,
        ));

        $this->addOption(new RequiredOption(
            name: 'enthusiasm',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Number of "!" to append',
            default: 1,
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $state   = new InputState($input, $output, new QuestionHelper(), $this->getDefinition());
        $handler = new RequiredOptionHandler();

        // Prompt for any RequiredOption that's missing in interactive mode;
        // throw in --no-interaction mode if there's no default.
        $handler->handleOptions($state);

        $name        = (string) $input->getOption('name');
        $enthusiasm  = max(0, (int) $input->getOption('enthusiasm'));

        $output->writeln(sprintf('Hello, %s%s', $name, str_repeat('!', $enthusiasm)));
        return 0;
    }
}

$app = new Application('tuner-demo', '0.1');
$app->add(new Greet());
exit($app->run());
