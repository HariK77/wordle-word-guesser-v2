<?php
/**
 * BaseController - Base class for all controllers
 * Provides common functionality for all controllers
 */

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Request instance
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = request();
    }

    /**
     * Render a view
     */
    protected function render(string $view, array $data = [])
    {
        return view($view, $data);
    }

    /**
     * Redirect to a route
     */
    protected function redirect(string $url, array $query = [])
    {
        return redirect($url, $query);
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200)
    {
        return json_response($data, $status);
    }

    /**
     * Abort with error
     */
    protected function abort(int $code, string $message = '')
    {
        http_response_code($code);
        echo $message ?: "Error {$code}";
        exit;
    }
}
