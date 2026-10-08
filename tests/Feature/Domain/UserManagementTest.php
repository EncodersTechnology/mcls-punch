<?php

namespace Tests\Feature\Domain;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BUG-8: the access-management screen offered directors, managers and supervisors the
 * ability to create and edit their own people, but the routes sat behind admin-only
 * middleware, so every submit bounced silently.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userAt(string $usertype, ?Site $site = null, ?User $manager = null): User
    {
        $user = User::factory()->create([
            'usertype' => $usertype,
            'manager_id' => $manager?->id,
            'email' => fake()->unique()->userName() . '@multiculturalcls.org',
        ]);

        if ($site) {
            $user->sites()->attach($site->id);
        }

        return $user;
    }

    public function test_supervisor_can_create_a_site_user_at_their_own_site(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('supervisor', $site))
            ->post('/admin/add/user', [
                'name' => 'New Site User',
                'email' => 'new.site.user@multiculturalcls.org',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'usertype' => 'employee',
                'site_id' => $site->id,
            ])
            ->assertRedirect(route('site.access.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'new.site.user@multiculturalcls.org',
            'usertype' => 'employee',
        ]);
    }

    public function test_supervisor_cannot_create_a_site_user_at_a_site_they_do_not_manage(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        $this->actingAs($this->userAt('supervisor', $mySite))
            ->post('/admin/add/user', [
                'name' => 'Elsewhere User',
                'email' => 'elsewhere@multiculturalcls.org',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'usertype' => 'employee',
                'site_id' => $otherSite->id,
            ])
            ->assertSessionHasErrors('site_id');

        $this->assertDatabaseMissing('users', ['email' => 'elsewhere@multiculturalcls.org']);
    }

    public function test_supervisor_cannot_create_a_supervisor(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('supervisor', $site))
            ->post('/admin/add/user', [
                'name' => 'Sneaky Supervisor',
                'email' => 'sneaky@multiculturalcls.org',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'usertype' => 'supervisor',
                'site_ids' => [$site->id],
            ])
            ->assertSessionHasErrors('usertype');
    }

    public function test_manager_can_create_a_supervisor(): void
    {
        $site = Site::factory()->create();
        $director = $this->userAt('director');
        $manager = $this->userAt('manager', null, $director);
        // A manager's accessible sites come from their supervisors, so seed one.
        $existingSupervisor = $this->userAt('supervisor', $site, $manager);

        $this->actingAs($manager)
            ->post('/admin/add/user', [
                'name' => 'Second Supervisor',
                'email' => 'second.supervisor@multiculturalcls.org',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'usertype' => 'supervisor',
                'site_ids' => [$site->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'second.supervisor@multiculturalcls.org',
            'usertype' => 'supervisor',
            'manager_id' => $manager->id,
        ]);
    }

    public function test_employee_cannot_create_users(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('employee', $site))
            ->post('/admin/add/user', [
                'name' => 'Nope',
                'email' => 'nope@multiculturalcls.org',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'usertype' => 'employee',
                'site_id' => $site->id,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'nope@multiculturalcls.org']);
    }

    public function test_supervisor_can_delete_a_site_user_at_their_site(): void
    {
        $site = Site::factory()->create();
        $employee = $this->userAt('employee', $site);

        $this->actingAs($this->userAt('supervisor', $site))
            ->delete('/admin/user/' . $employee->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_supervisor_cannot_delete_another_supervisor(): void
    {
        $site = Site::factory()->create();
        $victim = $this->userAt('supervisor', $site);

        $this->actingAs($this->userAt('supervisor', $site))
            ->delete('/admin/user/' . $victim->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $victim->id]);
    }

    public function test_access_management_screen_loads_for_a_supervisor(): void
    {
        $site = Site::factory()->create();
        $this->userAt('employee', $site);

        $this->actingAs($this->userAt('supervisor', $site))
            ->get('/acess/management')
            ->assertOk();
    }

    public function test_supervisor_sites_endpoint_is_closed_to_supervisors(): void
    {
        $site = Site::factory()->create();
        $target = $this->userAt('supervisor', $site);

        $this->actingAs($this->userAt('supervisor', $site))
            ->getJson('/admin/user/supervisor-sites/' . $target->id)
            ->assertForbidden();
    }
}
