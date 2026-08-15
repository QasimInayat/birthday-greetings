<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Loads every admin page and asserts it renders. Read-only: GET requests only.
 */
class PageSmokeTest extends TestCase
{
    public static function pageProvider(): array
    {
        return [
            'dashboard'         => ['dashboard.index'],
            'employees'         => ['employees.index'],
            'employee create'   => ['employees.create'],
            'bulk import'       => ['employees.bulk'],
            'bulk sample csv'   => ['employees.bulkSample'],
            'upcoming'          => ['employees.upcoming-birthdays'],
            'email templates'   => ['email-templates.index'],
            'email tpl create'  => ['email-templates.create'],
            'sms templates'     => ['sms-templates.index'],
            'sms tpl create'    => ['sms-templates.create'],
            'smtp config'       => ['email-config.index'],
            'email settings'    => ['email-settings.index'],
            'gateway config'    => ['sms-config.index'],
            'sms settings'      => ['sms-settings.index'],
            'reports'           => ['reports.summary'],
            'logs'              => ['logs.index'],
            'automation'        => ['cron-settings.index'],
        ];
    }

    /**
     * @dataProvider pageProvider
     */
    public function test_page_renders(string $routeName): void
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user in the database to authenticate as.');
        }

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertStatus(200);
    }
}
