# flyokai/symfony-console

> User docs → [`README.md`](README.md) · Agent quick-ref → [`CLAUDE.md`](CLAUDE.md) · Agent deep dive → [`AGENTS.md`](AGENTS.md)

> Async-friendly Symfony Console for Flyokai — required arguments/options, filesystem validators, and `flyokai/generic`-based pipelines.

Sits on top of `symfony/console` and adds a layer of input definitions, handlers, and validators tailored to setup/install/upgrade workflows. Inputs are tunable from across packages so any module can contribute parameters to the same command.

## Features

- **`RequiredArgument`, `RequiredOption`** — extend Symfony with `inputRequired`, `backupValue` (Closure), `questionAmplifiers`
- **`DirectoryOption`, `FileOption`** — filesystem-aware variants with `isWritable`, `replaceExisting`, `shouldExist` flags
- **Input handlers** — `RequiredArgumentHandler`, `RequiredOptionHandler`, `FilesystemOptionHandler`
- **Validators** — directory, file, existing file, crypt key, URI, HTTP URL, SSL cert, user, selection
- **`QuestionFactory`** — Symfony Question builder with amplifier system
- **State pattern** — `InputState` is `State<InputInterface>` from `flyokai/generic`

## Installation

```bash
composer require flyokai/symfony-console
```

## Quick start

```php
use Flyokai\SymfonyConsole\InputDefinition\RequiredOption;
use Flyokai\SymfonyConsole\InputDefinition\FileOption;
use Symfony\Component\Console\Input\InputOption;

$definition->addOption(
    new RequiredOption(
        name: 'db-host',
        mode: InputOption::VALUE_REQUIRED,
        description: 'MySQL hostname',
        default: 'localhost',
        inputRequired: true,
    )
);

$definition->addOption(
    new FileOption(
        name: 'crypt-key',
        description: 'Path to the OpenSSL key file',
        shouldExist: true,
    )
);
```

The matching `RequiredOptionHandler` / `FilesystemOptionHandler` does the actual prompting/validation:

- **Interactive mode** — prompts the user when the value is missing.
- **`--no-interaction`** — uses backup/default if available, otherwise throws.

## Input definitions

| Class | Extends | Adds |
|-------|---------|------|
| `RequiredArgument` | `InputArgument` | `inputRequired`, `backupValue`, `questionAmplifiers` |
| `RequiredOption` | `InputOption` | same + fluent `setBackupValue()` |
| `DirectoryOption` | `RequiredOption` | `isWritable` |
| `FileOption` | `RequiredOption` | `isWritable`, `replaceExisting`, `shouldExist` |

`backupValue` may be a `Closure(InputInterface): mixed` — useful for dynamic defaults derived from other parameters.

## Validators

| Validator | Purpose |
|-----------|---------|
| `DirectoryInputValidator` | Validate directory paths; create them on demand (interactive); check writability |
| `FileInputValidator` | Validate file paths; handle replacement/creation with parent dirs |
| `ExistingFileInputValidator` | Must exist + readable (+ writable if flagged) |
| `CryptKeyValidator` | Validate cryptographic key files via `Flyokai\Misc\CryptKey` |
| `UriValidator` | URI validation with allowed schemes |
| `HttpUrlValidator` | HTTP(S) URL validation (extends `UriValidator`) |
| `SslCertValidator` | SSL certificate validation (extends `CryptKeyValidator`) |
| `UserValidator` | Static helpers — `validatePassword` (8+ chars, no spaces), `validateUsername` (3+ chars, alnum), `validateEmail` |
| `SelectionValidator` | Multi-choice validation against allowed options (lowercased) |

## QuestionFactory

```php
use Flyokai\SymfonyConsole\QuestionFactory;

$question = QuestionFactory::question('Database name?', amplifiers: [
    'validator'    => fn($v) => $v ?: throw new \InvalidArgumentException('required'),
    'maxAttempts'  => 3,
    'normalizer'   => 'trim',
]);

$confirm = QuestionFactory::confirmation('Continue?', defaultAnswer: false);
```

Amplifiers (config arrays) include: `validator`, `hidden`, `hiddenFallback`, `maxAttempts`, `multiline`, `autocompleterCallback`, `autocompleterValues`, `normalizer`, `trimmable`.

## Tuner pattern

`InputState` implements `Flyokai\Generic\State<InputInterface>` so any module can contribute a tuner that mutates the input state as part of a pipeline.

In Flyokai, every setup/install/upgrade parameter is contributed by a separate `definition:tuner` (adds the option) and `input:tuner` (validates / collects the value at runtime), wired via DI. See the `flyokai/application` module documentation for the full setup-command parameter architecture.

## Gotchas

- **Validators create files/directories as a side effect** — `FileInputValidator` may create the parent directories during validation.
- **Backup value as Closure** — `getBackupValue(InputInterface)` calls the closure each time; useful for dynamic defaults.
- **Interactive vs. non-interactive** — interactive mode loops on validation errors; non-interactive throws immediately.
- **Global error handler** — `Directory*` / `File*` validators set a global error handler without nesting protection.
- **`SelectionValidator` is case-insensitive** — input is lowercased; keys are expected lowercase.

## See also

- [`flyokai/generic`](../generic/README.md) — `State<T>` and tuner primitives this package builds on
- [`flyokai/application`](../application/README.md) — uses these primitives for setup/install/upgrade commands
- [`flyokai/misc`](../misc/README.md) — `CryptKey` (used by `CryptKeyValidator`)

## License

MIT
