<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_administrator_can_view_school_settings(): void
    {
        $administrator = $this->makeUser('school-administrator');

        $this->actingAs($administrator)
            ->get(route('settings.school.edit'))
            ->assertOk()
            ->assertSee('School Management System')
            ->assertSee('SchoolHub');
    }

    public function test_authorized_administrator_can_save_school_settings_without_changing_the_singleton_id(): void
    {
        $administrator = $this->makeUser('super-administrator');

        $response = $this->actingAs($administrator)->put(route('settings.school.update'), [
            'school_name' => 'Green Valley Academy',
            'school_code' => 'GVA',
            'official_email' => 'office@greenvalley.example',
            'official_phone' => '+251 11 555 0100',
            'address' => 'Addis Ababa',
            'timezone' => 'Africa/Addis_Ababa',
            'id' => 9,
        ]);

        $response->assertRedirect(route('settings.school.edit'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('school_settings', [
            'id' => 1,
            'school_name' => 'Green Valley Academy',
            'school_code' => 'GVA',
            'timezone' => 'Africa/Addis_Ababa',
        ]);
        $this->assertDatabaseMissing('school_settings', ['id' => 9]);
        $this->assertDatabaseCount('school_settings', 1);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('at Green Valley Academy today');
    }

    public function test_invalid_contact_email_and_timezone_are_rejected(): void
    {
        $administrator = $this->makeUser('school-administrator');

        $response = $this->actingAs($administrator)->from(route('settings.school.edit'))->put(route('settings.school.update'), [
            'school_name' => 'Green Valley Academy',
            'official_email' => 'not-an-email',
            'timezone' => 'Mars/Olympus',
        ]);

        $response->assertRedirect(route('settings.school.edit'))
            ->assertSessionHasErrors(['official_email', 'timezone']);
        $this->assertDatabaseCount('school_settings', 0);
    }

    public function test_teacher_cannot_view_or_update_school_settings(): void
    {
        $teacher = $this->makeUser('teacher');

        $this->actingAs($teacher)
            ->get(route('settings.school.edit'))
            ->assertForbidden();

        $this->put(route('settings.school.update'), [
            'school_name' => 'Unauthorized Change',
            'timezone' => 'UTC',
        ])->assertForbidden();
        $this->assertDatabaseCount('school_settings', 0);
    }

    public function test_branch_administrator_cannot_manage_global_school_settings(): void
    {
        $branchAdministrator = $this->makeUser('branch-administrator');

        $this->actingAs($branchAdministrator)
            ->get(route('settings.school.edit'))
            ->assertForbidden();
    }

    private function makeUser(string $roleSlug): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], [
            'name' => str($roleSlug)->replace('-', ' ')->title()->toString(),
        ]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
