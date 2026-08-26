<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentPdfController;
use App\Http\Controllers\OpportunityExportController;
use App\Http\Controllers\ReportPdfController;
use App\Livewire\AccountManagement;
use App\Livewire\ChangePassword;
use App\Livewire\CreateAccount;
use App\Livewire\CustomerInsight;
use App\Livewire\DocumentForm;
use App\Livewire\DocumentList;
use App\Livewire\Home;
use App\Livewire\OpportunityBoard;
use App\Livewire\ReportDashboard;
use App\Livewire\RoleManagement;
use App\Livewire\TeamMembers;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', Home::class)->name('home');
    Route::get('/account/password', ChangePassword::class)->name('account.password');

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::middleware('permission:crm.view')->group(function () {
            Route::get('/board', OpportunityBoard::class)->name('board');
            Route::get('/board/export', [OpportunityExportController::class, 'export'])->name('board.export');
        });
        Route::middleware('permission:report.view')->group(function () {
            Route::get('/report', ReportDashboard::class)->name('report');
            Route::get('/report/export-pdf', [ReportPdfController::class, 'export'])->name('report.export-pdf');
        });
        Route::middleware('permission:customer.view')->group(function () {
            Route::get('/customers', CustomerInsight::class)->name('customers');
        });
        Route::middleware('permission:team.view')->group(function () {
            Route::get('/team', TeamMembers::class)->name('team');
        });
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::middleware('permission:document.view')->group(function () {
            Route::get('/', DocumentList::class)->name('index');
            Route::get('/{id}/pdf', [DocumentPdfController::class, 'export'])->name('pdf');
        });
        Route::middleware('permission:document.manage')->group(function () {
            Route::get('/create/{type}', DocumentForm::class)->name('create');
            Route::get('/{id}/edit', DocumentForm::class)->name('edit');
        });
    });

    Route::middleware('permission:accounts.create')->group(function () {
        Route::get('/account/create', CreateAccount::class)->name('account.create');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/accounts', AccountManagement::class)->name('accounts');
        Route::get('/roles', RoleManagement::class)->name('roles');
    });
});
