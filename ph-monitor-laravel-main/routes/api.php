<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MedicionController;

Route::post('/mediciones', [MedicionController::class, 'store']);
