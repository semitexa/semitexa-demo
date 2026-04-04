<?php

declare(strict_types=1);

namespace App\Application\Db\Model;

use Semitexa\Orm\Attributes\Column;
use Semitexa\Orm\Attributes\FromTable;

#[FromTable(name: 'products')]
final class ProductResource
{
    #[Column(type: 'uuid')]
    public string $id;

    #[Column(type: 'varchar', length: 255)]
    public string $name;

    #[Column(type: 'decimal', precision: 10, scale: 2)]
    public string $price;

    #[Column(type: 'varchar', length: 32)]
    public string $status;
}
