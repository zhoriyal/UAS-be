<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\UserController;

// Artikel - Public (user biasa)
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);

// Artikel - Admin only
Route::get('/admin/articles', [ArticleController::class, 'all']);
Route::post('/admin/articles', [ArticleController::class, 'store']);
Route::put('/admin/articles/{id}', [ArticleController::class, 'update']);
Route::delete('/admin/articles/{id}', [ArticleController::class, 'destroy']);

// Dokumen
Route::get('/documents', [DocumentController::class, 'index']);
Route::post('/documents', [DocumentController::class, 'store']);
Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
Route::get('/documents/{id}/download', [DocumentController::class, 'download']);

// User - Admin only
Route::get('/admin/users', [UserController::class, 'index']);
Route::post('/admin/users/role', [UserController::class, 'updateRole']);

// Dashboard Stats - Admin only
Route::get('/admin/stats', [UserController::class, 'stats']);
