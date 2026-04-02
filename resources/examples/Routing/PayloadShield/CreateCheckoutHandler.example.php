<?php

declare(strict_types=1);

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: CreateCheckoutPayload::class, resource: CheckoutResultResource::class)]
final class CreateCheckoutHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected CheckoutServiceInterface $checkoutService;

    public function handle(CreateCheckoutPayload $payload, CheckoutResultResource $resource): CheckoutResultResource
    {
        $checkout = $this->checkoutService->create(
            email: $payload->getEmail(),
            coupon: $payload->getCoupon(),
            agreeToTerms: $payload->hasAcceptedTerms(),
        );

        $resource->fromCheckout($checkout);

        return $resource;
    }
}
