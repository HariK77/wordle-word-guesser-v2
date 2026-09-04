<?php

/**
 * Global Helper Functions
 * Utility functions available throughout the application
 */

/**
 * Get configuration value
 * @param string $key Configuration key (dot notation supported)
 * @param mixed $default Default value
 * @return mixed Configuration value
 */
function config(string $key = '', mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        // Try multiple paths to find config file
        $possible_paths = [
            dirname(dirname(dirname(__FILE__))) . '/config/app.php',
            __DIR__ . '/../../config/app.php',
            '../config/app.php',
        ];

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $config = require $path;
                break;
            }
        }

        // If still not found, return default configuration
        if ($config === null) {
            $config = [
                'name' => 'Wordle Word Guesser',
                'version' => '2.0.0',
                'description' => 'A word guessing tool for Wordle players',
                'env' => 'development',
                'debug' => true,
                'word_length' => 5,
                'min_position_known_letters' => 1,
                'max_excluded_letters' => 26,
                'letter_start' => 65,
                'letter_end' => 90,
                'logs_path' => dirname(dirname(dirname(__FILE__))) . '/storage/logs',
                'words_path' => dirname(dirname(dirname(__FILE__))) . '/storage/words',
                'views_path' => dirname(dirname(dirname(__FILE__))) . '/resources/views',
                'timezone' => 'UTC',
            ];
        }
    }
    if ($key === '') {
        return $config;
    }

    if ($key === 'app') {
        return $config;
    }

    // Handle dot notation (e.g., 'app.name')
    if (strpos($key, '.') === false) {
        return $config[$key] ?? $default;
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (!is_array($value) || !isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }

    return $value;
}

/**
 * Get base URL
 */
function base_url(): string
{
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $uri = rtrim(str_replace('/public/index.php', '', $uri), '/');

    if (empty($uri)) {
        $uri = '';
    }

    return "{$protocol}://{$host}{$uri}/";
}

/**
 * Get current URL
 */
function current_url(): string
{
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') .
        '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
}

/**
 * Render a view
 */
function view(string $view, array $data = [])
{
    extract($data);

    $viewPath = config('views_path') . '/' . str_replace('.', '/', $view) . '.php';

    if (!file_exists($viewPath)) {
        throw new \Exception("View not found: {$viewPath}");
    }

    ob_start();
    include $viewPath;
    return ob_get_clean();
}

/**
 * Get or set session data
 */
function session(string $key = '', mixed $value = null): mixed
{
    if (!isset($_SESSION)) {
        session_start();
    }

    if ($key === '') {
        return $_SESSION;
    }

    if ($value === null) {
        return $_SESSION[$key] ?? null;
    }

    $_SESSION[$key] = $value;
    return $value;
}

/**
 * Set session flash message
 */
function session_flash(string $type, string $message): void
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get session flash message
 */
function get_flash(string $type, string $default = ''): string
{
    if (!isset($_SESSION)) {
        session_start();
    }

    $message = $_SESSION['flash'][$type] ?? $default;

    if (isset($_SESSION['flash'][$type])) {
        unset($_SESSION['flash'][$type]);
    }

    return $message;
}

/**
 * Sanitize input
 */
function sanitize(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get query parameter
 */
function get_query(string $key, mixed $default = null): mixed
{
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Get POST parameter
 */
function get_post(string $key, mixed $default = null): mixed
{
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Get request instance
 */
function request(): object
{
    static $request = null;

    if ($request === null) {
        $request = new class {
            public function getMethod(): string
            {
                return $_SERVER['REQUEST_METHOD'] ?? 'GET';
            }

            public function isPost(): bool
            {
                return $this->getMethod() === 'POST';
            }

            public function isGet(): bool
            {
                return $this->getMethod() === 'GET';
            }

            public function isAjax(): bool
            {
                return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
            }

            public function input(string $key): mixed
            {
                return $_REQUEST[$key] ?? null;
            }
        };
    }

    return $request;
}

/**
 * Redirect
 */
function redirect(string $url, array $query = []): void
{
    if (!empty($query)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
    }

    header("Location: {$url}");
    exit;
}

/**
 * JSON response
 */
function json_response(array $data, int $status = 200): string
{
    http_response_code($status);
    header('Content-Type: application/json');
    return json_encode($data);
}

/**
 * Logger
 */
function logger(): object
{
    static $logger = null;

    if ($logger === null) {
        $logger = new class {
            protected string $logFile;

            public function __construct()
            {
                $logsPath = config('logs_path');
                if (!is_dir($logsPath)) {
                    @mkdir($logsPath, 0755, true);
                }
                $this->logFile = $logsPath . '/app.log';
            }

            public function info(string $message): void
            {
                $this->log('INFO', $message);
            }

            public function warning(string $message): void
            {
                $this->log('WARNING', $message);
            }

            public function error(string $message): void
            {
                $this->log('ERROR', $message);
            }

            public function debug(string $message): void
            {
                if (config('debug')) {
                    $this->log('DEBUG', $message);
                }
            }

            protected function log(string $level, string $message): void
            {
                $timestamp = date('Y-m-d H:i:s');
                $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
                @file_put_contents($this->logFile, $logEntry, FILE_APPEND);
            }
        };
    }

    return $logger;
}

/**
 * Debug dump
 */
function dd(mixed ...$values): void
{
    echo '<pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow: auto;">';
    foreach ($values as $value) {
        var_dump($value);
    }
    echo '</pre>';
    exit;
}

/**
 * Dump without dying
 */
function dump(mixed ...$values): void
{
    echo '<pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow: auto;">';
    foreach ($values as $value) {
        var_dump($value);
    }
    echo '</pre>';
}

/**
 * Asset URL
 */
function asset(string $path): string
{
    return base_url() . 'assets/' . ltrim($path, '/');
}

/**
 * Check if value is empty but allow 0 and "0"
 */
function is_blank(mixed $value): bool
{
    return $value === null || $value === '' || (is_string($value) && trim($value) === '');
}

/**
 * Validate email
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Escape HTML
 */
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape attribute
 */
function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Highlight Letters present
 */
function highlightLetters(string $word, array $letters): string
{
    // Convert letters like "t,i" into an array
    $checkLetters = array_map('strtolower', $letters);

    $result = '';

    // Process each character in the word
    foreach (mb_str_split($word) as $char) {
        if (in_array(mb_strtolower($char), $checkLetters, true)) {
            $result .= '<span class="highlight">' . htmlspecialchars($char) . '</span>';
        } else {
            $result .= htmlspecialchars($char);
        }
    }

    return $result;
}
