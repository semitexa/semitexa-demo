<?php

declare(strict_types=1);

namespace App\Application\Handler\Container;

use App\Application\Payload\Container\FactoryInjectionPayload;
use App\Application\Resource\Page\FactoryProbeResource;
use App\Domain\Mail\TransactionalMailer;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsFactory;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: FactoryInjectionPayload::class, resource: FactoryProbeResource::class)]
final class FactoryInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsFactory]
    protected \Closure $mailerFactory;

    public function handle(FactoryInjectionPayload $payload, FactoryProbeResource $resource): FactoryProbeResource
    {
        /** @var TransactionalMailer $mailer */
        $mailer = ($this->mailerFactory)();

        return $resource->withMailerId(spl_object_id($mailer));
    }
}
