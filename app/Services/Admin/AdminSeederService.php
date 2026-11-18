<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Admin\UserRepository;

/**
 * Provisionnement initial uniquement. Ne recrée ni ne réactive un admin connu.
 * Les identifiants doivent être fournis explicitement hors du dépôt.
 */
final class AdminSeederService
{
    public function __construct(private UserRepository $users)
    {
    }

    public function seed(): void
    {
        $email = trim((string) (getenv('FINEA_BOOTSTRAP_ADMIN_EMAIL') ?: ''));
        $password = (string) (getenv('FINEA_BOOTSTRAP_ADMIN_PASSWORD') ?: '');

        if ($email === '' && $password === '') {
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($password) < 12
            || in_array(strtolower($password), ['admin', 'password', '123456789012'], true)) {
            throw new \RuntimeException('Configuration initiale administrateur invalide.');
        }

        // Un compte existant ne doit jamais être élevé automatiquement.
        if (($this->users->statistics()['administrators'] ?? 0) > 0
            || $this->users->findByEmail($email) !== null) {
            return;
        }

        $this->users->create(new User(
            id: null,
            fullName: 'Admin',
            email: $email,
            phone: null,
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            status: 'active',
            isAdmin: true,
        ));
    }
}
