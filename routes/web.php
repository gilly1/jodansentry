<?php

use App\Livewire\Admin\CreateUser;
use App\Livewire\Admin\EditUser;
use App\Livewire\Admin\RolesPermissions;
use App\Livewire\Admin\UserList;
use App\Livewire\AuditLogs;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\Mpesa\TransactionStatus;
use App\Livewire\Payments\BatchDetails;
use App\Livewire\Payments\BatchList;
use App\Livewire\Payments\PendingApprovals;
use App\Livewire\Payments\UploadBatch;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', Login::class)->name('login');
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Payments
    Route::get('/payments/upload', UploadBatch::class)->name('payments.upload');
    Route::get('/payments/batches', BatchList::class)->name('payments.batches');
    Route::get('/payments/batches/{batch}', BatchDetails::class)->name('payments.batch.show');
    Route::get('/payments/approvals', PendingApprovals::class)->name('payments.approvals');

    // M-Pesa
    Route::get('/mpesa/transaction-status', TransactionStatus::class)->name('mpesa.transaction-status');

    // Audit Logs
    Route::get('/audit-logs', AuditLogs::class)->name('audit-logs');

    // Admin
    Route::middleware('can:manage users')->prefix('admin')->group(function () {
        Route::get('/users', UserList::class)->name('admin.users');
        Route::get('/users/create', CreateUser::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', EditUser::class)->name('admin.users.edit');
        Route::get('/roles', RolesPermissions::class)->name('admin.roles');
    });
});
