<?php

/**
 * ApiController - Handles API requests
 * Provides JSON API endpoints for the application
 */

namespace App\Controllers;

use App\Requests\GuessWordRequest;
use App\Services\WordGuesserService;

class ApiController extends BaseController
{
    /**
     * Guess words endpoint
     * POST /api/guess
     */
    public function guess()
    {
        if (!$this->isJson()) {
            return $this->json(['error' => 'Invalid request format'], 400);
        }

        $input = $this->getJsonInput();
        $_GET = $input; // Set GET data for validation
        $request = new GuessWordRequest();

        if (!$request->validate()) {
            return $this->json([
                'success' => false,
                'errors' => $request->errors(),
            ], 422);
        }

        $data = $request->validated();
        $service = new WordGuesserService(
            $data['excluded_letters'],
            $data['position_known_letters'],
            $data["known_letters"]
        );

        $words = $service->guess();
        sort($words);

        return $this->json([
            'success' => true,
            'words' => $words,
            'count' => count($words),
            'position_known_letters' => $data['position_known_letters'],
            'excluded_letters' => $data['excluded_letters'],
        ]);
    }

    /**
     * Check if request is JSON
     */
    protected function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Get JSON input
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
