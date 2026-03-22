<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/foo', function () {
    rescue(fn () => Artisan::call('storage:link'), null, report: false);

    return to_route('home');
});
