<?php

declare(strict_types=1);

namespace App\Application\Db\TableModel;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;

#[FromTable(name: 'machine_credentials')]
final class MachineCredentialTableModel
{
    #[Column(type: MySqlType::Varchar, length: 64)]
    public string $id;

    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $secret_hash;
}
