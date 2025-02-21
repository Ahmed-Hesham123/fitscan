<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

// Auth
Route::get('/signup', function () {
    return view('auth.signup'); 
})->name('signup'); 

// Pages
Route::get('/bodymetrics', function () {
    return view('pages.bodymetrics'); 
})->name('bodymetrics'); 

Route::get('/dashboard', function () {
    return view('pages.dashboard'); 
})->name('dashboard'); 

