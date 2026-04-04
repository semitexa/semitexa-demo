<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Payload\Api\ApiSchemaDiscoveryPayload;
use App\Application\Resource\Page\ApiSchemaPageResource;
use App\Domain\Api\SchemaRegistryInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ApiSchemaDiscoveryPayload::class, resource: ApiSchemaPageResource::class)]
final class ApiSchemaDiscoveryHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected SchemaRegistryInterface $schemaRegistry;

    public function handle(ApiSchemaDiscoveryPayload $payload, ApiSchemaPageResource $resource): ApiSchemaPageResource
    {
        return $resource->fromSchema($this->schemaRegistry->describe());
    }
}
