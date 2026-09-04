<?php

/**
 * Application Configuration
 * Central configuration for the Word Guesser application
 */

return [
    // Application Details
    'name' => 'Wordle Word Guesser',
    'version' => '2.0.0',
    'description' => 'A word guessing tool for Wordle players',
    'author' => 'Word Guesser Team',
    'url' => 'http://localhost/word-guesser',

    // Environment
    'env' => getenv('APP_ENV') ?? 'development',
    'debug' => getenv('APP_DEBUG') === 'true' ?? false,

    // Paths
    'root_path' => dirname(dirname(__FILE__)),
    'app_path' => dirname(dirname(__FILE__)) . '/app',
    'public_path' => dirname(dirname(__FILE__)) . '/public',
    'views_path' => dirname(dirname(__FILE__)) . '/resources/views',
    'logs_path' => dirname(dirname(__FILE__)) . '/storage/logs',
    'words_path' => dirname(dirname(__FILE__)) . '/storage/words',

    // Timezone
    'timezone' => 'UTC',

    // Word Guesser Settings
    'word_length' => 5,
    'min_position_known_letters' => 1,
    'max_excluded_letters' => 26,

    // Letter Range (ASCII)
    'letter_start' => 97,  // 'a'
    'letter_end' => 122,    // 'z'
];
