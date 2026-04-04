<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class DiOverviewResource
{
    /**
     * @param list<string> $topics
     */
    public function withCanon(array $topics): self
    {
        return $this;
    }
}
