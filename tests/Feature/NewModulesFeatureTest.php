<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookCirculation;
use App\Models\BookType;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Library;
use App\Models\LibraryMember;
use App\Models\Period;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewModulesFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
    }

    private function makeUser(string $roleSlug, ?Branch $branch = null): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => str($roleSlug)->replace('-', ' ')->title()->toString(),
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'branch_id' => $branch?->id,
        ]);
    }

    private function makeBranch(string $name = 'Central Branch', string $code = 'CEN'): Branch
    {
        return Branch::create([
            'name' => $name . '_' . uniqid(),
            'code' => $code . '_' . uniqid(),
            'status' => 'active',
        ]);
    }

    public function test_teacher_crud_and_assignment(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('school-administrator', $branch);
        $teacherUser = User::factory()->create(['branch_id' => $branch->id]);

        // Create Teacher
        $response = $this->actingAs($admin)->post(route('teachers.store'), [
            'branch_id' => $branch->id,
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH-' . uniqid(),
            'phone' => '1234567890',
            'qualification' => 'M.Sc. Mathematics',
            'specialization' => 'Algebra',
            'joining_date' => '2026-09-01',
            'status' => 'active',
        ]);
        $response->assertRedirect(route('teachers.index'));

        $teacher = Teacher::where('user_id', $teacherUser->id)->firstOrFail();

        // Grade and Section and Subject
        $grade = Grade::create(['branch_id' => $branch->id, 'name' => 'Grade 10', 'code' => 'G10_' . uniqid(), 'level' => 10, 'status' => 'active']);
        $section = Section::create(['grade_id' => $grade->id, 'name' => 'Section A', 'capacity' => 30, 'status' => 'active']);
        $subject = Subject::create(['branch_id' => $branch->id, 'name' => 'Mathematics', 'code' => 'MATH_' . uniqid(), 'status' => 'active']);

        // Create Assignment
        $assignResponse = $this->actingAs($admin)->post(route('teacher-assignments.store'), [
            'teacher_id' => $teacher->id,
            'grade_id' => $grade->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'role' => 'subject_teacher',
            'notes' => 'Head of math for Grade 10',
        ]);
        $assignResponse->assertRedirect(route('teacher-assignments.index'));
        $this->assertDatabaseHas('teacher_assignments', [
            'teacher_id' => $teacher->id,
            'grade_id' => $grade->id,
            'section_id' => $section->id,
        ]);
    }

    public function test_period_and_school_hour_management(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('school-administrator', $branch);

        // Period
        $periodResponse = $this->actingAs($admin)->post(route('periods.store'), [
            'branch_id' => $branch->id,
            'period_number' => 1,
            'name' => 'Morning Period 1',
            'start_time' => '08:00',
            'end_time' => '08:45',
            'is_break' => 0,
            'status' => 'active',
        ]);
        $periodResponse->assertRedirect(route('periods.index'));
        $this->assertDatabaseHas('periods', ['period_number' => 1, 'branch_id' => $branch->id]);

        // School Hour
        $hourResponse = $this->actingAs($admin)->post(route('school-hours.store'), [
            'branch_id' => $branch->id,
            'shift_name' => 'Main Regular Shift',
            'opening_time' => '07:30',
            'closing_time' => '15:30',
            'operating_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'is_active' => 1,
        ]);
        $hourResponse->assertRedirect(route('school-hours.index'));
        $this->assertDatabaseHas('school_hours', ['shift_name' => 'Main Regular Shift']);
    }

    public function test_classroom_and_timetable_with_conflict_detection(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('school-administrator', $branch);

        // Classroom
        $room = Classroom::create([
            'branch_id' => $branch->id,
            'name' => 'Science Lab 1',
            'room_number' => 'LAB-' . uniqid(),
            'capacity' => 25,
            'type' => 'science_lab',
            'status' => 'active',
        ]);

        $teacherUser = User::factory()->create(['branch_id' => $branch->id]);
        $teacher = Teacher::create([
            'branch_id' => $branch->id,
            'user_id' => $teacherUser->id,
            'employee_id' => 'TCH-' . uniqid(),
            'status' => 'active',
        ]);
        $grade = Grade::create(['branch_id' => $branch->id, 'name' => 'Grade 9', 'code' => 'G9_' . uniqid(), 'level' => 9, 'status' => 'active']);
        $section1 = Section::create(['grade_id' => $grade->id, 'name' => 'Section A', 'capacity' => 25, 'status' => 'active']);
        $section2 = Section::create(['grade_id' => $grade->id, 'name' => 'Section B', 'capacity' => 25, 'status' => 'active']);
        $subject = Subject::create(['branch_id' => $branch->id, 'name' => 'Physics', 'code' => 'PHY_' . uniqid(), 'status' => 'active']);
        $period = Period::create([
            'branch_id' => $branch->id,
            'period_number' => 1,
            'name' => 'Period 1',
            'start_time' => '08:00',
            'end_time' => '08:45',
            'status' => 'active',
        ]);

        // First timetable slot succeeds
        $timetableResponse = $this->actingAs($admin)->post(route('timetables.store'), [
            'branch_id' => $branch->id,
            'section_id' => $section1->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'classroom_id' => $room->id,
            'period_id' => $period->id,
            'day_of_week' => 'Monday',
        ]);
        $timetableResponse->assertRedirect(route('timetables.index'));

        // Second slot with teacher conflict should fail with validation error
        $conflictResponse = $this->actingAs($admin)->post(route('timetables.store'), [
            'branch_id' => $branch->id,
            'section_id' => $section2->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id, // Same teacher
            'classroom_id' => null,
            'period_id' => $period->id,
            'day_of_week' => 'Monday',
        ]);
        $conflictResponse->assertSessionHasErrors(['teacher_id']);
    }

    public function test_student_and_staff_attendance(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('school-administrator', $branch);

        $grade = Grade::create(['branch_id' => $branch->id, 'name' => 'Grade 8', 'code' => 'G8_' . uniqid(), 'level' => 8, 'status' => 'active']);
        $section = Section::create(['grade_id' => $grade->id, 'name' => 'Section A', 'capacity' => 30, 'status' => 'active']);
        $student = Student::factory()->create(['branch_id' => $branch->id]);

        // Student Attendance bulk save
        $studentAttendanceResponse = $this->actingAs($admin)->post(route('student-attendance.store'), [
            'section_id' => $section->id,
            'attendance_date' => '2026-10-14',
            'attendances' => [
                [
                    'student_id' => $student->id,
                    'status' => 'present',
                    'remarks' => 'On time',
                ],
            ],
        ]);
        $studentAttendanceResponse->assertRedirect();
        $this->assertDatabaseHas('student_attendances', [
            'student_id' => $student->id,
            'section_id' => $section->id,
            'status' => 'present',
        ]);

        // Staff Attendance
        $staffUser = User::factory()->create(['branch_id' => $branch->id]);
        $staffAttendanceResponse = $this->actingAs($admin)->post(route('staff-attendance.store'), [
            'branch_id' => $branch->id,
            'attendance_date' => '2026-10-14',
            'attendances' => [
                [
                    'user_id' => $staffUser->id,
                    'status' => 'present',
                    'check_in_time' => '07:55',
                    'check_out_time' => '16:05',
                ],
            ],
        ]);
        $staffAttendanceResponse->assertRedirect();
        $this->assertDatabaseHas('staff_attendances', [
            'user_id' => $staffUser->id,
            'branch_id' => $branch->id,
            'status' => 'present',
        ]);
    }

    public function test_library_system_with_book_circulation_and_return(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('school-administrator', $branch);

        // Library & BookType
        $library = Library::create([
            'branch_id' => $branch->id,
            'name' => 'Central Library ' . uniqid(),
            'status' => 'active',
        ]);
        $bookType = BookType::create([
            'name' => 'Literature ' . uniqid(),
            'code' => 'LIT_' . uniqid(),
        ]);

        // Book
        $book = Book::create([
            'library_id' => $library->id,
            'book_type_id' => $bookType->id,
            'title' => 'The Great Gatsby ' . uniqid(),
            'author' => 'F. Scott Fitzgerald',
            'isbn' => '978-' . rand(100000000, 999999999),
            'total_copies' => 3,
            'available_copies' => 3,
            'status' => 'available',
        ]);

        // Member
        $student = Student::factory()->create(['branch_id' => $branch->id]);
        $member = LibraryMember::create([
            'branch_id' => $branch->id,
            'member_number' => 'LIB-' . uniqid(),
            'member_type' => 'student',
            'student_id' => $student->id,
            'max_borrow_limit' => 3,
            'status' => 'active',
        ]);

        // Issue book
        $issueResponse = $this->actingAs($admin)->post(route('circulations.store'), [
            'book_id' => $book->id,
            'library_member_id' => $member->id,
            'issue_date' => '2026-10-14',
            'due_date' => '2026-10-28',
        ]);
        $issueResponse->assertRedirect(route('circulations.index'));

        $book->refresh();
        $this->assertEquals(2, $book->available_copies);
        $this->assertDatabaseHas('book_circulations', [
            'book_id' => $book->id,
            'library_member_id' => $member->id,
            'status' => 'issued',
        ]);

        $circulation = BookCirculation::where('book_id', $book->id)->firstOrFail();

        // Return book
        $returnResponse = $this->actingAs($admin)->post(route('circulations.return', $circulation), [
            'return_date' => '2026-10-20',
            'fine_amount' => 0,
        ]);
        $returnResponse->assertRedirect(route('circulations.index'));

        $book->refresh();
        $circulation->refresh();
        $this->assertEquals(3, $book->available_copies);
        $this->assertEquals('returned', $circulation->status);
    }

    public function test_roster_views_render(): void
    {
        $branch = $this->makeBranch();
        $admin = $this->makeUser('super-administrator', $branch);

        $this->actingAs($admin)->get(route('roster.students'))->assertOk();
        $this->actingAs($admin)->get(route('roster.teachers'))->assertOk();
        $this->actingAs($admin)->get(route('roster.timetable'))->assertOk();
        $this->actingAs($admin)->get(route('roster.grades'))->assertOk();
    }
}
