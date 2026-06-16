<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TorController;
use App\Http\Controllers\Api\LpjController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AnnualBudgetController;
use App\Http\Controllers\Api\ActivityCategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\UserController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);

    // Account
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/account', [AccountController::class, 'update']);

    // TOR routes
    Route::apiResource('tor', TorController::class);
    Route::post('tor/{id}/submit', [TorController::class, 'submit']);
    Route::post('tor/{id}/review-secretary', [TorController::class, 'reviewBySecretary']);
    Route::post('tor/{id}/verify-admin', [TorController::class, 'verifyByAdmin']);
    Route::post('tor/{id}/approve-head', [TorController::class, 'approveByHead']);

    // LPJ routes
    Route::apiResource('lpj', LpjController::class);
    Route::post('lpj/{id}/submit', [LpjController::class, 'submit']);
    Route::post('lpj/{id}/review-secretary', [LpjController::class, 'reviewBySecretary']);
    Route::post('lpj/{id}/verify-admin', [LpjController::class, 'verifyByAdmin']);
    Route::post('lpj/{id}/approve-head', [LpjController::class, 'approveByHead']);
    Route::get('lpj/{id}/compare-budget', [LpjController::class, 'compareBudget']);

    // Annual Budget routes (admin only)
    Route::get('annual-budgets', [AnnualBudgetController::class, 'index']);
    Route::get('annual-budgets/{id}', [AnnualBudgetController::class, 'show']);
    Route::post('annual-budgets', [AnnualBudgetController::class, 'store'])->middleware('role:admin jurusan');
    Route::put('annual-budgets/{id}', [AnnualBudgetController::class, 'update'])->middleware('role:admin jurusan');
    Route::delete('annual-budgets/{id}', [AnnualBudgetController::class, 'destroy'])->middleware('role:admin jurusan');

    // User Management routes (admin only)
    Route::apiResource('users', UserController::class)->middleware('role:admin jurusan');

    // Activity Category routes
    Route::get('activity-categories', [ActivityCategoryController::class, 'index']);
    Route::get('activity-categories/{id}', [ActivityCategoryController::class, 'show']);

    // Dashboard routes
    Route::get('dashboard/summary', [DashboardController::class, 'getSummary']);
    Route::get('dashboard/filter', [DashboardController::class, 'getFilteredData']);
    Route::get('dashboard/charts', [DashboardController::class, 'getChartData']);
    Route::get('dashboard/annual-budget', [DashboardController::class, 'getAnnualBudgetData']);
    Route::post('dashboard/refresh-cache', [DashboardController::class, 'refreshCache']);

    // LPJ Pre-fill routes
    Route::get('lpj/prefill/{torId}', [LpjController::class, 'getPreFillData']);
    Route::post('lpj/create-with-prefill', [LpjController::class, 'createWithPreFill']);

    // Attachment routes
    Route::post('attachments/upload', [AttachmentController::class, 'upload']);
    Route::get('attachments/download/{id}', [AttachmentController::class, 'download']);
    Route::delete('attachments/{id}', [AttachmentController::class, 'destroy']);
});

