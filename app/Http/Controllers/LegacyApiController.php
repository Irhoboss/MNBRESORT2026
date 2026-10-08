<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Laravel HTTP boundary for the existing API actions. */
final class LegacyApiController
{
    public function __invoke(Request $request): Response
    {
        // The existing API uses PHP sessions and reads its action from $_GET.
        // Keep its request contract intact while Laravel owns URL dispatch.
        $_GET = array_merge($_GET, $request->query());
        $_GET['a'] = $request->query('a', '');
        $_SERVER['REQUEST_METHOD'] = $request->method();

        ob_start();
        require base_path('api.php');
        $body = ob_get_clean();

        return response($body, http_response_code(), ['Content-Type' => 'application/json']);
    }
}
