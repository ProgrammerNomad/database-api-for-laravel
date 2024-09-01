<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;
use App\Http\Controllers\TechnologyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('data', [DataController::class, 'index']);
Route::post('data', [DataController::class, 'store']);
Route::get('data/{id}', [DataController::class, 'show']);
Route::put('data/{id}', [DataController::class, 'update']);
Route::delete('data/{id}', [DataController::class, 'destroy']);

Route::resource('data', DataController::class);



Route::get('data2', [DataController::class, 'index']);
Route::post('data2', [DataController::class, 'store']);
Route::get('data2/{id}', [DataController::class, 'show']);
Route::put('data2/{id}', [DataController::class, 'update']);
Route::delete('data2/{id}', [DataController::class, 'destroy']);

Route::resource('data2', DataController::class);

Route::resource('technologies', TechnologyController::class);

