<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_administrator_creates_an_academic_year(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $response = $this->actingAs($administrator)->post(route('academic-years.store'), [
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', [
            'branch_id' => $branch->id,
            'name' => '2026-2027',
            'status' => 'planned',
        ]);
    }

    public function test_calendar_list_and_form_pages_render_for_an_authorized_administrator(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $academicYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');
        $term = $academicYear->terms()->create([
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'status' => 'planned',
        ]);

        $this->actingAs($administrator)
            ->get(route('academic-years.index'))
            ->assertOk();

        $this->get(route('academic-years.create'))->assertOk();
        $this->get(route('academic-years.show', $academicYear))->assertOk();
        $this->get(route('academic-years.edit', $academicYear))->assertOk();
        $this->get(route('terms.index'))->assertOk();
        $this->get(route('terms.create'))->assertOk();
        $this->get(route('terms.show', $term))->assertOk();
        $this->get(route('terms.edit', $term))->assertOk();
    }

    public function test_academic_year_cannot_overlap_an_existing_year_at_the_same_branch(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');

        $response = $this->actingAs($administrator)->from(route('academic-years.create'))->post(route('academic-years.store'), [
            'branch_id' => $branch->id,
            'name' => 'Overlapping year',
            'start_date' => '2027-06-01',
            'end_date' => '2028-05-31',
        ]);

        $response->assertRedirect(route('academic-years.create'))
            ->assertSessionHasErrors('start_date');
        $this->assertDatabaseMissing('academic_years', ['name' => 'Overlapping year']);
    }

    public function test_activating_a_year_completes_the_previous_year_for_its_branch(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $previousYear = $this->createAcademicYear($branch, '2025-2026', '2025-09-01', '2026-06-30', 'active');
        $nextYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');

        $response = $this->actingAs($administrator)->post(route('academic-years.activate', $nextYear));

        $response->assertRedirect(route('academic-years.show', $nextYear));
        $this->assertDatabaseHas('academic_years', ['id' => $previousYear->id, 'status' => 'completed']);
        $this->assertDatabaseHas('academic_years', ['id' => $nextYear->id, 'status' => 'active']);
    }

    public function test_term_dates_must_fall_within_the_academic_year(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $academicYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');

        $response = $this->actingAs($administrator)->from(route('terms.create'))->post(route('terms.store'), [
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-20',
        ]);

        $response->assertRedirect(route('terms.create'))
            ->assertSessionHasErrors('start_date');
        $this->assertDatabaseMissing('terms', ['name' => 'Term 1']);
    }

    public function test_authorized_administrator_creates_a_term_inside_its_academic_year(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $academicYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');

        $response = $this->actingAs($administrator)->post(route('terms.store'), [
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('terms', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'status' => 'planned',
        ]);
    }

    public function test_activating_a_term_completes_the_previous_term_in_its_active_year(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $academicYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30', 'active');
        $previousTerm = $academicYear->terms()->create([
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'status' => 'active',
        ]);
        $nextTerm = $academicYear->terms()->create([
            'name' => 'Term 2',
            'start_date' => '2027-01-10',
            'end_date' => '2027-03-30',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($administrator)->post(route('terms.activate', $nextTerm));

        $response->assertRedirect(route('terms.show', $nextTerm));
        $this->assertDatabaseHas('terms', ['id' => $previousTerm->id, 'status' => 'completed']);
        $this->assertDatabaseHas('terms', ['id' => $nextTerm->id, 'status' => 'active']);
    }

    public function test_term_cannot_be_activated_before_its_academic_year(): void
    {
        $administrator = $this->makeUser('school-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');
        $academicYear = $this->createAcademicYear($branch, '2026-2027', '2026-09-01', '2027-06-30');
        $term = $academicYear->terms()->create([
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'status' => 'planned',
        ]);

        $response = $this->actingAs($administrator)->post(route('terms.activate', $term));

        $response->assertRedirect(route('terms.show', $term))
            ->assertSessionHas('error', 'Activate this term only after its academic year is active.');
        $this->assertDatabaseHas('terms', ['id' => $term->id, 'status' => 'planned']);
    }

    public function test_branch_administrator_cannot_view_another_branches_academic_year(): void
    {
        $assignedBranch = $this->makeBranch('Assigned Campus', 'ASSIGNED');
        $otherBranch = $this->makeBranch('Other Campus', 'OTHER');
        $administrator = $this->makeUser('branch-administrator', $assignedBranch);
        $academicYear = $this->createAcademicYear($otherBranch, '2026-2027', '2026-09-01', '2027-06-30');

        $this->actingAs($administrator)
            ->get(route('academic-years.show', $academicYear))
            ->assertForbidden();
    }

    public function test_branch_administrator_cannot_create_an_academic_year_for_another_branch(): void
    {
        $assignedBranch = $this->makeBranch('Assigned Campus', 'ASSIGNED');
        $otherBranch = $this->makeBranch('Other Campus', 'OTHER');
        $administrator = $this->makeUser('branch-administrator', $assignedBranch);

        $response = $this->actingAs($administrator)->post(route('academic-years.store'), [
            'branch_id' => $otherBranch->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('academic_years', ['branch_id' => $otherBranch->id]);
    }

    public function test_teacher_cannot_view_academic_calendar_management(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->actingAs($teacher)->get(route('academic-years.index'))->assertForbidden();
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

    private function makeBranch(string $name, string $code): Branch
    {
        return Branch::create([
            'name' => $name,
            'code' => $code,
            'status' => 'active',
        ]);
    }

    private function createAcademicYear(
        Branch $branch,
        string $name,
        string $startDate,
        string $endDate,
        string $status = 'planned',
    ): AcademicYear {
        return $branch->academicYears()->create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ]);
    }
}
