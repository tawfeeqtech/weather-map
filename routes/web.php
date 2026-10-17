<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeatherController::class, 'index'])->name('weather.index');
Route::post('/weather/search', [WeatherController::class, 'search'])->name('weather.search');
Route::post('/weather/locate', [WeatherController::class, 'locate'])->name('weather.locate');
