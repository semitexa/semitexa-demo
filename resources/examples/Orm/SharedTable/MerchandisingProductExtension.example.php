<?php

declare(strict_types=1);

namespace App\Merchandising\Resource;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;

#[FromTable(name: 'demo_products')]
final class MerchandisingProductExtension
{
    #[Column(type: MySqlType::Varchar, length: 80, nullable: true)]
    public ?string $badge_label = null;

    #[Column(type: MySqlType::Int, default: 0)]
    public int $merch_priority = 0;

    #[Column(type: MySqlType::Varchar, length: 48, nullable: true)]
    public ?string $campaign_code = null;
}
