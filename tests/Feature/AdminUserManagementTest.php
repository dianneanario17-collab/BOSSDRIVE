<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_change_accounts_to_staff_role(): void
    {
        $staff = User::factory()->create(['role' => 'user']);

        $this->withSession(['is_admin' => true])
            ->postJson(route('admin.users.store'), [
                'name' => 'New Staff',
                'email' => 'new.staff@example.com',
                'role' => 'staff',
                'password' => 'SecurePass1!',
                'password_confirmation' => 'SecurePass1!',
            ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'staff');

        $this->withSession(['is_admin' => true])
            ->patchJson(route('admin.users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => 'staff',
            ])
            ->assertOk()
            ->assertJsonPath('user.role', 'staff');

        $this->withSession(['is_admin' => true])
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('<option value="staff">Staff</option>', false)
            ->assertSee("'Staff'", false);
    }

    public function test_staff_account_can_sign_in_through_staff_login(): void
    {
        $password = 'SecurePass1!';
        $staff = User::factory()->create([
            'role' => 'staff',
            'password' => Hash::make($password),
        ]);

        $this->post(route('staff.login.submit'), [
            'email' => $staff->email,
            'password' => $password,
        ])
            ->assertRedirect(route('staff.reservations'))
            ->assertSessionHas('is_staff', true)
            ->assertSessionHas('staff_user_id', $staff->id)
            ->assertSessionHas('staff_name', $staff->name);
    }
}
