<?php

declare(strict_types=1);

namespace App\Application\Handler\Container;

use App\Application\Payload\Container\MutableInjectionPayload;
use App\Application\Resource\Page\ExecutionBagResource;
use App\Domain\Execution\ExecutionBag;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsMutable;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: MutableInjectionPayload::class, resource: ExecutionBagResource::class)]
final class MutableInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected ExecutionBag $bag;

    public function handle(MutableInjectionPayload $payload, ExecutionBagResource $resource): ExecutionBagResource
    {
        $this->bag->put('request_id', uniqid('req_', true));

        return $resource->fromBag($this->bag);
    }
}
