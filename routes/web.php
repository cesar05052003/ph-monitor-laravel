<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MedicionWebController;

Route::get('/mediciones', [MedicionWebController::class, 'index']);
Route::post('/simular-medicion', [MedicionWebController::class, 'simular']);
