# flyokai/symfony-console

Enhanced async Symfony Console with required arguments/options, filesystem validation, and generic pipeline integration.

See [AGENTS.md](AGENTS.md) for detailed module knowledge.

## Quick Reference

- **Input definitions**: `RequiredArgument`, `RequiredOption`, `DirectoryOption`, `FileOption`
- **Handlers**: `RequiredArgumentHandler`, `RequiredOptionHandler`, `FilesystemOptionHandler`
- **Validators**: Directory, File, ExistingFile, CryptKey, Uri, HttpUrl, SslCert, User, Selection
- **State pattern**: `InputState` implements `State<InputInterface>` from flyokai/generic
- **Question factory**: `QuestionFactory` with amplifier-based customization
