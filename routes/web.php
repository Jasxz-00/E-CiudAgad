<?php


use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DuplicateClaimController as AdminDuplicateClaimController;
use App\Http\Controllers\Admin\MasterDataController as AdminMasterDataController;
use App\Http\Controllers\Admin\QueueScheduleController as AdminQueueScheduleController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WFQController as AdminWFQController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\DocumentSettingController as AdminDocumentSettingController;
use App\Http\Controllers\Admin\ProfileChangeController as AdminProfileChangeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Personnel\DashboardController as PersonnelDashboardController;
use App\Http\Controllers\Personnel\RegistrationController as PersonnelRegistrationController;
use App\Http\Controllers\Personnel\RequestController as PersonnelRequestController;
use App\Http\Controllers\Personnel\ResidentRequestController as PersonnelResidentRequestController;
use App\Http\Controllers\Queue\QueueActionController;
use App\Http\Controllers\Resident\ConcernController as ResidentConcernController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('landing');
})->name('home');
Route::get('/queue-status', [App\Http\Controllers\Queue\QueueMonitorController::class, 'publicStatus'])->name('queue.public-status');

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        LoginController::class,
        'showResidentLoginForm'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'residentLogin'
    ])->name('resident.login');


    Route::get('/personnel/login', [
        LoginController::class,
        'showPersonnelLoginForm'
    ])->name('personnel.login.form');

    Route::post('/personnel/login', [
        LoginController::class,
        'personnelLogin'
    ])->name('personnel.login');


    Route::get('/admin/login', [
        LoginController::class,
        'showAdminLoginForm'
    ])->name('admin.login.form');

    Route::post('/admin/login', [
        LoginController::class,
        'adminLogin'
    ])->name('admin.login');

    Route::get('/register', [
        RegisterController::class,
        'showRegistrationForm'
    ])->name('register');

    Route::post('/register', [
        RegisterController::class,
        'register'
    ])->middleware('throttle:10,1');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/register/insist-duplicate', [RegisterController::class, 'insistDuplicate'])->middleware('throttle:10,1')->name('register.insist-duplicate');

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

    $user = auth()->user();

    if (! in_array($user->role, ['admin', 'personnel'])) {
        abort(403);
    }

    if (Storage::disk('private')->exists($path)) {
        return Storage::disk('private')->response($path);
    }

    abort(404);
})->where('path', '.*')->middleware('auth')->name('storage.private');

