<?php

declare(strict_types=1);

namespace App\Application\Exception\Api;

final class DemoApiNotFoundException extends \RuntimeException
{
    public function __construct(string $resource, string $id)
    {
        parent::__construct(sprintf('%s "%s" was not found.', $resource, $id));
    }
}
