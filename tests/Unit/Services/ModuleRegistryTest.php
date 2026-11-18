<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Shared\ModuleRegistry;
use Tests\TestCase;

final class ModuleRegistryTest extends TestCase
{
    public function test_disabled_or_missing_modules_are_not_available(): void
    {
        $registry = new ModuleRegistry(['finance' => true, 'site-admin' => false]);
        self::assertTrue($registry->isEnabled('finance'));
        self::assertFalse($registry->isEnabled('site-admin'));
        self::assertFalse($registry->isEnabled('transit-douane'));
        self::assertTrue($registry->isEnabled('admin'));
    }

    public function test_ibtp_profile_disables_legacy_public_website(): void
    {
        $profile = require BASE_PATH . '/config/module-profiles/ibtp.php';
        $registry = new ModuleRegistry($profile);
        self::assertFalse($registry->isEnabled('site-admin'));
        self::assertTrue($registry->isEnabled('finance'));
        self::assertTrue($registry->isEnabled('rh'));
    }
}
