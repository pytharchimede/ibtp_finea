<?php

namespace App\Security;

final class PermissionEntityRegistry
{
    public const BTP_PROJETS = 'btp_projets';
    public const BTP_DEVIS = 'btp_devis';
    public const BTP_CHANTIERS = 'btp_chantiers';
    public const BTP_SOUS_TRAITANCES = 'btp_sous_traitances';
    public const BTP_FINANCES = 'btp_finances';
    public const USERS = 'users';
    public const PERMISSION_ENTITIES = 'permission_entities';
    public const USER_PERMISSIONS = 'user_permissions';
    public const RH_EMPLOYEES = 'rh_employees';
    public const RH_EMPLOYEE_HISTORY = 'rh_employee_history';
    public const RH_EMPLOYEE_MUTATIONS = 'rh_employee_mutations';
    public const RH_EXIT_REASONS = 'rh_exit_reasons';
    public const RH_FUNCTIONS = 'rh_functions';
    public const RH_SERVICES = 'rh_services';
    public const RH_STATUSES = 'rh_statuses';

    public static function all(): array
    {
        return [
            self::BTP_PROJETS => ['module'=>'BTP','name'=>'Projets BTP','description'=>'Projets et clients','sort_order'=>500],
            self::BTP_DEVIS => ['module'=>'BTP','name'=>'Devis BTP','description'=>'Devis et approbations','sort_order'=>510],
            self::BTP_CHANTIERS => ['module'=>'BTP','name'=>'Chantiers BTP','description'=>'BC et travaux','sort_order'=>520],
            self::BTP_SOUS_TRAITANCES => ['module'=>'BTP','name'=>'Sous-traitance','description'=>'Offres et visas','sort_order'=>530],
            self::BTP_FINANCES => ['module'=>'BTP','name'=>'Déboursés et dépenses','description'=>'Budgets et pilotage','sort_order'=>540],
            self::USERS => [
                'module' => 'Administration',
                'name' => 'Comptes utilisateurs',
                'description' => 'Comptes de connexion, état et rattachement au personnel.',
                'sort_order' => 10,
            ],
            self::PERMISSION_ENTITIES => [
                'module' => 'Administration',
                'name' => 'Entités de permissions',
                'description' => 'Catalogue des données pouvant être protégées.',
                'sort_order' => 20,
            ],
            self::USER_PERMISSIONS => [
                'module' => 'Administration',
                'name' => 'Droits utilisateurs',
                'description' => 'Matrice CRUD attribuée à chaque utilisateur.',
                'sort_order' => 30,
            ],
            self::RH_EMPLOYEES => [
                'module' => 'Ressources humaines',
                'name' => 'Personnel',
                'description' => 'Identité, contact, dossier administratif et situation du personnel.',
                'sort_order' => 100,
            ],
            self::RH_EMPLOYEE_HISTORY => [
                'module' => 'Ressources humaines',
                'name' => 'Historique RH',
                'description' => 'Événements, notes, entrées, sorties et réintégrations.',
                'sort_order' => 110,
            ],
            self::RH_EMPLOYEE_MUTATIONS => [
                'module' => 'Ressources humaines',
                'name' => 'Mutations du personnel',
                'description' => 'Changements de service, fonction, statut et site.',
                'sort_order' => 120,
            ],
            self::RH_EXIT_REASONS => [
                'module' => 'Ressources humaines',
                'name' => 'Motifs de sortie',
                'description' => 'Référentiel des motifs de sortie du personnel.',
                'sort_order' => 130,
            ],
            self::RH_FUNCTIONS => [
                'module' => 'Ressources humaines',
                'name' => 'Fonctions',
                'description' => 'Référentiel des fonctions occupées.',
                'sort_order' => 140,
            ],
            self::RH_SERVICES => [
                'module' => 'Ressources humaines',
                'name' => 'Services',
                'description' => 'Référentiel des services et affectations.',
                'sort_order' => 150,
            ],
            self::RH_STATUSES => [
                'module' => 'Ressources humaines',
                'name' => 'Statuts contractuels',
                'description' => 'Référentiel des statuts du personnel.',
                'sort_order' => 160,
            ],
        ];
    }

    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function exists(string $code): bool
    {
        return isset(self::all()[$code]);
    }

    public static function label(string $code): string
    {
        return self::all()[$code]['name'] ?? $code;
    }

    public static function codesForModule(string $module): array
    {
        return array_keys(array_filter(
            self::all(),
            static fn(array $entity): bool => $entity['module'] === $module
        ));
    }
}
