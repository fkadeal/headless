<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['404'];
});

require __DIR__ . '/auth.php';
