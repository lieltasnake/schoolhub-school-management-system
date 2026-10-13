<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentTypeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookCirculationController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookTypeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeDiscountController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeEntryController;
use App\Http\Controllers\GradePromotionController;
use App\Http\Controllers\GradeScaleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LibraryMemberController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RegistrationPromotionController;
use App\Http\Controllers\RegistrationSetupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\SchoolHourController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentGroupController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\StudentTypeController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\TimetableController;
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
    Route::resource('grades', GradeController::class);
    Route::resource('sections', SectionController::class);

    // 1. Registration
    Route::get('/registrations/promotion', [RegistrationPromotionController::class, 'index'])->name('registrations.promotion');
    Route::post('/registrations/promotion', [RegistrationPromotionController::class, 'promote'])->name('registrations.promotion.promote');
    Route::get('/registrations/setup', [RegistrationSetupController::class, 'index'])->name('registrations.setup');
    Route::get('/registrations/setup/create', [RegistrationSetupController::class, 'create'])->name('registrations.setup.create');
    Route::post('/registrations/setup', [RegistrationSetupController::class, 'store'])->name('registrations.setup.store');
    Route::get('/registrations/setup/{setup}/edit', [RegistrationSetupController::class, 'edit'])->name('registrations.setup.edit');
    Route::put('/registrations/setup/{setup}', [RegistrationSetupController::class, 'update'])->name('registrations.setup.update');
    Route::delete('/registrations/setup/{setup}', [RegistrationSetupController::class, 'destroy'])->name('registrations.setup.destroy');
    Route::post('/registrations/{registration}/approve', [StudentRegistrationController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{registration}/reject', [StudentRegistrationController::class, 'reject'])->name('registrations.reject');
    Route::resource('registrations', StudentRegistrationController::class);

    // 2. Students
    Route::post('/students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
    Route::resource('students', StudentController::class);
    Route::resource('enrollments', StudentEnrollmentController::class);
    Route::resource('student-types', StudentTypeController::class);
    Route::post('/student-groups/{student_group}/members', [StudentGroupController::class, 'addMember'])->name('student-groups.members.add');
    Route::delete('/student-groups/{student_group}/members/{student}', [StudentGroupController::class, 'removeMember'])->name('student-groups.members.remove');
    Route::resource('student-groups', StudentGroupController::class);
    Route::resource('grade-promotions', GradePromotionController::class)->only(['index', 'create', 'store']);

    // 3. Teachers & Subjects
    Route::resource('teachers', TeacherController::class);
    Route::resource('teacher-assignments', TeacherAssignmentController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('subjects', SubjectController::class);

    // 4. Periods & Hours
    Route::resource('periods', PeriodController::class);
    Route::resource('school-hours', SchoolHourController::class);

    // 5. Classrooms & Timetable
    Route::resource('classrooms', ClassroomController::class);
    Route::resource('timetables', TimetableController::class);

    // 6. Roster
    Route::get('/roster/students', [RosterController::class, 'students'])->name('roster.students');
    Route::get('/roster/teachers', [RosterController::class, 'teachers'])->name('roster.teachers');
    Route::get('/roster/timetable', [RosterController::class, 'classTimetable'])->name('roster.timetable');
    Route::get('/roster/grades', [RosterController::class, 'studentGrades'])->name('roster.grades');

    // 7. Attendance
    Route::get('/attendance/students', [StudentAttendanceController::class, 'index'])->name('student-attendance.index');
    Route::get('/attendance/students/take', [StudentAttendanceController::class, 'create'])->name('student-attendance.create');
    Route::post('/attendance/students', [StudentAttendanceController::class, 'store'])->name('student-attendance.store');
    Route::delete('/attendance/students/{studentAttendance}', [StudentAttendanceController::class, 'destroy'])->name('student-attendance.destroy');

    Route::get('/attendance/staff', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::get('/attendance/staff/take', [StaffAttendanceController::class, 'create'])->name('staff-attendance.create');
    Route::post('/attendance/staff', [StaffAttendanceController::class, 'store'])->name('staff-attendance.store');
    Route::delete('/attendance/staff/{staffAttendance}', [StaffAttendanceController::class, 'destroy'])->name('staff-attendance.destroy');

    // 8. Library
    Route::resource('libraries', LibraryController::class);
    Route::resource('book-types', BookTypeController::class);
    Route::resource('books', BookController::class);
    Route::resource('library-members', LibraryMemberController::class);
    Route::post('/circulations/{circulation}/return', [BookCirculationController::class, 'returnBook'])->name('circulations.return');
    Route::resource('circulations', BookCirculationController::class)->only(['index', 'create', 'store', 'destroy']);

    // 9. Exams & Assessments
    Route::resource('assessment-types', AssessmentTypeController::class);
    Route::resource('grade-scales', GradeScaleController::class);
    Route::resource('assessments', AssessmentController::class);
    Route::get('/grade-entries', [GradeEntryController::class, 'index'])->name('grade-entries.index');
    Route::get('/grade-entries/{assessment}/edit', [GradeEntryController::class, 'edit'])->name('grade-entries.edit');
    Route::put('/grade-entries/{assessment}', [GradeEntryController::class, 'update'])->name('grade-entries.update');
    Route::get('/grade-entries/{assessment}', [GradeEntryController::class, 'show'])->name('grade-entries.show');

    // 10. Finance & Administration
    Route::resource('fee-categories', FeeCategoryController::class);
    Route::resource('fee-structures', FeeStructureController::class);
    Route::resource('fee-discounts', FeeDiscountController::class);
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::resource('invoices', InvoiceController::class);
    Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'destroy']);
    Route::resource('branches', BranchController::class)->except(['destroy']);
    Route::resource('users', UserController::class)->except(['destroy']);
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
