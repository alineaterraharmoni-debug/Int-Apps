<?php

use App\Http\Controllers\ReportPdfController;
use App\Livewire\CustomerInsight;
use App\Livewire\Home;
use App\Livewire\OpportunityBoard;
use App\Livewire\ReportDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::prefix('crm')->name('crm.')->group(function () {
    Route::get('/board', OpportunityBoard::class)->name('board');
    Route::get('/report', ReportDashboard::class)->name('report');
    Route::get('/report/export-pdf', [ReportPdfController::class, 'export'])->name('report.export-pdf');
    Route::get('/customers', CustomerInsight::class)->name('customers');
});
