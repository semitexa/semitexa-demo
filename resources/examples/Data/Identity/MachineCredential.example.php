<?php

declare(strict_types=1);

namespace App\Domain\Auth;

final class MachineCredential
{
    public function __construct(
        public readonly string $id,
        public readonly string $secretHash,
        public readonly array $scopes,
    ) {}
}
