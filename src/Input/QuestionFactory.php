<?php

namespace Flyokai\SymfonyConsole\Input;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class QuestionFactory
{
    /**
     * @param Question $question
     * @param array<string, mixed> $amplifiers
     * @return Question
     */
    protected static function amplify(Question $question, array $amplifiers = []): Question
    {
        foreach ($amplifiers as $amplifier => $value) {
            match ($amplifier) {
                'validator' => $question->setValidator($value),
                'hidden' => $question->setHidden($value),
                'hiddenFallback' => $question->setHiddenFallback($value),
                'maxAttempts' => $question->setMaxAttempts($value),
                'multiline' => $question->setMultiline($value),
                'autocompleterCallback' => $question->setAutocompleterCallback($value),
                'autocompleterValues' => $question->setAutocompleterValues($value),
                'normalizer' => $question->setNormalizer($value),
                'trimmable' => $question->setTrimmable($value),
                'template' => $question,
                'validatorFactory' => $question,
                default => throw new \InvalidArgumentException(
                    sprintf('Unknown question amplifier "%s"', $amplifier)
                )
            };
        }
        return $question;
    }

    /**
     * @param string $question
     * @param string|bool|int|float|null $default
     * @param array<string, mixed> $amplifiers
     * @return Question
     */
    public static function question(
        string $question,
        string|bool|int|float|null $default = null,
        array $amplifiers = []
    ): Question
    {
        return self::amplify(new Question($question, $default), $amplifiers);
    }

    /**
     * @param string $question
     * @param string|bool|int|float|null $default
     * @param array<string, mixed> $amplifiers
     * @return Question
     */
    public static function confirmation(
        string $question,
        string|bool|int|float|null $default = null,
        array $amplifiers = []
    ): Question
    {
        //$amplifiers['validator'] ??= self::yesNoValidator(...);
        return self::amplify(new ConfirmationQuestion($question), $amplifiers);
    }

    /**
     * @param string $question
     * @param string|bool|int|float|null $default
     * @param array<string, mixed> $amplifiers
     * @return Question
     */
    public static function requiredQuestion(
        string $question,
        string|bool|int|float|null $default = null,
        array $amplifiers = []
    ): Question
    {
        $notEmptyValidator = self::notEmptyValidator(...);
        $validator = $amplifiers['validator']??null;
        $amplifiers['validator'] = static function (string|null $value) use($notEmptyValidator, $validator): string {
            $value = $notEmptyValidator($value);
            return $validator ? $validator($value) : $value;
        };
        return self::question($question, $default, $amplifiers);
    }

    public static function notEmptyValidator(string|null $value): string
    {
        $value = trim((string)$value);
        if ($value == '') {
            throw new ValidationException('The value cannot be empty');
        }
        return $value;
    }

    /**
     * @param int $length
     * @return \Closure(string|null): string
     */
    public static function createLengthValidator(int $length): \Closure
    {
        return static function (string|null $value) use($length) : string {
            $value = trim((string)$value);
            if (strlen($value)<$length) {
                throw new ValidationException(sprintf('Minimum length of %s is not met', $length));
            }
            return $value;
        };
    }

    /**
     * @var string[]
     */
    protected static array $noOptions = ['n', 'no'];

    /**
     * @return string[]
     */
    public static function noOptions(): array
    {
        return self::$noOptions;
    }
    /**
     * @var string[]
     */
    protected static array $yesOptions = ['y', 'yes'];

    /**
     * @return string[]
     */
    public static function yesOptions(): array
    {
        return self::$yesOptions;
    }

    public static function yesNoValidator(string|null $value): string
    {
        $value = strtolower(trim((string)$value));
        if (!in_array($value, self::yesOptions()) && !in_array($value, self::noOptions())) {
            throw new ValidationException(sprintf('Only these values acceptable: %s',
                implode(', ', array_merge(self::yesOptions(), self::noOptions()))
            ));
        }

        return in_array($value, self::yesOptions()) ? self::yesOptions()[0] : self::noOptions()[0];
    }

}
