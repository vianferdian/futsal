<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
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

        // Seed initial setting
        Setting::setByKey('system_name', 'Liga Futsal Asli');
        Setting::setByKey('default_half_duration', '20');
        Setting::setByKey('max_fouls_before_penalty', '5');
        Setting::setByKey('max_timeout_per_period', '1');
        Setting::setByKey('pdf_footer', 'Futsal Indonesia');
        Setting::setByKey('timezone', 'Asia/Jakarta');
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Liga Futsal Asli');
    }

    public function test_supervisor_cannot_view_settings_page(): void
    {
        $response = $this->actingAs($this->supervisorUser)
            ->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    public function test_team_admin_cannot_view_settings_page(): void
    {
        $response = $this->actingAs($this->teamAdminUser)
            ->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.update'), [
                'system_name' => 'Sistem Baru Futsal',
                'default_half_duration' => '15',
                'max_fouls_before_penalty' => '6',
                'max_timeout_per_period' => '2',
                'pdf_footer' => 'Footer Baru PDF',
                'timezone' => 'Asia/Jayapura',
                'copyright' => 'Copyright 2026 Baru @ AFP',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Sistem Baru Futsal', Setting::getByKey('system_name'));
        $this->assertEquals('15', Setting::getByKey('default_half_duration'));
        $this->assertEquals('6', Setting::getByKey('max_fouls_before_penalty'));
        $this->assertEquals('2', Setting::getByKey('max_timeout_per_period'));
        $this->assertEquals('Copyright 2026 Baru @ AFP', Setting::getByKey('copyright'));

        // Assert audit log registers
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->adminUser->id,
            'action' => 'Mengubah pengaturan sistem',
        ]);
    }

    public function test_settings_validation_rules(): void
    {
        // default_half_duration below min (5)
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.settings.update'), [
                'system_name' => 'Sistem Baru Futsal',
                'default_half_duration' => '3',
                'max_fouls_before_penalty' => '6',
                'max_timeout_per_period' => '2',
                'pdf_footer' => 'Footer Baru PDF',
                'timezone' => 'Asia/Jayapura',
                'copyright' => 'Copyright 2026 Baru @ AFP',
            ]);

        $response->assertSessionHasErrors('default_half_duration');
    }
}
