<?php

declare(strict_types=1);

namespace App\Application\Db\Mapper;

use App\Application\Db\TableModel\MachineCredentialTableModel;
use App\Domain\Auth\MachineCredential;

final class MachineCredentialMapper
{
    public function toDomain(MachineCredentialTableModel $row): MachineCredential
    {
        return new MachineCredential($row->id, $row->secret_hash, []);
    }
}
