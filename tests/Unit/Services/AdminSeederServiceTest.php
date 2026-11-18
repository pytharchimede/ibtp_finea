<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\Admin\UserRepository;
use App\Services\Admin\AdminSeederService;
use RuntimeException;
use Tests\TestCase;

final class AdminSeederServiceTest extends TestCase
{
    /** @var array<string,string|false> */
    private array $previous = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['FINEA_BOOTSTRAP_ADMIN_EMAIL', 'FINEA_BOOTSTRAP_ADMIN_PASSWORD'] as $key) {
            $this->previous[$key] = getenv($key);
            putenv($key);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->previous as $key => $value) {
            putenv($value === false ? $key : $key . '=' . $value);
        }
        parent::tearDown();
    }

    public function test_no_default_admin_is_created_without_explicit_credentials(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->expects(self::never())->method('create');
        (new AdminSeederService($users))->seed();
    }

    public function test_weak_bootstrap_password_is_rejected(): void
    {
        putenv('FINEA_BOOTSTRAP_ADMIN_EMAIL=admin@example.test');
        putenv('FINEA_BOOTSTRAP_ADMIN_PASSWORD=admin');
        $users = $this->createMock(UserRepository::class);
        $users->expects(self::never())->method('create');
        $this->expectException(RuntimeException::class);
        (new AdminSeederService($users))->seed();
    }

    public function test_existing_admin_is_not_replaced(): void
    {
        putenv('FINEA_BOOTSTRAP_ADMIN_EMAIL=admin@example.test');
        putenv('FINEA_BOOTSTRAP_ADMIN_PASSWORD=This-is-a-test-password-2026!');
        $users = $this->createMock(UserRepository::class);
        $users->method('statistics')->willReturn(['administrators' => 1]);
        $users->expects(self::never())->method('create');
        (new AdminSeederService($users))->seed();
    }
}
