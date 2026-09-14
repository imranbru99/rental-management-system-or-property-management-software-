<?php

use App\Http\Controllers\Api\ApplicationApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiscoveryApiController;
use App\Http\Controllers\Api\ListingApiController;
use App\Http\Controllers\Api\TenantPortalApiController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/listings', [ListingApiController::class, 'index']);
Route::get('/listings/{listing}', [ListingApiController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/listings/{listing}/favorite', [ListingApiController::class, 'favorite']);
    Route::delete('/listings/{listing}/favorite', [DiscoveryApiController::class, 'unfavorite']);
    Route::get('/favorites', [DiscoveryApiController::class, 'favorites']);

    Route::get('/saved-searches', [DiscoveryApiController::class, 'savedSearches']);
    Route::post('/saved-searches', [DiscoveryApiController::class, 'storeSavedSearch']);

    Route::get('/applications', [ApplicationApiController::class, 'index']);
    Route::post('/listings/{listing}/applications', [ApplicationApiController::class, 'store']);

    Route::get('/viewings', [DiscoveryApiController::class, 'viewings']);
    Route::post('/listings/{listing}/viewings', [DiscoveryApiController::class, 'storeViewing']);

    Route::get('/leases', [TenantPortalApiController::class, 'leases']);
    Route::post('/leases/{lease}/sign', [DiscoveryApiController::class, 'signLease']);
    Route::get('/invoices', [TenantPortalApiController::class, 'invoices']);
    Route::post('/invoices/{invoice}/pay', [TenantPortalApiController::class, 'pay']);
    Route::get('/maintenance', [TenantPortalApiController::class, 'maintenance']);
    Route::post('/maintenance', [TenantPortalApiController::class, 'storeMaintenance']);

    Route::get('/announcements', [DiscoveryApiController::class, 'announcements']);
    Route::get('/notices', [DiscoveryApiController::class, 'notices']);
    Route::post('/notices', [DiscoveryApiController::class, 'storeNotice']);
    Route::get('/conversations', [DiscoveryApiController::class, 'conversations']);
    Route::post('/conversations/{conversation}/messages', [DiscoveryApiController::class, 'reply']);
});
