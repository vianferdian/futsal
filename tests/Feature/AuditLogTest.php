<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $supervisorUser;
    protected User $teamAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN,
            'status' => 'active',
        ]);

        $this->supervisorUser = User::create([
            'name' => 'Supervisor User',
            'email' => 'spv@test.com',
            'username' => 'spv_test',
            'password' => bcrypt('password'),
            'role' => UserRole::SUPERVISOR,
            'status' => 'active',
        ]);

        $this->teamAdminUser = User::create([
            'name' => 'Team Admin User',
            'email' => 'ta@test.com',
            'username' => 'ta_test',
            'password' => bcrypt('password'),
            'role' => UserRole::TEAM_ADMIN,
            'status' => 'active',
        ]);

        // Create sample audit logs
        AuditLog::create([
            'user_id' => $this->supervisorUser->id,
            'action' => 'Mengubah lineup tim',
            'subject_type' => 'MatchLineup',
            'subject_id' => 1,
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'action' => 'Membuat jadwal baru',
            'subject_type' => 'FutsalMatch',
            'subject_id' => 2,
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_admin_can_access_audit_logs_list(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Audit Log Sistem');
        $response->assertSee('Mengubah lineup tim');
        $response->assertSee('Membuat jadwal baru');
    }

    public function test_supervisor_cannot_access_audit_logs_list(): void
    {
        $response = $this->actingAs($this->supervisorUser)
            ->get(route('admin.audit-logs.index'));

        $response->assertStatus(403);
    }

    public function test_team_admin_cannot_access_audit_logs_list(): void
    {
        $response = $this->actingAs($this->teamAdminUser)
            ->get(route('admin.audit-logs.index'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_audit_logs_list(): void
    {
        $response = $this->get(route('admin.audit-logs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_can_filter_audit_logs_by_search_text(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit-logs.index', ['search' => 'lineup']));

        $response->assertOk();
        $response->assertSee('Mengubah lineup tim');
        $response->assertDontSee('Membuat jadwal baru');
    }

    public function test_can_filter_audit_logs_by_user_id(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit-logs.index', ['user_id' => $this->adminUser->id]));

        $response->assertOk();
        $response->assertSee('Membuat jadwal baru');
        $response->assertDontSee('Mengubah lineup tim');
    }
}
