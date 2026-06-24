<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    // CSRF protection is disabled for the LTI URLs, just like it would be for any other POST request coming from
    // outside your app.
    protected $except = [
        '/lti',
        '/lti/*',
    ];
}
