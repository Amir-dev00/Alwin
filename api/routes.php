<?php

use App\Controllers\Api\CalculatorController;
use App\Controllers\Api\ContactController;
use App\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('site', [SiteController::class, 'site']);
Route::get('home', [SiteController::class, 'home']);
Route::get('products', [SiteController::class, 'products']);
Route::get('projects', [SiteController::class, 'projects']);
Route::get('projects/{slug}', [SiteController::class, 'project']);
Route::get('articles', [SiteController::class, 'articles']);
Route::get('articles/{slug}', [SiteController::class, 'article']);
Route::get('pricing', [CalculatorController::class, 'catalog']);
Route::post('calculator/estimate', [CalculatorController::class, 'estimate']);
Route::post('leads', [CalculatorController::class, 'storeLead'])->middleware('throttle:20,1');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:8,1');
