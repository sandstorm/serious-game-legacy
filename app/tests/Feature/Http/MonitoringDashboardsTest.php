<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);
beforeEach(function () {
    /** @var TestCase $this */
    // the container runs tests with APP_ENV=local, where Horizon allows everybody by default
    $this->app['env'] = 'production';
});

function createAdminUser(bool $superadmin): User
{
    /** @var User $user */
    $user = User::create([
        'name' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role_superadmin' => $superadmin,
        'role_lehrperson' => false,
    ]);
    return $user;
}

describe('pulse dashboard', function () {
    it('renders for superadmins', function () {
        /** @var TestCase $this */
        $this->actingAs(createAdminUser(superadmin: true));

        $this->get('/pulse')->assertOk();
    });
});

describe('horizon dashboard', function () {
    it('is accessible for superadmins', function () {
        /** @var TestCase $this */
        $this->actingAs(createAdminUser(superadmin: true));

        $this->get('/horizon')->assertOk();
    });

    it('is forbidden for other admin users', function () {
        /** @var TestCase $this */
        $this->withExceptionHandling();
        $this->actingAs(createAdminUser(superadmin: false));

        $this->get('/horizon')->assertForbidden();
    });
});
