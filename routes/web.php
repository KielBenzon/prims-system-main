<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingpageController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParishionerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PriestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GoogleAuthController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/', [HomeController::class, 'index'])->name('home');


Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

// GOOGLE AUTH ROUTES
Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/call-back', [GoogleAuthController::class, 'callbackGoogle']);


// Dashboard Redirect Based on Role
Route::get('/dashboard', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'Admin'
            ? redirect()->route('admin_dashboard')
            : redirect()->route('parishioner_dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware('Admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin_dashboard');
    Route::get('/admin/documents', [DocumentController::class, 'index'])->name('documents');
    Route::get('/admin/priest', [PriestController::class, 'index'])->name('priests');
    Route::get('/admin/mail', [MailController::class, 'index'])->name('mails');
    // 
    Route::get('/admin/donations', [DonationController::class, 'index'])->name('donations');
    Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
    Route::get('/admin/donation/show', [DonationController::class, 'showDonations'])->name('show_donations');
    // 
    Route::get('/admin/approval_request', [RequestController::class, 'approval_request'])->name('approval_request');
    Route::get('/admin/payment', [TransactionController::class, 'index'])->name('payment');
    Route::get('/release-certificate/{id}', 
    [RequestController::class, 'releaseCertificate']
)->name('certificate.release');

    Route::get('/transactions', [TransactionController::class, 'showTable'])->name('transactions.index');
    Route::get('/admin/announcement', [AnnouncementController::class, 'index'])->name('announcement');
    // Route::get('/admin/payment/show', [DonationController::class, 'showPayment'])->name('show_payment');
    Route::get('/admin/payment', [TransactionController::class, 'showTable'])->name('payment');
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment');

    Route::get('/admin/payment', [TransactionController::class, 'showTable'])->name('payment');
    // Route for parishioner to resubmit payment after a decline
Route::get('/payment/resubmit/{request}', [PaymentController::class, 'resubmit'])
    ->name('payment.resubmit')
    ->middleware(['auth', 'parishioner']); // adjust middleware if needed

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    // Report index (show filter form)
Route::get('/transactions', [TransactionController::class, 'index'])
    ->name('transactions');
    Route::get('/transactions/generate', [TransactionController::class, 'showGenerateForm'])->name('transactions.generate.form');
    Route::get('/transactions/generate', [TransactionController::class, 'generate'])
    ->name('transactions.generate');

    // Route to show the report page with all transactions
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/transactions/report', [TransactionController::class, 'generateReport'])->name('transactions.report');
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');


Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
Route::get('/transactions/export/{format}', [TransactionController::class, 'export'])->name('transactions.export');
Route::middleware(['web', 'Admin'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/report', [TransactionController::class, 'report'])->name('transactions.report');
});



// Route to handle form submission and generate filtered report
Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
Route::post('/transactions/generate', [TransactionController::class, 'generate'])->name('transactions.generate');
Route::get('/transactions/generateQuick/{filter}', [TransactionController::class, 'generateQuick'])->name('transactions.generateQuick');
Route::get('/transactions/export-pdf', [TransactionController::class, 'exportPdf'])->name('transactions.exportPdf');
Route::get('/transactions/export-excel', [TransactionController::class, 'exportExcel'])->name('transactions.exportExcel');

// Generate report (form submit)
Route::post('/transactions/generate', [TransactionController::class, 'generate'])
    ->name('transactions.generate');

// Quick filter routes
    Route::get('/transactions/generate/{filter}', [TransactionController::class, 'generateQuick'])
        ->name('transactions.generateQuick');

    // Export routes
    Route::get('/transactions/export/pdf', [TransactionController::class, 'exportPdf'])
        ->name('transactions.exportPdf');

    Route::get('/transactions/export/excel', [TransactionController::class, 'exportExcel'])
        ->name('transactions.exportExcel');
});