Route::middleware(['auth'])->group(function () {
    Route::get('/request/{control_number}', [App\Http\Controllers\Queue\QueueMonitorController::class, 'show'])->name('request.show');
    Route::get('/verify/{token}', [VerificationController::class, 'show'])->name('verification.show');
    
    Route::middleware(['role:personnel'])->prefix('queue')->name('queue.')->group(function () {
        Route::get('/monitor', [App\Http\Controllers\Queue\QueueMonitorController::class, 'index'])->name('monitor');
        Route::get('/status', [App\Http\Controllers\Queue\QueueMonitorController::class, 'statusJson'])->name('status');
        Route::get('/personnel', [App\Http\Controllers\Queue\QueueMonitorController::class, 'personnel'])->name('personnel');
        Route::post('/{control_number}/process', [QueueActionController::class, 'callNext'])->name('process');
        Route::post('/{control_number}/call-next', [QueueActionController::class, 'callNext'])->name('call-next');
        Route::post('/{control_number}/hold', [QueueActionController::class, 'hold'])->name('hold');
        Route::post('/{control_number}/resume', [QueueActionController::class, 'resume'])->name('resume');
        Route::post('/{control_number}/skip', [QueueActionController::class, 'skip'])->name('skip');
        Route::post('/{control_number}/ready', [QueueActionController::class, 'markReady'])->name('ready');
        Route::post('/{control_number}/release', [QueueActionController::class, 'release'])->name('release');
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::match(['get', 'post'], '/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

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

    Route::middleware(['auth', 'role:personnel', 'staff-locale'])->prefix('personnel')->name('personnel.')->group(function () {
            Route::get('/dashboard', [PersonnelDashboardController::class, 'index'])->name('dashboard');
            Route::get('/requests', [PersonnelRequestController::class, 'index'])->name('requests');
            Route::get('/requests/{id}', [PersonnelRequestController::class, 'show'])->name('request.show');
            Route::post('/requests/{id}/review', [PersonnelRequestController::class, 'review'])->name('request.review');
            Route::post('/requests/{id}/approve', [PersonnelRequestController::class, 'approve'])->name('request.approve');
            Route::post('/requests/{id}/reject', [PersonnelRequestController::class, 'reject'])->name('request.reject');
            Route::post('/requests/{id}/complete', [PersonnelRequestController::class, 'complete'])->name('request.complete');
            Route::post('/requests/{id}/release', [PersonnelRequestController::class, 'release'])->name('request.release');

            Route::get('/registrations/create', [PersonnelRegistrationController::class, 'create'])->name('registrations.create');
            Route::post('/registrations', [PersonnelRegistrationController::class, 'store'])->name('registrations.store');
            Route::get('/registrations/{id}', [PersonnelRegistrationController::class, 'show'])->name('registrations.show');

            Route::get('/resident-requests/create', [PersonnelResidentRequestController::class, 'create'])->name('resident-requests.create');
            Route::post('/resident-requests', [PersonnelResidentRequestController::class, 'store'])->name('resident-requests.store');

            Route::get('/documents/print/{id}', [Personnel\RequestController::class, 'print'])->name('documents.print');
            Route::get('/documents/print/{id}', [PersonnelRequestController::class, 'print'])->name('documents.print');
        });

    Route::middleware(['auth', 'role:admin', 'staff-locale'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('/users', AdminUserController::class)->except(['show']);
        Route::get('/wfq', [AdminWFQController::class, 'index'])->name('wfq.index');
        Route::post('/wfq', [AdminWFQController::class, 'store'])->name('wfq.store');
        Route::put('/wfq/{id}', [AdminWFQController::class, 'update'])->name('wfq.update');
        Route::delete('/wfq/{id}', [AdminWFQController::class, 'destroy'])->name('wfq.destroy');
        Route::post('/wfq/recalculate', [AdminWFQController::class, 'recalculate'])->name('wfq.recalculate');
        Route::get('/queue-schedule', [AdminQueueScheduleController::class, 'index'])->name('queue-schedule.index');
        Route::put('/queue-schedule/{id}', [AdminQueueScheduleController::class, 'update'])->name('queue-schedule.update');
        Route::post('/queue-schedule/{id}/holidays', [AdminQueueScheduleController::class, 'storeHoliday'])->name('queue-schedule.holidays.store');
        Route::delete('/queue-schedule/{id}/holidays/{holidayId}', [AdminQueueScheduleController::class, 'destroyHoliday'])->name('queue-schedule.holidays.destroy');
        Route::resource('/document-types', AdminMasterDataController::class)->parameters(['document-types' => 'id'])->names('document-types')->only(['index', 'store', 'update']);
        Route::resource('/purposes', AdminMasterDataController::class)->parameters(['purposes' => 'id'])->names('purposes')->only(['index', 'store', 'update']);
        Route::resource('/categories', AdminMasterDataController::class)->parameters(['categories' => 'id'])->names('categories')->only(['index', 'store', 'update']);
        
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

        Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
        Route::get('/verifications/{id}', [AdminVerificationController::class, 'show'])->name('verifications.show');
        Route::post('/verifications/{id}/verify', [AdminVerificationController::class, 'verify'])->name('verifications.verify');
        Route::post('/verifications/{id}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
        Route::get('/document-settings', [AdminDocumentSettingController::class, 'index']) ->name('document-settings.index');
        Route::post('/document-settings', [AdminDocumentSettingController::class, 'update'])->name('document-settings.update');
        Route::get('/profile-changes', [AdminProfileChangeController::class, 'index'])->name('profile-changes.index');
        Route::post('/profile-changes/{id}/approve', [AdminProfileChangeController::class, 'approve'])->name('profile-changes.approve');
        Route::post('/profile-changes/{id}/reject', [AdminProfileChangeController::class, 'reject'])->name('profile-changes.reject');
        });
});
