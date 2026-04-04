<?php

declare(strict_types=1);

namespace App\Application\Db\TableModel;

use Semitexa\Orm\Attributes\Column;
use Semitexa\Orm\Attributes\FromTable;

#[FromTable(name: 'machine_credentials')]
final class MachineCredentialTableModel
{
    #[Column(type: 'varchar', length: 64)]
    public string $id;

    #[Column(type: 'varchar', length: 255)]
    public string $secret_hash;
}
