<?php
declare(strict_types=1);

namespace App\Services\Shared;

/** Configuration des modules installés. Les ACL utilisateur restent obligatoires. */
final class ModuleRegistry
{
    /** @var array<string,bool> */
    private array $modules;

    /** @param array<string,bool>|null $modules */
    public function __construct(?array $modules = null)
    {
        $this->modules = $modules ?? require dirname(__DIR__, 3) . '/config/modules.php';
    }

    public function isEnabled(string $slug): bool
    {
        // Conserver le noyau d'administration, protégé séparément par les ACL.
        if ($slug === 'admin') {
            return true;
        }
        return ($this->modules[$slug] ?? false) === true;
    }

    /** @return array<string,bool> */
    public function all(): array
    {
        return $this->modules;
    }
}
