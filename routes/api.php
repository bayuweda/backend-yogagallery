<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user-profile', [UserController::class, 'profile']);


Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');

Route::get('packages', [PackageController::class, 'index']);
Route::get('/packages/{id}', [PackageController::class, 'show']);
Route::post('/packages/store', [PackageController::class, 'store']);
Route::put('/packages/{id}', [PackageController::class, 'update']);
Route::delete('/packages/{id}', [PackageController::class, 'destroy']);

Route::get('/check-availability', [AppointmentController::class, 'checkAvailability']);
Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);
Route::get('/bookings', [AppointmentController::class, 'getBookings']);
Route::get('bookings/today', [AppointmentController::class, 'getTodayBookings']);


// admin
Route::prefix('admin/appointments')->group(function () {
    Route::post('/', [AdminAppointmentController::class, 'createSlot']);
    Route::get('/', [AdminAppointmentController::class, 'getSlots']);
    Route::delete('/{id}', [AdminAppointmentController::class, 'deleteSlot']);
});
