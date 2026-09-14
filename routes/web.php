<?php

use App\Http\Controllers\PublicListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicListingController::class, 'home'])->name('home');
Route::get('/listings', [PublicListingController::class, 'index'])->name('listings.index');
Route::get('/listings/{listing}', [PublicListingController::class, 'show'])->name('listings.show');
Route::post('/listings/{listing}/apply', [PublicListingController::class, 'apply'])->name('listings.apply');
Route::post('/listings/{listing}/viewings', [PublicListingController::class, 'requestViewing'])->name('listings.viewings.store');
Route::post('/listings/{listing}/favorite', [PublicListingController::class, 'favorite'])->name('listings.favorite');
Route::post('/saved-searches', [PublicListingController::class, 'saveSearch'])->name('saved-searches.store');
