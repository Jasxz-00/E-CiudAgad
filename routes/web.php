<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DuplicateClaimController as AdminDuplicateClaimController;
use App\Http\Controllers\Admin\MasterDataController as AdminMasterDataController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WFQController as AdminWFQController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Personnel\DashboardController as PersonnelDashboardController;
use App\Http\Controllers\Personnel\RegistrationController as PersonnelRegistrationController;
use App\Http\Controllers\Personnel\RequestController as PersonnelRequestController;
use App\Http\Controllers\Personnel\ResidentRequestController as PersonnelResidentRequestController;
use App\Http\Controllers\Resident\ConcernController as ResidentConcernController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/register/insist-duplicate', [RegisterController::class, 'insistDuplicate'])->name('register.insist-duplicate');
    Route::get('/register/credentials', [RegisterController::class, 'showCredentials'])->name('register.credentials');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendRecovery'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
    Route::post('/forgot-password/verify-phone-otp', [ForgotPasswordController::class, 'verifyPhoneOtp'])->name('password.verify-phone');
    Route::get('/forgot-password/phone-reset', [ForgotPasswordController::class, 'showPhoneResetForm'])->name('password.phone-reset');
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/storage/private/{path}', function (string $path) {
    abort_unless(auth()->check(), 403);
    $fullPath = storage_path('app/private/'.$path);
    abort_unless(file_exists($fullPath), 404);

    return response()->file($fullPath);
})->where('path', '.*')->middleware('auth')->name('storage.private');

Route::middleware(['auth'])->group(function () {
    Route::middleware(['role:resident'])->prefix('resident')->name('resident.')->group(function () {
        Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/requests', [ResidentDashboardController::class, 'requests'])->name('requests');
        Route::get('/requests/{id}', [ResidentDashboardController::class, 'showRequest'])->name('request.show');
        Route::get('/profile', [ResidentDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [ResidentDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/reset-pin', [ResidentDashboardController::class, 'resetPin'])->name('profile.reset-pin');
        Route::get('/new-request', [ResidentDashboardController::class, 'newRequest'])->name('new-request');
        Route::post('/new-request', [ResidentDashboardController::class, 'submitRequest'])->name('submit-request');
        Route::get('/concerns', [ResidentConcernController::class, 'index'])->name('concerns');
        Route::post('/concerns', [ResidentConcernController::class, 'store'])->name('concerns.store');
        Route::get('/concerns/{id}', [ResidentConcernController::class, 'show'])->name('concerns.show');
    });

    Route::middleware(['role:personnel'])->prefix('personnel')->name('personnel.')->group(function () {
        Route::get('/dashboard', [PersonnelDashboardController::class, 'index'])->name('dashboard');
        Route::get('/requests', [PersonnelRequestController::class, 'index'])->name('requests');
        Route::get('/requests/{id}', [PersonnelRequestController::class, 'show'])->name('request.show');
        Route::post('/requests/{id}/review', [PersonnelRequestController::class, 'review'])->name('request.review');
        Route::post('/requests/{id}/approve', [PersonnelRequestController::class, 'approve'])->name('request.approve');
        Route::post('/requests/{id}/reject', [PersonnelRequestController::class, 'reject'])->name('request.reject');
        Route::post('/requests/{id}/complete', [PersonnelRequestController::class, 'complete'])->name('request.complete');

        Route::get('/registrations/create', [PersonnelRegistrationController::class, 'create'])->name('registrations.create');
        Route::post('/registrations', [PersonnelRegistrationController::class, 'store'])->name('registrations.store');
        Route::get('/registrations/{id}', [PersonnelRegistrationController::class, 'show'])->name('registrations.show');

        Route::get('/resident-requests/create', [PersonnelResidentRequestController::class, 'create'])->name('resident-requests.create');
        Route::post('/resident-requests', [PersonnelResidentRequestController::class, 'store'])->name('resident-requests.store');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('/users', AdminUserController::class)->except(['show']);
        Route::get('/wfq', [AdminWFQController::class, 'index'])->name('wfq.index');
        Route::post('/wfq', [AdminWFQController::class, 'store'])->name('wfq.store');
        Route::put('/wfq/{id}', [AdminWFQController::class, 'update'])->name('wfq.update');
        Route::delete('/wfq/{id}', [AdminWFQController::class, 'destroy'])->name('wfq.destroy');
        Route::post('/wfq/recalculate', [AdminWFQController::class, 'recalculate'])->name('wfq.recalculate');
        Route::resource('/document-types', AdminMasterDataController::class)->parameters(['document-types' => 'id'])->names('document-types')->only(['index', 'store', 'update']);
        Route::resource('/purposes', AdminMasterDataController::class)->parameters(['purposes' => 'id'])->names('purposes')->only(['index', 'store', 'update']);
        Route::resource('/categories', AdminMasterDataController::class)->parameters(['categories' => 'id'])->names('categories')->only(['index', 'store', 'update']);
        Route::resource('/announcements', AdminAnnouncementController::class)->except(['show']);
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/accounts', [AdminUserController::class, 'accounts'])->name('accounts.index');
        Route::get('/staff', [AdminUserController::class, 'staff'])->name('staff.index');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{id}', [AdminRequestController::class, 'show'])->name('requests.show');
        Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        Route::get('/duplicate-claims', [AdminDuplicateClaimController::class, 'index'])->name('duplicate-claims.index');
        Route::get('/duplicate-claims/{id}', [AdminDuplicateClaimController::class, 'show'])->name('duplicate-claims.show');
        Route::post('/duplicate-claims/{id}/approve', [AdminDuplicateClaimController::class, 'approve'])->name('duplicate-claims.approve');
        Route::post('/duplicate-claims/{id}/dismiss', [AdminDuplicateClaimController::class, 'dismiss'])->name('duplicate-claims.dismiss');
    });
});
