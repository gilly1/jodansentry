<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', App\Livewire\Auth\Login::class)->name('login')->middleware('guest');

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', App\Livewire\Dashboard\Index::class)
        ->name('dashboard');

    // Payments
    Route::get('/payments/upload', App\Livewire\Payments\UploadBatch::class)
        ->name('payments.upload');

    Route::get('/payments/batches', App\Livewire\Payments\BatchList::class)
        ->name('payments.batches');

    Route::get('/payments/batches/{batch}', App\Livewire\Payments\BatchDetails::class)
        ->name('payments.batches.show');

    Route::get('/payments/approvals', App\Livewire\Payments\PendingApprovals::class)
        ->name('payments.approvals');

    // Reports
    Route::get('/reports/payments', App\Livewire\Reports\PaymentReports::class)
        ->name('reports.payments');

    // Audit
    Route::get('/audit-logs', App\Livewire\AuditLogs\Index::class)
        ->name('audit-logs.index');

    // Admin
    Route::get('/admin/users', App\Livewire\Admin\Users\Index::class)
        ->name('admin.users.index');

    Route::get('/admin/users/create', App\Livewire\Admin\Users\Create::class)
        ->name('admin.users.create');

    Route::get('/admin/users/{user}/edit', App\Livewire\Admin\Users\Edit::class)
        ->name('admin.users.edit');
});