Route::middleware(['Admin'])->group(function () {
    Route::post('/transactions/generate', [TransactionController::class, 'generate'])
        ->name('transactions.generate');
    Route::post('/admin/priest', [PriestController::class, 'store'])->name('priest.store');
    Route::post('/admin/mail', [MailController::class, 'store'])->name('mail.store');
    Route::post('/admin/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/admin/documents/ocr', [DocumentController::class, 'processOCR'])->name('documents.ocr');
    Route::post('/admin/announcement', [AnnouncementController::class, 'store'])->name('announcement.store');
    Route::post('/admin/request_baptismal', [AdminController::class, 'requestBaptismal'])->name('baptismal.store');
    Route::post('/transactions/store', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/review-payment/{id}', [PaymentController::class, 'reviewPayment'])
    ->name('payment.review');

    

    Route::put('/admin/priest/{id}', [PriestController::class, 'update'])->name('priest.update');
    Route::put('/admin/mail/{id}', [MailController::class, 'update'])->name('mail.update');
    Route::put('/admin/documents/{id}', [DocumentController::class, 'update'])->name('documents.update');
    Route::put('/admin/approval_request/{id}', [RequestController::class, 'approve_request'])->name('approval_request.update');
    Route::put('/request/update/{id}', [RequestController::class, 'update'])
    ->name('request.update');
    Route::put('/admin/donations/{id}', [DonationController::class, 'update'])->name('donation.update');
    Route::put('/admin/donations/{id}/status', [DonationController::class, 'updateStatus'])->name('donation.updateStatus');
    Route::put('/admin/announcement/{id}', [AnnouncementController::class, 'update'])->name('announcement.update');
    Route::put('/payment/{id}/status', [PaymentController::class, 'updateStatus'])
    ->name('payment.status.update')
    ->middleware('auth', 'Admin'); // use your middleware
    Route::put('/admin/request/{id}/approve', [RequestController::class, 'approve_request'])
        ->name('approve_request');
    Route::put('/admin/verify-payment/{id}', [RequestController::class, 'verifyPayment'])
    ->name('verify.payment');



    Route::delete('/admin/priest/{id}', [PriestController::class, 'destroy'])->name('priest.destroy');
    Route::delete('/admin/mail/{id}', [MailController::class, 'destroy'])->name('mail.destroy');
    Route::delete('/admin/donations/{id}', [DonationController::class, 'destroy'])->name('donation.destroy');
    Route::delete('/admin/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::delete('/admin/announcement/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.destroy');
    Route::delete('/admin/approval_request/{id}', [RequestController::class, 'destroy'])->name('approval_request.destroy');
    Route::delete('/request/delete/{id}', [RequestController::class, 'destroy'])
    ->name('request.destroy');

    Route::post('/admin/documents/{id}', [DocumentController::class, 'restore'])->name('documents.restore');

});

// Parishioner Routes
Route::middleware('Parishioner')->group(function () {
    Route::get('/parishioner/dashboard', [ParishionerController::class, 'index'])->name('parishioner_dashboard');
    Route::get('/parishioner/request', [RequestController::class, 'index'])->name('request');
    Route::get('/parishioner/donations', action: [DonationController::class, 'parishionerIndex'])->name('parishioner_donation');

    Route::post('/parishioner/request', [RequestController::class, 'store'])->name('request.store');
    Route::post('/parishioner/donations', [DonationController::class, 'store'])->name('donation.store');

    Route::put('/parishioner/request/{id}', [RequestController::class, 'update'])->name('request.update');
    Route::put('/payment/update/{id}/{amount_paid}/{transaction_id}', [PaymentController::class, 'update'])->name('payment.update');

    Route::put('/parishioner/payment/{id}', [RequestController::class, 'updatePayment'])->name('payment.update');

    Route::delete('/parishioner/request/{id}', [RequestController::class, 'destroy'])->name('request.destroy');
});

Route::middleware('Admin')->group(function () {
    Route::get('/admin/requests', [RequestController::class, 'index'])
        ->name('admin.requests');
});


// Authentication Routes
require __DIR__ . '/auth.php';
