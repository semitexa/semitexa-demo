<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Exception;

use Semitexa\Core\Exception\DomainException;
use Semitexa\Core\Http\HttpStatus;

final class DemoApiNotFoundException extends DomainException
{
    public function __construct(
        private readonly string $resource,
        private readonly string $identifier,
    ) {
        parent::__construct("{$resource} #{$identifier} not found.");
    }

    public function getStatusCode(): HttpStatus
    {
        return HttpStatus::NotFound;
    }

    public function getErrorCode(): string
    {
        return 'not_found';
    }

    public function getErrorContext(): array
    {
        return [
            'resource' => $this->resource,
            'identifier' => $this->identifier,
        ];
    }
}
