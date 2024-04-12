<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/token', function () {
    return csrf_token(); 
});

// Grouping event-related routes under a common prefix and middleware if necessary
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('events.index');

    // Upload route
    Route::post('/upload', [EventController::class, 'uploadRoster'])->name('events.upload');

    // Specific routes for stand-by and next week using clearer paths
    Route::get('/flights/stand-by/next-week', [EventController::class, 'getSBYEventsForNextWeek'])->name('events.standby.nextweek');
    Route::get('/flights/next-week', [EventController::class, 'getEventsForNextWeek'])->name('events.nextweek');

    // Using a more specific route for locations to avoid overlap
    Route::get('/flights/location/{location}', [EventController::class, 'getEventsForGivenLocation'])->name('events.location');

    // Ensure this route does not overlap with the location route by making clear distinctions in the path
    Route::get('/flights/{start_date}/{end_date}', [EventController::class, 'getEventsBetweenDates'])->name('events.dates');
});


