<?php

declare(strict_types=1);

namespace App\Application\Handler\Container;

use App\Application\Payload\Container\ServiceContractPayload;
use App\Application\Resource\Page\MailerStatusResource;
use App\Domain\Mail\MailerInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ServiceContractPayload::class, resource: MailerStatusResource::class)]
final class ServiceContractHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected MailerInterface $mailer;

    public function handle(ServiceContractPayload $payload, MailerStatusResource $resource): MailerStatusResource
    {
        return $resource->withResolvedMailer($this->mailer::class);
    }
}
