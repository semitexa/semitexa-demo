<?php

declare(strict_types=1);

namespace App\Application\Handler\Container;

use App\Application\Payload\Container\ReadonlyInjectionPayload;
use App\Application\Resource\Page\ServiceProbeResource;
use App\Domain\Demo\FeatureRegistryInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ReadonlyInjectionPayload::class, resource: ServiceProbeResource::class)]
final class ReadonlyInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected FeatureRegistryInterface $featureRegistry;

    public function handle(ReadonlyInjectionPayload $payload, ServiceProbeResource $resource): ServiceProbeResource
    {
        return $resource->withRegistryId(spl_object_id($this->featureRegistry));
    }
}
