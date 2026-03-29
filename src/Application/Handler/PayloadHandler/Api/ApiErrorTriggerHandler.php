<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\AccessDeniedException;
use Semitexa\Core\Exception\ConflictException;
use Semitexa\Core\Exception\RateLimitException;
use Semitexa\Core\Exception\ValidationException;
use Semitexa\Demo\Application\Exception\DemoApiNotFoundException;
use Semitexa\Demo\Application\Payload\Request\Api\ApiErrorTriggerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;

#[AsPayloadHandler(payload: ApiErrorTriggerPayload::class, resource: DemoApiResponse::class)]
final class ApiErrorTriggerHandler implements TypedHandlerInterface
{
    public function handle(ApiErrorTriggerPayload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $type = strtolower($payload->getType());

        throw match ($type) {
            'validation' => new ValidationException([
                'fields' => [
                    'The requested sparse fieldset is invalid.',
                    'Use only slug,name,price,description,status,category,rating,reviewCount.',
                ],
            ]),
            'forbidden' => new AccessDeniedException('The selected machine credential cannot access this resource.'),
            'conflict' => new ConflictException('The requested representation profile conflicts with the current endpoint state.'),
            'rate-limit' => new RateLimitException(45),
            default => new DemoApiNotFoundException('Demo API product', 'missing-product'),
        };
    }
}
