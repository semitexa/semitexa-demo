<?php

declare(strict_types=1);

namespace App\Application\Db\Model;

use Semitexa\Orm\Attributes\Column;
use Semitexa\Orm\Attributes\FromTable;

#[FromTable(name: 'categories')]
final class CategoryResource
{
    #[Column(type: 'uuid')]
    public string $id;

    #[Column(type: 'varchar', length: 255)]
    public string $name;
}
