<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Stateless JSON endpoint: no session, no CSRF, so it is not part of the `web` group.
Route::post('/chat', ChatController::class)->middleware('throttle:chat');
