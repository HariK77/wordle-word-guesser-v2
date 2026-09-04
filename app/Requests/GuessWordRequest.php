<?php

/**
 * GuessWordRequest - Form Request Validation
 * Validates incoming guess word requests
 */

namespace App\Requests;

class GuessWordRequest
{
    /**
     * Request data
     */
    protected array $data = [];

    /**
     * Validation errors
     */
    protected array $errors = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = $_GET ?? [];
    }

    /**
     * Validate request
     */
    public function validate(): bool
    {
        $this->errors = [];

        // Validate known letters
        $this->validateKnownLetters();

        // Validate excluded letters
        $this->validateExcludedLetters();

        return empty($this->errors);
    }

    /**
     * Validate known letters
     */
    protected function validateKnownLetters(): void
    {
        $config = config('app');
        $wordLength = $config['word_length'] ?? 5;
        $knownCount = 0;

        for ($i = 1; $i <= $wordLength; $i++) {
            $letter = $this->sanitizeInput("l{$i}");

            if (!empty($letter)) {
                // Check if it's a single letter
                if (strlen($letter) !== 1 || !preg_match('/^[A-Z]$/i', $letter)) {
                    $this->errors["l{$i}"] = "Letter {$i} must be a single letter (A-Z)";
                    continue;
                }
                $knownCount++;
            }
        }

        // At least one letter should be known
        $minLetters = $config['min_position_known_letters'] ?? 1;
        if ($knownCount < $minLetters) {
            $this->errors['position_known_letters'] = "Please provide at least {$minLetters} known letter.";
        }
    }

    /**
     * Validate excluded letters
     */
    protected function validateExcludedLetters(): void
    {
        $config = config('app');
        $excluded = $this->sanitizeInput('excluded');

        if (!empty($excluded)) {
            // Split by comma
            $letters = array_filter(
                array_map('trim', explode(',', $excluded)),
                fn($letter) => !empty($letter)
            );

            foreach ($letters as $letter) {
                if (strlen($letter) !== 1 || !preg_match('/^[A-Z]$/i', $letter)) {
                    $this->errors['excluded'] = "Excluded letters must be single letters (A-Z) separated by commas.";
                    break;
                }
            }

            $maxExcluded = $config['max_excluded_letters'] ?? 26;
            if (count($letters) > $maxExcluded) {
                $this->errors['excluded'] = "Maximum {$maxExcluded} excluded letters allowed.";
            }
        }
    }

    /**
     * Sanitize input
     */
    protected function sanitizeInput(string $key): string
    {
        if (!isset($this->data[$key])) {
            return '';
        }

        $value = $this->data[$key];

        // Handle non-string values
        if (!is_string($value)) {
            return '';
        }

        $value = trim($value);
        $value = stripslashes($value);
        $value = strtoupper($value);

        return $value;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        $config = config('app');
        $wordLength = $config['word_length'] ?? 5;
        $validated = [
            'position_known_letters' => [],
            'excluded_letters' => [],
            'known_letters' => []
        ];

        // Extract known letters
        for ($i = 1; $i <= $wordLength; $i++) {
            $validated['position_known_letters'][] = $this->sanitizeInput("l{$i}");
        }

        // Extract excluded letters
        $excluded = $this->sanitizeInput('excluded');
        if (!empty($excluded)) {
            $validated['excluded_letters'] = array_filter(
                array_map('trim', explode(',', $excluded)),
                fn($letter) => !empty($letter) && strlen($letter) === 1
            );
        }

        // Extract known letters
        $known = $this->sanitizeInput('known');
        if (!empty($known)) {
            $validated['known_letters'] = array_filter(
                array_map('trim', explode(',', $known)),
                fn($letter) => !empty($letter) && strlen($letter) === 1
            );
        }

        return $validated;
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get first error message
     */
    public function getFirstError(): string
    {
        return reset($this->errors) ?: '';
    }

    /**
     * Get all error messages
     */
    public function getAllErrors(): string
    {
        return implode(' ', $this->errors);
    }
}
