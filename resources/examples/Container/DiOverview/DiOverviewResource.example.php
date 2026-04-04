<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class DiOverviewResource
{
    /** @var list<string> */
    private array $topics = [];

    /**
     * @param list<string> $topics
     */
    public function withCanon(array $topics): self
    {
        $resource = clone $this;
        $resource->topics = $topics;

        return $resource;
    }

    /**
     * @return list<string>
     */
    public function topics(): array
    {
        return $this->topics;
    }
}
