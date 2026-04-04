<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface MachineCredentialRepositoryInterface
{
    public function findById(string $id): ?MachineCredential;
}
