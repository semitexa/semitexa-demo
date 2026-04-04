<?php

declare(strict_types=1);

namespace App\Application\Resource\Admin;

final class UserListResource
{
    /**
     * @param list<array<string, mixed>> $users
     */
    public function fromUsers(array $users): self
    {
        return $this;
    }
}
