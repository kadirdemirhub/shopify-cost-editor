<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF doğrulamasından hariç tutulacak yollar.
     */
    protected $except = [
        'product/update-cost',
    ];
}
