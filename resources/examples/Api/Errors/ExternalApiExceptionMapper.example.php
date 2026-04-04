<?php

declare(strict_types=1);

namespace App\Application\Api;

use Throwable;

final class ExternalApiExceptionMapper
{
    public function toEnvelope(Throwable $exception): array
    {
        return [
            'error' => [
                'type' => 'not_found',
                'message' => $exception->getMessage(),
            ],
        ];
    }
}
