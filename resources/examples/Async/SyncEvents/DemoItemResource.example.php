<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class DemoItemResource
{
    private string $status = '';

    public function withStatus(string $status): self
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
