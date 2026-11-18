<?php

declare(strict_types=1);
namespace App\Services\Crm\Ao;
interface AoSourceAdapterInterface
{
    public function getSourceName(): string;
    public function fetchPublicOpportunities(): array;
    public function normalizeOpportunity(array $raw): array;
}
