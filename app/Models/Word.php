<?php
/**
 * Word Model
 * Represents a word with its properties and methods
 */

namespace App\Models;

class Word
{
    /**
     * Word value
     */
    protected string $value;

    /**
     * Word length
     */
    protected int $length;

    /**
     * Word letters
     */
    protected array $letters = [];

    /**
     * Constructor
     */
    public function __construct(string $word)
    {
        $this->value = strtolower(trim($word));
        $this->length = strlen($this->value);
        $this->letters = str_split($this->value);
    }

    /**
     * Get word value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get word in uppercase
     */
    public function getUpperCase(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Get word length
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Get all letters
     */
    public function getLetters(): array
    {
        return $this->letters;
    }

    /**
     * Get letter at position
     */
    public function getLetterAt(int $position): ?string
    {
        return $this->letters[$position] ?? null;
    }

    /**
     * Contains letter
     */
    public function containsLetter(string $letter): bool
    {
        return in_array(strtolower($letter), $this->letters);
    }

    /**
     * Match pattern
     * @param array $knownLetters Array with positions and letters
     * @param array $excludedLetters Array of letters to exclude
     */
    public function matchesPattern(array $knownLetters, array $excludedLetters): bool
    {
        // Check known letters
        foreach ($knownLetters as $position => $letter) {
            if (!empty($letter)) {
                if ($this->getLetterAt($position) !== strtolower($letter)) {
                    return false;
                }
            }
        }

        // Check excluded letters
        foreach ($excludedLetters as $letter) {
            if ($this->containsLetter($letter)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get unique letters
     */
    public function getUniqueLetters(): array
    {
        return array_unique($this->letters);
    }

    /**
     * Count specific letter occurrences
     */
    public function countLetter(string $letter): int
    {
        return count(array_filter($this->letters, fn($l) => $l === strtolower($letter)));
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
