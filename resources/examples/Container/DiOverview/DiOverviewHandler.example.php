<?php

declare(strict_types=1);

namespace App\Application\Handler\Container;

use App\Application\Payload\Container\DiOverviewPayload;
use App\Application\Resource\Page\DiOverviewResource;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: DiOverviewPayload::class, resource: DiOverviewResource::class)]
final class DiOverviewHandler implements TypedHandlerInterface
{
    public function handle(DiOverviewPayload $payload, DiOverviewResource $resource): DiOverviewResource
    {
        return $resource->withCanon(['readonly', 'mutable', 'factory', 'contracts']);
    }
}
