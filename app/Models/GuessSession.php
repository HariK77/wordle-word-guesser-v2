<?php

/**
 * GuessSession Model
 * Represents a word guessing session
 */

namespace App\Models;

class GuessSession
{
    /**
     * Session ID
     */
    protected string $sessionId;

    /**
     * Known letters array
     */
    protected array $knownLetters = [];

    /**
     * Excluded letters array
     */
    protected array $excludedLetters = [];

    /**
     * Guessed words
     */
    protected array $guessedWords = [];

    /**
     * Session metadata
     */
    protected array $metadata = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sessionId = session_id() ?? uniqid('session_');
        $this->loadSession();
    }

    /**
     * Load session from storage
     */
    protected function loadSession(): void
    {
        if (isset($_SESSION['guess_session'])) {
            $data = $_SESSION['guess_session'];
            $this->knownLetters = $data['position_known_letters'] ?? [];
            $this->excludedLetters = $data['excluded_letters'] ?? [];
            $this->guessedWords = $data['guessed_words'] ?? [];
            $this->metadata = $data['metadata'] ?? [];
        }
    }

    /**
     * Save session to storage
     */
    public function save(): void
    {
        $_SESSION['guess_session'] = [
            'position_known_letters' => $this->knownLetters,
            'excluded_letters' => $this->excludedLetters,
            'guessed_words' => $this->guessedWords,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * Set known letters
     */
    public function setKnownLetters(array $letters): self
    {
        $this->knownLetters = array_map('strtoupper', $letters);
        return $this;
    }

    /**
     * Get known letters
     */
    public function getKnownLetters(): array
    {
        return $this->knownLetters;
    }

    /**
     * Set excluded letters
     */
    public function setExcludedLetters(array $letters): self
    {
        $this->excludedLetters = array_map('strtoupper', $letters);
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
     * Set guessed words
     */
    public function setGuessedWords(array $words): self
    {
        $this->guessedWords = array_unique($words);
        return $this;
    }

    /**
     * Get guessed words
     */
    public function getGuessedWords(): array
    {
        return $this->guessedWords;
    }

    /**
     * Add guessed word
     */
    public function addGuessedWord(string $word): self
    {
        if (!in_array(strtolower($word), $this->guessedWords)) {
            $this->guessedWords[] = strtolower($word);
        }
        return $this;
    }

    /**
     * Count guessed words
     */
    public function countGuessedWords(): int
    {
        return count($this->guessedWords);
    }

    /**
     * Set metadata
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Get metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Clear session
     */
    public function clear(): self
    {
        $this->knownLetters = [];
        $this->excludedLetters = [];
        $this->guessedWords = [];
        $this->metadata = [];
        return $this;
    }

    /**
     * Check if session has any data
     */
    public function hasData(): bool
    {
        return !empty($this->knownLetters) || !empty($this->excludedLetters);
    }
}
