<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SmsConfigController;
use App\Http\Controllers\SmsSettingController;
use App\Http\Controllers\EmailConfigController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailSettingController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CronSettingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::resource('employees', EmployeeController::class);
    Route::get('upcoming-birthdays', [EmployeeController::class, 'upcomingBirthdays'])->name('employees.upcoming-birthdays');
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/preview/{id}', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');

    Route::resource('sms-templates', SmsTemplateController::class);
    Route::get('sms-templates/preview/{id}', [SmsTemplateController::class, 'preview'])
        ->name('sms-templates.preview');

// Email Configuration Routes
    // SMTP settings are read-only here - they live in .env
    Route::get('/email-config', [EmailConfigController::class, 'index'])->name('email-config.index');
    Route::post('/email-config/test', [EmailConfigController::class, 'test'])->name('email-config.test');

    // SMS Configuration Routes
    // Bulk employee import (must be declared before the employees resource routes)
    Route::get('employees-bulk', [EmployeeController::class, 'bulk'])->name('employees.bulk');
    Route::post('employees-bulk', [EmployeeController::class, 'bulkUpload'])->name('employees.bulkUpload');
    Route::get('employees-bulk/sample', [EmployeeController::class, 'bulkSample'])->name('employees.bulkSample');

    Route::get('/sms-config', [SmsConfigController::class, 'index'])->name('sms-config.index');
    Route::put('/sms-config', [SmsConfigController::class, 'update'])->name('sms-config.update');
    Route::post('/sms-config/test', [SmsConfigController::class, 'test'])->name('sms-config.test');
    Route::get('/sms-config/balance', [SmsConfigController::class, 'balance'])->name('sms-config.balance');
    Route::get('/sms-config/message-status', [SmsConfigController::class, 'messageStatus'])->name('sms-config.message-status');

    Route::get('email-settings', [EmailSettingController::class, 'index'])->name('email-settings.index');
    Route::post('email-settings', [EmailSettingController::class, 'store'])->name('email-settings.store');

    Route::get('sms-settings', [SmsSettingController::class, 'index'])->name('sms-settings.index');
    Route::post('sms-settings', [SmsSettingController::class, 'store'])->name('sms-settings.store');

    // Automation / Cron schedule
    Route::get('cron-settings', [CronSettingController::class, 'index'])->name('cron-settings.index');
    Route::post('cron-settings', [CronSettingController::class, 'store'])->name('cron-settings.store');
    Route::post('cron-settings/run-now', [CronSettingController::class, 'runNow'])->name('cron-settings.run-now');

    Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    Route::get('reports/summary', [ReportController::class, 'index'])->name('reports.summary');
    Route::post('reports/send-email', [ReportController::class, 'sendEmailReport'])->name('reports.send-email');
});


// Public self-registration is disabled: this is a private admin portal.
// Create admin accounts on the server with: php artisan make:admin
Auth::routes(['register' => false]);

Route::get('/', function () {
    return redirect('login');
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
