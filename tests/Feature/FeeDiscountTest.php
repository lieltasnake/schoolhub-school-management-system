<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FeeDiscount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_fee_discounts(): void
    {
        $this->get(route('fee-discounts.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_view_fee_discounts_list(): void
    {
        $admin = $this->makeUser('super-administrator');
        $branch = $this->makeBranch('Central Campus', 'CENTRAL');

        $discount = FeeDiscount::create([
            'branch_id' => $branch->id,
            'name' => 'Sibling Discount',
            'code' => 'SIB',
            'type' => 'percentage',
            'value' => '15.00',
            'description' => '15% off for 2nd sibling',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('fee-discounts.index'))
            ->assertOk()
            ->assertSee('Sibling Discount')
            ->assertSee('15.00%');
    }

    public function test_authorized_accountant_can_create_fee_discount(): void
    {
        $branch = $this->makeBranch('North Campus', 'NORTH');
        $accountant = $this->makeUser('accountant-cashier', $branch);

        $response = $this->actingAs($accountant)->post(route('fee-discounts.store'), [
            'branch_id' => $branch->id,
            'name' => 'Merit Scholarship',
            'code' => 'MERIT',
            'type' => 'fixed',
            'value' => '250.00',
            'description' => '$250 scholarship for high achievers',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fee-discounts.index'));
        $this->assertDatabaseHas('fee_discounts', [
            'branch_id' => $branch->id,
            'name' => 'Merit Scholarship',
            'code' => 'MERIT',
            'type' => 'fixed',
            'value' => '250.00',
        ]);
    }

    public function test_percentage_discount_cannot_exceed_100_percent(): void
    {
        $branch = $this->makeBranch('East Campus', 'EAST');
        $admin = $this->makeUser('school-administrator', $branch);

        $response = $this->actingAs($admin)->post(route('fee-discounts.store'), [
            'branch_id' => $branch->id,
            'name' => 'Invalid Discount',
            'code' => 'INV',
            'type' => 'percentage',
            'value' => '150.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('value');
    }

    public function test_discount_name_and_code_must_be_unique_within_branch(): void
    {
        $branch = $this->makeBranch('South Campus', 'SOUTH');
        $admin = $this->makeUser('school-administrator', $branch);

        FeeDiscount::create([
            'branch_id' => $branch->id,
            'name' => 'Staff Child',
            'code' => 'STF',
            'type' => 'percentage',
            'value' => '50.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('fee-discounts.store'), [
            'branch_id' => $branch->id,
            'name' => 'Staff Child',
            'code' => 'STF2',
            'type' => 'percentage',
            'value' => '25.00',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_authorized_user_can_update_fee_discount(): void
    {
        $branch = $this->makeBranch('West Campus', 'WEST');
        $admin = $this->makeUser('school-administrator', $branch);

        $discount = FeeDiscount::create([
            'branch_id' => $branch->id,
            'name' => 'Early Bird',
            'code' => 'EARLY',
            'type' => 'fixed',
            'value' => '50.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put(route('fee-discounts.update', $discount), [
            'name' => 'Early Bird Special',
            'code' => 'EARLY-SPEC',
            'type' => 'fixed',
            'value' => '75.00',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('fee-discounts.index'));
        $this->assertDatabaseHas('fee_discounts', [
            'id' => $discount->id,
            'name' => 'Early Bird Special',
            'value' => '75.00',
        ]);
    }

    public function test_authorized_user_can_delete_fee_discount(): void
    {
        $branch = $this->makeBranch('Main Campus', 'MAIN');
        $admin = $this->makeUser('school-administrator', $branch);

        $discount = FeeDiscount::create([
            'branch_id' => $branch->id,
            'name' => 'Obsolete Discount',
            'code' => 'OBS',
            'type' => 'fixed',
            'value' => '10.00',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('fee-discounts.destroy', $discount));

        $response->assertRedirect(route('fee-discounts.index'));
        $this->assertDatabaseMissing('fee_discounts', [
            'id' => $discount->id,
        ]);
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
}
