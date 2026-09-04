<?php

/**
 * WordGuesserService - Business Logic for Word Guessing
 * Handles the core logic of guessing words
 */

namespace App\Services;

use App\Models\Word;

class WordGuesserService
{
    /**
     * Excluded letters
     */
    protected array $excludedLetters = [];

    /**
     * Known letters
     */
    protected array $knownLetters = [];

    /**
     * Position Known Letters
     */
    protected array $positionKnownLetters = [];

    /**
     * Allowed letters
     */
    protected array $allowedLetters = [];

    /**
     * Guessed words
     */
    protected array $guessedWords = [];

    /**
     * Words storage path
     */
    protected string $wordsPath;

    /**
     * Loaded words
     */
    protected array $loadedWords = [];

    /**
     * Constructor
     */
    public function __construct(array $excluded = [], array $positionKnownLetters = [], array $knownLetters = [], string $wordsPath = '')
    {
        $config = config('app');
        $this->excludedLetters = array_map('strtolower', $excluded);
        $this->knownLetters = array_map('strtolower', $knownLetters);
        $this->positionKnownLetters = array_map('strtolower', $positionKnownLetters);
        $this->wordsPath = $wordsPath ?: $config['words_path'];
        $this->precomputeAllowedLetters();
    }

    /**
     * Precompute allowed letters for better performance
     */
    protected function precomputeAllowedLetters(): void
    {
        $config = config('app');
        $this->allowedLetters = [];

        for ($i = $config['letter_start']; $i <= $config['letter_end']; $i++) {
            $letter = chr($i);
            if (!in_array($letter, $this->excludedLetters)) {
                $this->allowedLetters[] = $letter;
            }
        }
    }

    /**
     * Main entry point for word guessing
     */
    public function guess(): array
    {
        $unknownPositions = $this->getUnknownLettersPosition();

        if (count(array_filter($this->positionKnownLetters)) === 5) {
            $this->guessedWords[] = implode($this->positionKnownLetters);
            return array_unique(array_map('strtoupper', $this->guessedWords));
        }

        if (count(array_filter($this->positionKnownLetters)) === 1 && count(array_filter($this->knownLetters)) <= 1 && !empty($this->positionKnownLetters[0])) {
            $this->guessedWords[] = implode($this->positionKnownLetters);
            return array_unique(array_map('strtoupper', $this->loadWords($this->positionKnownLetters[0])));
        }

        if (empty($unknownPositions)) {
            return [];
        }

        $this->guessedWords = [];
        $this->recursiveGuess($unknownPositions, 0);

        return array_unique(array_map('strtoupper', $this->guessedWords));
    }

    /**
     * Get unknown letters positions
     */
    protected function getUnknownLettersPosition(): array
    {
        $positions = [];

        foreach ($this->positionKnownLetters as $key => $value) {
            if (empty($value)) {
                $positions[] = $key;
            }
        }

        return $positions;
    }

    /**
     * Recursively fill unknown positions with allowed letters
     */
    protected function recursiveGuess(array $unknownPositions, int $index): void
    {
        // Base case: all positions filled
        if ($index === count($unknownPositions)) {
            $word = implode($this->positionKnownLetters);
            $knownWords = $this->loadWords(substr($word, 0, 1));

            if (in_array($word, $knownWords, true) && $this->containsAllLetters($word)) {
                $this->guessedWords[] = $word;
            }
            return;
        }

        // Recursive case: try each allowed letter
        $position = $unknownPositions[$index];

        foreach ($this->allowedLetters as $letter) {
            $this->positionKnownLetters[$position] = $letter;
            $this->recursiveGuess($unknownPositions, $index + 1);
        }

        // Backtrack
        $this->positionKnownLetters[$position] = '';
    }

    /**
     * Load words from JSON file
     */
    protected function loadWords(string $letter, string $type = "txt"): array
    {
        if (strlen($letter) === 0) {
            return [];
        }

        if (array_key_exists($letter, $this->loadedWords)) {
            return $this->loadedWords[$letter];
        }

        $path = $this->wordsPath . '/' . $type . '/' . $letter . '.' . $type;

        if (!file_exists($path)) {
            logger()->warning("Words file not found: {$path},  Letter: {$letter}");
            return [];
        }

        try {
            $words = [];
            if ($type === "json") {
                $words = json_decode(file_get_contents($path), true);
            } else {
                $words = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            $this->loadedWords[$letter] = $words;
            return $words;
        } catch (\Exception $e) {
            logger()->error("Error loading words: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if all given letters exist in a word
     */
    protected function containsAllLetters(string $word): bool
    {
        if (count($this->knownLetters) == 0) {
            return true;
        }

        $letters = implode("", $this->knownLetters);
        // Check each letter individually
        for ($i = 0; $i < strlen($letters); $i++) {
            if (strpos($word, $letters[$i]) === false) {
                return false; // Letter not found
            }
        }
        return true;
    }

    /**
     * Get all guessed words
     */
    public function getGuessedWords(): array
    {
        return $this->guessedWords;
    }

    /**
     * Get count of guessed words
     */
    public function getGuessedWordsCount(): int
    {
        return count($this->guessedWords);
    }

    /**
     * Filter words by pattern
     */
    public function filterWordsByPattern(array $words, array $knownLetters, array $excludedLetters): array
    {
        return array_filter($words, function ($wordValue) use ($knownLetters, $excludedLetters) {
            $word = new Word($wordValue);
            return $word->matchesPattern($knownLetters, $excludedLetters);
        });
    }

    /**
     * Set excluded letters
     */
    public function setExcludedLetters(array $letters): self
    {
        $this->excludedLetters = array_map('strtolower', $letters);
        $this->precomputeAllowedLetters();
        return $this;
    }

    /**
     * Set known letters
     */
    public function setKnownLetters(array $letters): self
    {
        $this->knownLetters = array_map('strtolower', $letters);
        return $this;
    }

    /**
     * Set Position known letters
     */
    public function setPositionKnownLetters(array $letters): self
    {
        $this->positionKnownLetters = array_map('strtolower', $letters);
        return $this;
    }

    /**
     * Get excluded letters
     */
    public function getExcludedLetters(): array
    {
        return $this->excludedLetters;
    }

    /**
     * Get known letters
     */
    public function getKnownLetters(): array
    {
        return $this->knownLetters;
    }

    /**
     * Get known letters
     */
    public function getPositionKnownLetters(): array
    {
        return $this->positionKnownLetters;
    }
}
