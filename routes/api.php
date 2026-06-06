<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::post('/track/pageview', [TrackingController::class, 'pageview']);
Route::post('/track/click', [TrackingController::class, 'click']);
