<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PackageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');

Route::get('packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);

Route::get('/check-availability', [AppointmentController::class, 'checkAvailability']);
Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);