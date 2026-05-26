<?php

namespace App\Services;

class PasswordGeneratorService
{
    /**
     * @var list<string>|null
     */
    private ?array $words = null;

    public function generate(): string
    {
        $format = config('student-password-reset.temp_password.format');
        $minLength = config('student-password-reset.temp_password.min_length');

        do {
            $password = $this->generateFromFormat($format);
        } while (mb_strlen($password) < $minLength);

        return $password;
    }

    private function generateFromFormat(string $format): string
    {
        return match ($format) {
            'word-word-4digits-word' => $this->wordWordDigitsWord(),
            default => $this->wordWordDigitsWord(),
        };
    }

    private function wordWordDigitsWord(): string
    {
        return sprintf(
            '%s-%s-%04d-%s',
            $this->randomWord(),
            $this->randomWord(),
            random_int(0, 9999),
            $this->randomWord(),
        );
    }

    private function randomWord(): string
    {
        $words = $this->wordList();
        $word = $words[random_int(0, count($words) - 1)];

        return ucfirst(strtolower($word));
    }

    /**
     * @return list<string>
     */
    private function wordList(): array
    {
        if ($this->words !== null) {
            return $this->words;
        }

        $listName = config('student-password-reset.temp_password.word_list', 'default');
        $path = config_path('words/'.$listName.'.php');

        if (! is_readable($path)) {
            $path = config_path('words/default.php');
        }

        /** @var list<string> $words */
        $words = require $path;

        $this->words = $words;

        return $this->words;
    }
}
