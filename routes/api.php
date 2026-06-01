<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DashboardController;

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

// Dashboard - User (Tenggat Waktu, Keuangan, Laporan)
Route::get('/dashboard/deadlines', [DashboardController::class, 'deadlines']);
Route::post('/dashboard/deadlines', [DashboardController::class, 'storeDeadline']);
Route::put('/dashboard/deadlines/{id}', [DashboardController::class, 'updateDeadline']);
Route::delete('/dashboard/deadlines/{id}', [DashboardController::class, 'destroyDeadline']);

Route::get('/dashboard/finances', [DashboardController::class, 'finances']);
Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
Route::post('/dashboard/finances', [DashboardController::class, 'storeFinance']);
Route::delete('/dashboard/finances/{id}', [DashboardController::class, 'destroyFinance']);

Route::get('/dashboard/reports', [DashboardController::class, 'reports']);
Route::post('/dashboard/reports', [DashboardController::class, 'storeReport']);
Route::put('/dashboard/reports/{id}', [DashboardController::class, 'updateReport']);
Route::delete('/dashboard/reports/{id}', [DashboardController::class, 'destroyReport']);

// Rangkum Dokumen (AI Summary)
Route::get('/dashboard/summaries', [DashboardController::class, 'summaries']);
Route::post('/dashboard/summarize', [DashboardController::class, 'summarize']);
Route::get('/dashboard/summaries/{id}', [DashboardController::class, 'summaryStatus']);
Route::delete('/dashboard/summaries/{id}', [DashboardController::class, 'destroySummary']);
