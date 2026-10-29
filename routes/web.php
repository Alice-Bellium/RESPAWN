<?php

use Illuminate\Support\Facades\Route;

Route::view('/{path?}', 'welcome')
    ->where('path', '(?!api(?:/|$)|build(?:/|$)|images(?:/|$)).*')
    ->name('storefront');
