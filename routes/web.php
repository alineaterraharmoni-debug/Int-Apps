<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ReportPdfController;
use App\Livewire\CustomerInsight;
use App\Livewire\Home;
use App\Livewire\OpportunityBoard;
use App\Livewire\ReportDashboard;
use App\Livewire\TeamMembers;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', Home::class)->name('home');

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/board', OpportunityBoard::class)->name('board');
        Route::get('/report', ReportDashboard::class)->name('report');
        Route::get('/report/export-pdf', [ReportPdfController::class, 'export'])->name('report.export-pdf');
        Route::get('/customers', CustomerInsight::class)->name('customers');
        Route::get('/team', TeamMembers::class)->name('team');
    });
});
