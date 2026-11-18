<?php
declare(strict_types=1);
namespace App\View\Pages\Admin;
final class FebAdminPage { public function __construct(public readonly array $workflows,public readonly array $users,public readonly array $chantiers,public readonly array $accesses,public readonly array $budgets,public readonly array $history,public readonly bool $showWorkflowForm=false,public readonly ?array $workflow=null,public readonly array $eligibleBudgets=[],public readonly int $selectedChantier=0){} }
