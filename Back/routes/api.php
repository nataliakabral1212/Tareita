<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicioController;

Route::get('/clientes',      [ClienteController::class, 'index']);
Route::get('/clientes/{id}', [ClienteController::class, 'show']);
Route::post('/clientes',     [ClienteController::class, 'store']);

Route::get('/categorias',      [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
Route::post('/categorias',     [CategoriaController::class, 'store']);

Route::get('/servicios',       [ServicioController::class, 'index']);
Route::get('/servicios/{id}',  [ServicioController::class, 'show']);
Route::post('/servicios',      [ServicioController::class, 'store']);
