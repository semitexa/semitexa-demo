<?php

declare(strict_types=1);

namespace App\Application\Handler\Routing;

use App\Application\Payload\Routing\BasicRoutePayload;
use App\Application\Resource\Page\BasicPageResource;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: BasicRoutePayload::class, resource: BasicPageResource::class)]
final class BasicRouteHandler implements TypedHandlerInterface
{
    public function handle(BasicRoutePayload $payload, BasicPageResource $resource): BasicPageResource
    {
        return $resource
            ->withTitle('Basic Route')
            ->withSummary('Define a route with one attribute — no XML, no YAML, no config files.');
    }
}
