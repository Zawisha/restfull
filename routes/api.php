<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;


Route::post('/add_product', [ApiController::class, 'addProduct']);
Route::post('/edit_product', [ApiController::class, 'editProduct']);
Route::post('/delete_category', [ApiController::class, 'deleteCategory']);
Route::post('/get_product', [ApiController::class, 'getProduct']);
Route::post('/add_category', [ApiController::class, 'addCategory']);
