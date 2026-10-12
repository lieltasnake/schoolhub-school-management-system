<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/settings/school', [SchoolSettingController::class, 'edit'])->name('settings.school.edit');
    Route::put('/settings/school', [SchoolSettingController::class, 'update'])->name('settings.school.update');
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::post('/academic-years/{academic_year}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::resource('academic-years', AcademicYearController::class)->except(['destroy']);
    Route::post('/terms/{term}/activate', [TermController::class, 'activate'])->name('terms.activate');
    Route::resource('terms', TermController::class)->except(['destroy']);
    Route::resource('branches', BranchController::class)->except(['destroy']);
    Route::resource('users', UserController::class)->except(['destroy']);
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
