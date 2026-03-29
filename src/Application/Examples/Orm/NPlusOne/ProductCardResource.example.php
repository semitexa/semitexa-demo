<?php

declare(strict_types=1);

namespace App\Catalog\Resource;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\PrimaryKey;

#[FromTable(name: 'demo_products')]
final class ProductCardResource
{
    #[PrimaryKey(strategy: 'uuid')]
    #[Column(type: MySqlType::Binary, length: 16)]
    public string $id;

    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $name;

    #[Column(type: MySqlType::Decimal, precision: 10, scale: 2)]
    public string $price;

    #[Column(type: MySqlType::Binary, length: 16, nullable: true)]
    public ?string $category_id = null;
}
