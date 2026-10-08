<?php

namespace Tests\Unit;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private function userAt(string $usertype, ?User $manager = null): User
    {
        return User::factory()->create([
            'usertype' => $usertype,
            'manager_id' => $manager?->id,
            'email' => fake()->unique()->userName() . '@multiculturalcls.org',
        ]);
    }

    public function test_admin_can_manage_lower_roles_but_not_another_admin(): void
    {
        $admin = $this->userAt('admin');

        $this->assertTrue($admin->canManage($this->userAt('siteadmin')));
        $this->assertTrue($admin->canManage($this->userAt('employee')));
        $this->assertFalse($admin->canManage($this->userAt('admin')));
    }

    public function test_manager_can_only_manage_their_own_supervisors(): void
    {
        $manager = $this->userAt('manager');
        $mine = $this->userAt('supervisor', $manager);
        $theirs = $this->userAt('supervisor', $this->userAt('manager'));

        $this->assertTrue($manager->canManage($mine));
        $this->assertFalse($manager->canManage($theirs));
    }

    /** BUG-9: usertype is nullable, so an unknown role must deny rather than throw. */
    public function test_null_usertype_is_denied_rather_than_fatal(): void
    {
        $legacy = User::factory()->create([
            'usertype' => null,
            'email' => fake()->unique()->userName() . '@multiculturalcls.org',
        ]);

        $this->assertFalse($legacy->canManage($this->userAt('employee')));
        $this->assertTrue($this->userAt('admin')->canManage($legacy));
    }

    public function test_employee_accessible_sites_is_limited_to_one(): void
    {
        $employee = $this->userAt('employee');
        $employee->sites()->attach(Site::factory()->create()->id);

        $this->assertCount(1, $employee->getAccessibleSites());
    }

    public function test_admin_accessible_sites_covers_everything(): void
    {
        Site::factory()->count(3)->create();

        $this->assertCount(3, $this->userAt('admin')->getAccessibleSites());
    }

    public function test_manager_accessible_sites_come_from_their_supervisors(): void
    {
        $manager = $this->userAt('manager');
        $supervisor = $this->userAt('supervisor', $manager);
        $site = Site::factory()->create();
        $supervisor->sites()->attach($site->id);
        Site::factory()->create(); // unrelated site

        $accessible = $manager->getAccessibleSites();

        $this->assertCount(1, $accessible);
        $this->assertSame($site->id, $accessible->first()->id);
    }
}
