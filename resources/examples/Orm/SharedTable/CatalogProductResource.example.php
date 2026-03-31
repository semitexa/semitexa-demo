<?php

declare(strict_types=1);

namespace App\Catalog\Resource;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\PrimaryKey;
use Semitexa\Orm\Attribute\TenantScoped;

#[FromTable(name: 'demo_products')]
#[TenantScoped(strategy: 'same_storage')]
final class CatalogProductResource
{
    #[PrimaryKey(strategy: 'uuid')]
    #[Column(type: MySqlType::Binary, length: 16)]
    public string $id;

    #[Column(type: MySqlType::Varchar, length: 64)]
    public string $tenant_id;

    #[Column(type: MySqlType::Varchar, length: 190)]
    public string $name;

    #[Column(type: MySqlType::Text)]
    public ?string $description = null;

    #[Column(type: MySqlType::Decimal, precision: 10, scale: 2)]
    public string $price;

    #[Column(type: MySqlType::Varchar, length: 32)]
    public string $status = 'draft';
}
