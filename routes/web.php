<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/token', function () {
    return csrf_token(); 
});

Route::post('/upload', [EventController::class, 'uploadRoster']);

Route::get('/events', [EventController::class, 'index']);

Route::get('/events/flights/stand-by/next-week', [EventController::class, 'getSBYEventsForNextWeek']);

Route::get('/events/flights/next-week', [EventController::class, 'getEventsForNextWeek']);

Route::get('/events/flights/{start_date}/{end_date}', [EventController::class, 'getEventsBetweenDates']);

Route::get('/events/flights/{location}', [EventController::class, 'getEventsForGivenLocation']);






