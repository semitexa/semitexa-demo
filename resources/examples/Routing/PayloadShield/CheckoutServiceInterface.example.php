<?php

declare(strict_types=1);

namespace Examples\Routing\PayloadShield;

interface CheckoutServiceInterface
{
    public function create(string $email, ?string $coupon, bool $agreeToTerms): object;
}
