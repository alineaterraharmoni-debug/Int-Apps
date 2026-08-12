<?php

use App\Http\Controllers\ReportPdfController;
use App\Livewire\OpportunityBoard;
use App\Livewire\ReportDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', OpportunityBoard::class)->name('board');
Route::get('/report', ReportDashboard::class)->name('report');
Route::get('/report/export-pdf', [ReportPdfController::class, 'export'])->name('report.export-pdf');
