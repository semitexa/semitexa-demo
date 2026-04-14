<?php

declare(strict_types=1);

namespace App\Application\Db\ResourceModel;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;

#[FromTable(name: 'machine_credentials')]
final class MachineCredentialResourceModel
{
    #[Column(type: MySqlType::Varchar, length: 64)]
    public string $id;

    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $secret_hash;
}
