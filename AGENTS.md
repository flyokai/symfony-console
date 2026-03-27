# flyokai/symfony-console

Enhanced async Symfony Console with input validation, filesystem handling, and generic pipeline integration.

## Key Abstractions

### State Classes
- `DefinitionState` (implements `State<InputDefinition>`) — wraps Symfony InputDefinition for pipeline
- `InputState` (implements `State<InputInterface>`) — central state: input, output, questionHelper, inputDefinition, handlers

### Enhanced Input Definitions
- `RequiredArgument` extends InputArgument — adds `inputRequired`, `backupValue` (supports Closure), `questionAmplifiers`
- `RequiredOption` extends InputOption — same enhancements + fluent `setBackupValue()`
- `DirectoryOption` extends RequiredOption — adds `isWritable` flag
- `FileOption` extends RequiredOption — adds `isWritable`, `replaceExisting`, `shouldExist` flags

### Input Handlers
- `RequiredArgumentHandler` — ensures required arguments: if missing + interactive → asks question; else throws
- `RequiredOptionHandler` — handles required + optional options with backup value confirmation flow
- `FilesystemOptionHandler` — processes DirectoryOption/FileOption with validator delegation

### Validators
| Validator | Purpose |
|-----------|---------|
| `DirectoryInputValidator` | Validates dir paths, creates if missing (interactive), checks writability |
| `FileInputValidator` | Validates file paths, handles replacement/creation with parent dirs |
| `ExistingFileInputValidator` | File must exist + readable (+ writable if flagged) |
| `CryptKeyValidator` | Validates cryptographic key files via `Wtsergo\Misc\CryptKey` |
| `UriValidator` | URI validation with allowed schemes |
| `HttpUrlValidator` | HTTPS/HTTP URL validation (extends UriValidator) |
| `SslCertValidator` | SSL certificate validation (extends CryptKeyValidator) |
| `UserValidator` | Static: `validatePassword` (8+ chars, no spaces), `validateUsername` (3+ chars, alnum), `validateEmail` |
| `SelectionValidator` | Multi-choice validation against allowed options |

### QuestionFactory
Static methods for creating Symfony Questions:
- `question()`, `confirmation()`, `requiredQuestion()` with amplifier support
- Amplifiers: validator, hidden, hiddenFallback, maxAttempts, multiline, autocompleterCallback, autocompleterValues, normalizer, trimmable

## Patterns

- Uses Generic module's `State<T>` interface throughout
- Question amplifiers are config arrays applied via `amplify(Question, amplifiers[])`
- Backup values can be Closures receiving InputInterface — enables dynamic defaults
- Validators create files/directories as side effects during validation

## Gotchas

- **File creation during validation**: FileInputValidator creates files/parent dirs as side effect
- **Backup value as Closure**: `getBackupValue(InputInterface)` resolves callable dynamically
- **Interactive vs non-interactive**: Validation errors loop in interactive mode, throw immediately otherwise
- **Global error handler**: Directory/File validators set global error handler without nesting protection
- **SelectionValidator case**: Converts input to lowercase; keys expected lowercase
