<?php

declare(strict_types=1);

namespace App\Application\Resource\Admin;

final class UserListResource
{
    /** @var list<array<string, mixed>> */
    private array $users = [];

    /**
     * @param list<array<string, mixed>> $users
     */
    public function fromUsers(array $users): self
    {
        $resource = clone $this;
        $resource->users = $users;

        return $resource;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function users(): array
    {
        return $this->users;
    }
}
