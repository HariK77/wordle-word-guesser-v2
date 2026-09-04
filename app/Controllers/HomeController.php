<?php

/**
 * HomeController - Handles home page requests
 * Main controller for displaying the word guesser interface
 */

namespace App\Controllers;

use App\Requests\GuessWordRequest;
use App\Services\WordGuesserService;
use App\Models\GuessSession;

class HomeController extends BaseController
{
    /**
     * Show the home page
     */
    public function index()
    {
        $request = new GuessWordRequest();

        // Validate request
        if (!$request->validate() && !empty($_GET)) {
            return view('home', [
                'words' => [],
                'count' => 0,
                'error' => $request->getFirstError(),
                'errors' => $request->errors(),
                'success' => null,
                'query' => [],
            ]);
        }

        // Get validated data
        $data = $request->validated();
        $words = [];
        $message = null;

        // Only process if at least 1 known letter provided
        if (!empty(array_filter($data['position_known_letters']))) {
            $service = new WordGuesserService(
                $data['excluded_letters'],
                $data['position_known_letters'],
                $data["known_letters"]
            );

            $words = $service->guess();
            sort($words);

            if (!empty($words)) {
                $count = count($words);
                $message = "Found {$count} matching word(s)!";
                logger()->info("Words guessed: {$count} matches found");
            } else {
                $message = "No words found matching your criteria. Try adjusting your clues!";
            }
        }

        return view('home', [
            'words' => $words,
            'count' => count($words),
            'error' => null,
            'errors' => [],
            'success' => !empty($words) ? $message : null,
            'query' => $data,
        ]);
    }

    /**
     * Clear the session
     */
    public function clear()
    {
        $session = new GuessSession();
        $session->clear()->save();

        session_flash('success', 'Cleared all inputs!');
        redirect(base_url());
    }

    /**
     * Get stats
     */
    public function stats()
    {
        $session = new GuessSession();

        return json_response([
            'position_known_letters' => $session->getKnownLetters(),
            'excluded_letters' => $session->getExcludedLetters(),
            'guessed_words_count' => $session->countGuessedWords(),
        ]);
    }
}
