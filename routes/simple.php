<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Laravel Meat Shop POS is working!';
});

Route::get('/test', function () {
    return 'Simple test route works!';
});
