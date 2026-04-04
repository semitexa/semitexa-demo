<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class DemoItemResource
{
    public function withStatus(string $status): self
    {
        return $this;
    }
}
