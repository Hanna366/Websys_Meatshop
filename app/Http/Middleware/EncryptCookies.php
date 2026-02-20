<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    // You may add cookies that should not be encrypted here
    protected $except = [
        //
    ];
}
