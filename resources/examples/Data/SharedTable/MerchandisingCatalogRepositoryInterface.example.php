<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

interface MerchandisingCatalogRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listCampaignProducts(): array;
}
