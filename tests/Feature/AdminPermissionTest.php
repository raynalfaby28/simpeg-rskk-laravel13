<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkUnit;
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

    public function test_profil_request_captures_editable_fields_and_strips_locked_kepegawaian(): void
    {
        $user = $this->makeUser('user', '9014');
        $emp = $this->makeEmployee($user);
        $emp->update(['status_pegawai' => 'PNS']);

        $this->actingAs($user)->put(route('profile.update'), [
            'nama_lengkap' => $emp->nama_lengkap,
            'no_npwp' => '00.111.222.3-444.000',
            'no_kk' => '3200123456789',
            'tahun_pendidikan_akhir' => '2020',
            'status_pegawai' => 'Outsourcing',
            'gaji_pokok' => '99999999',
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'profil')
            ->latest('id')
            ->first();

        $this->assertNotNull($cr);
        $nd = $cr->new_data;
        $this->assertEquals('00.111.222.3-444.000', $nd['no_npwp']);
        $this->assertEquals('3200123456789', $nd['no_kk']);
        $this->assertEquals('2020', $nd['tahun_pendidikan_akhir']);
        $this->assertArrayNotHasKey('status_pegawai', $nd);
        $this->assertArrayNotHasKey('gaji_pokok', $nd);
        $this->assertArrayNotHasKey('employment_status_id', $nd);
    }

    public function test_super_admin_profil_request_also_strips_locked_kepegawaian(): void
    {
        $sa = $this->makeUser('super_admin', '9015');
        $emp = $this->makeEmployee($sa);
        $emp->update(['status_pegawai' => 'PNS']);

        $this->actingAs($sa)->put(route('profile.update'), [
            'nama_lengkap' => $emp->nama_lengkap,
            'email_pribadi' => 'sa@rs.example',
            'status_pegawai' => 'Kontrak',
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'profil')
            ->latest('id')
            ->first();

        $this->assertNotNull($cr);
        $nd = $cr->new_data;
        $this->assertEquals('sa@rs.example', $nd['email_pribadi']);
        $this->assertArrayNotHasKey('status_pegawai', $nd);
    }

    public function test_profil_request_allows_initial_jabatan_fill(): void
    {
        $pos = Position::create(['name' => 'Perawat Ahli']);
        $wu = WorkUnit::create(['name' => 'Instalasi Rawat Inap']);

        $user = $this->makeUser('user', '9016');
        $emp = $this->makeEmployee($user);

        $this->actingAs($user)->put(route('profile.update'), [
            'nama_lengkap' => $emp->nama_lengkap,
            'current_position_id' => $pos->id,
            'work_unit_id' => $wu->id,
            'eselon' => 'IV.a',
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'profil')
            ->latest('id')
            ->first();

        $this->assertNotNull($cr);
        $nd = $cr->new_data;
        $this->assertEquals($pos->id, $nd['current_position_id']);
        $this->assertEquals($wu->id, $nd['work_unit_id']);
        $this->assertEquals('IV.a', $nd['eselon']);
    }

    public function test_profil_request_locks_jabatan_after_recorded(): void
    {
        $pos = Position::create(['name' => 'Perawat Ahli']);
        $wu = WorkUnit::create(['name' => 'Instalasi Rawat Inap']);
        $pos2 = Position::create(['name' => 'Jabatan Baru']);

        $user = $this->makeUser('user', '9017');
        $emp = $this->makeEmployee($user);
        $emp->update(['current_position_id' => $pos->id, 'work_unit_id' => $wu->id]);

        $this->actingAs($user)->put(route('profile.update'), [
            'nama_lengkap' => $emp->nama_lengkap,
            'current_position_id' => $pos2->id,
            'work_unit_id' => $wu->id,
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'profil')
            ->latest('id')
            ->first();

        $this->assertNotNull($cr);
        $nd = $cr->new_data;
        $this->assertArrayNotHasKey('current_position_id', $nd);
        $this->assertArrayNotHasKey('work_unit_id', $nd);
    }

    public function test_user_mutation_submit_creates_pending_change_request(): void
    {
        $pos = Position::create(['name' => 'Perawat Ahli']);
        $wu = WorkUnit::create(['name' => 'Instalasi Rawat Inap']);

        $user = $this->makeUser('user', '9020');
        $emp = $this->makeEmployee($user);

        $this->actingAs($user)->post(route('sub.store', [$emp, 'mutasi']), [
            'jenis_mutasi' => 'Promosi',
            'unit_tujuan_id' => $wu->id,
            'jabatan_baru' => 'Perawat Ahli',
            'tanggal_mutasi' => '2026-02-01',
            'no_sk' => 'SK-MUT-001',
        ])->assertRedirect();

        $this->assertDatabaseMissing('employee_mutations', ['employee_id' => $emp->id]);

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'mutasi')
            ->latest('id')
            ->first();

        $this->assertNotNull($cr);
        $this->assertEquals('pending', $cr->status);
        $nd = $cr->new_data;
        $this->assertEquals('create', $nd['_mutation_operation']);
        $this->assertEquals('SK-MUT-001', $nd['no_sk']);
    }

    public function test_approved_mutation_request_records_row_and_syncs_position(): void
    {
        $pos = Position::create(['name' => 'Perawat Ahli']);
        $wu = WorkUnit::create(['name' => 'Instalasi Rawat Inap']);
        $sa = $this->makeUser('super_admin', '9021');
        $this->makeEmployee($sa);

        $user = $this->makeUser('user', '9022');
        $emp = $this->makeEmployee($user);

        $this->actingAs($user)->post(route('sub.store', [$emp, 'mutasi']), [
            'jenis_mutasi' => 'Alih Tugas',
            'unit_tujuan_id' => $wu->id,
            'jabatan_baru' => 'Perawat Ahli',
            'tanggal_mutasi' => '2026-03-01',
            'no_sk' => 'SK-MUT-002',
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'mutasi')
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($sa)
            ->post(route('approvals.approve', $cr))
            ->assertRedirect(route('approvals.index'));

        $this->assertDatabaseHas('employee_mutations', [
            'employee_id' => $emp->id,
            'jenis_mutasi' => 'Alih Tugas',
            'unit_tujuan_id' => $wu->id,
            'no_sk' => 'SK-MUT-002',
        ]);
        $this->assertDatabaseHas('employees', [
            'id' => $emp->id,
            'current_position_id' => $pos->id,
            'work_unit_id' => $wu->id,
        ]);
        $this->assertDatabaseHas('change_requests', ['id' => $cr->id, 'status' => 'approved']);
    }

    public function test_rejected_mutation_request_does_not_record_row(): void
    {
        $pos = Position::create(['name' => 'Jabatan Lama Lama']);
        $wu = WorkUnit::create(['name' => 'Instalasi Lab']);
        $sa = $this->makeUser('super_admin', '9023');
        $this->makeEmployee($sa);

        $user = $this->makeUser('user', '9024');
        $emp = $this->makeEmployee($user);

        $this->actingAs($user)->post(route('sub.store', [$emp, 'mutasi']), [
            'jenis_mutasi' => 'Mutasi Masuk',
            'unit_tujuan_id' => $wu->id,
            'jabatan_baru' => 'Jabatan Lama Lama',
            'tanggal_mutasi' => '2026-04-01',
        ])->assertRedirect();

        $cr = ChangeRequest::where('employee_id', $emp->id)
            ->where('module_type', 'mutasi')
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($sa)
            ->post(route('approvals.reject', $cr), ['rejection_reason' => 'Dokumen SK belum dilampirkan'])
            ->assertRedirect(route('approvals.index'));

        $this->assertDatabaseMissing('employee_mutations', ['employee_id' => $emp->id]);
        $this->assertDatabaseHas('change_requests', ['id' => $cr->id, 'status' => 'rejected']);
        $this->assertDatabaseMissing('employees', ['id' => $emp->id, 'current_position_id' => $pos->id]);
    }

    public function test_admin_mutation_still_records_directly_without_approval(): void
    {
        $pos = Position::create(['name' => 'Perawat Mahir']);
        $wu = WorkUnit::create(['name' => 'Instalasi Bedah']);

        $admin = $this->makeUser('admin', '9025');
        $this->makeEmployee($admin);

        $target = $this->makeUser('user', '9026');
        $emp = $this->makeEmployee($target);

        $this->actingAs($admin)->post(route('sub.store', [$emp, 'mutasi']), [
            'jenis_mutasi' => 'Promosi',
            'unit_tujuan_id' => $wu->id,
            'jabatan_baru' => 'Perawat Mahir',
            'tanggal_mutasi' => '2026-05-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('employee_mutations', ['employee_id' => $emp->id, 'jenis_mutasi' => 'Promosi']);
        $this->assertDatabaseMissing('change_requests', ['employee_id' => $emp->id, 'module_type' => 'mutasi']);
        $this->assertDatabaseHas('employees', ['id' => $emp->id, 'current_position_id' => $pos->id]);
    }
}
