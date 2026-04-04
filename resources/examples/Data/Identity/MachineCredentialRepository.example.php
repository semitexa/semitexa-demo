<?php

declare(strict_types=1);

namespace App\Application\Db\Repository;

use App\Domain\Auth\MachineCredential;
use App\Domain\Auth\MachineCredentialRepositoryInterface;

final class MachineCredentialRepository implements MachineCredentialRepositoryInterface
{
    public function findById(string $id): ?MachineCredential
    {
        return null;
    }
}
