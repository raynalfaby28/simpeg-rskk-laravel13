<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $nip): User
    {
        return User::create([
            'nip' => $nip,
            'name' => ucfirst(str_replace('_', ' ', $role)).' '.$nip,
            'role' => $role,
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
    }

    private function makeEmployee(User $user): Employee
    {
        return Employee::create([
            'user_id' => $user->id,
            'nip' => $user->nip,
            'nama_lengkap' => $user->name,
        ]);
    }

    private function pendingChange(Employee $employee, array $newData = ['hp' => '08123456789']): ChangeRequest
    {
        return ChangeRequest::create([
            'employee_id' => $employee->id,
            'module_type' => 'profil',
            'old_data' => ['hp' => null],
            'new_data' => $newData,
            'status' => 'pending',
        ]);
    }

    public function test_admin_cannot_create_employee_with_super_admin_role(): void
    {
        $admin = $this->makeUser('admin', '9001');
        $this->makeEmployee($admin);

        $this->actingAs($admin)->post(route('employees.store'), [
            'nama_lengkap' => 'Coba SA',
            'nip' => '7777',
            'role' => 'super_admin',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['nip' => '7777']);
        $this->assertDatabaseMissing('employees', ['nip' => '7777']);
    }

    public function test_admin_cannot_access_or_use_account_management(): void
    {
        $admin = $this->makeUser('admin', '9002');
        $this->makeEmployee($admin);

        $this->actingAs($admin)->get(route('admin.accounts.index'))->assertStatus(403);

        $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'nip' => '7778',
            'name' => 'Naik Pangkat',
            'role' => 'super_admin',
            'password' => 'password123',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['nip' => '7778']);
    }

    public function test_admin_cannot_edit_super_admin_employee(): void
    {
        $admin = $this->makeUser('admin', '9003');
        $this->makeEmployee($admin);

        $sa = $this->makeUser('super_admin', '9004');
        $saEmp = $this->makeEmployee($sa);

        $this->actingAs($admin)->get(route('employees.edit', $saEmp))->assertStatus(403);

        $this->actingAs($admin)->put(route('employees.update', $saEmp), [
            'nama_lengkap' => 'Diubah Admin',
        ])->assertStatus(403);

        $this->assertDatabaseHas('employees', [
            'id' => $saEmp->id,
            'nama_lengkap' => $sa->name,
        ]);
    }

    public function test_admin_can_approve_other_user_change_request(): void
    {
        $admin = $this->makeUser('admin', '9005');
        $this->makeEmployee($admin);

        $user = $this->makeUser('user', '9006');
        $emp = $this->makeEmployee($user);
        $cr = $this->pendingChange($emp);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $cr))
            ->assertRedirect(route('approvals.index'));

        $this->assertDatabaseHas('change_requests', [
            'id' => $cr->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('employees', ['id' => $emp->id, 'hp' => '08123456789']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'approve', 'user_id' => $admin->id]);
    }

    public function test_admin_cannot_approve_own_change_request(): void
    {
        $admin = $this->makeUser('admin', '9007');
        $emp = $this->makeEmployee($admin);
        $cr = $this->pendingChange($emp);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $cr))
            ->assertStatus(403);

        $this->assertDatabaseHas('change_requests', ['id' => $cr->id, 'status' => 'pending']);
    }

    public function test_admin_cannot_approve_super_admin_change_request(): void
    {
        $admin = $this->makeUser('admin', '9008');
        $this->makeEmployee($admin);

        $sa = $this->makeUser('super_admin', '9009');
        $emp = $this->makeEmployee($sa);
        $cr = $this->pendingChange($emp);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $cr))
            ->assertStatus(403);

        $this->assertDatabaseHas('change_requests', ['id' => $cr->id, 'status' => 'pending']);
    }

    public function test_super_admin_can_still_manage_roles(): void
    {
        $sa = $this->makeUser('super_admin', '9010');
        $this->makeEmployee($sa);

        $target = $this->makeUser('user', '9011');
        $this->makeEmployee($target);

        $this->actingAs($sa)
            ->patch(route('admin.accounts.update-role', $target), ['role' => 'admin'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin']);
    }

    public function test_super_admin_cannot_demote_super_admin_via_account_management(): void
    {
        $sa = $this->makeUser('super_admin', '9012');
        $this->makeEmployee($sa);

        $other = $this->makeUser('super_admin', '9013');
        $this->makeEmployee($other);

        $this->actingAs($sa)
            ->patch(route('admin.accounts.update-role', $other), ['role' => 'user'])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', ['id' => $other->id, 'role' => 'super_admin']);
    }
}
