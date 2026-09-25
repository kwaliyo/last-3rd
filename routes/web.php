<?php

use App\Livewire\NightTimeComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The Last 3rd application routes. Both root and /night point to
| the full-page Livewire NightTimeComponent.
|
*/

Route::get('/', NightTimeComponent::class)->name('home');
Route::get('/night', NightTimeComponent::class)->name('night');
