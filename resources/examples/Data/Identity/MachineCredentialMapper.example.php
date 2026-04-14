<?php

declare(strict_types=1);

namespace App\Application\Db\Mapper;

use App\Application\Db\ResourceModel\MachineCredentialResourceModel;
use App\Domain\Auth\MachineCredential;

final class MachineCredentialMapper
{
    public function toDomain(MachineCredentialResourceModel $row): MachineCredential
    {
        return new MachineCredential($row->id, $row->secret_hash, []);
    }
}
