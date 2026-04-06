<?php

declare(strict_types=1);

use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Exception\ValidationException;

#[AsPayload(
    path: '/checkout',
    methods: ['POST'],
    responseWith: CheckoutResultResource::class,
)]
final class CreateCheckoutPayload
{
    protected string $email = '';
    protected ?string $coupon = null;
    protected bool $agreeToTerms = false;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);

        if ($email === '') {
            throw new ValidationException(['email' => ['Email is required.']]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => ['Email must be valid.']]);
        }

        $this->email = $email;
    }

    public function getCoupon(): ?string
    {
        return $this->coupon;
    }

    public function setCoupon(string $coupon): void
    {
        $coupon = strtoupper(trim($coupon));
        $this->coupon = $coupon !== '' ? $coupon : null;
    }

    public function hasAcceptedTerms(): bool
    {
        return $this->agreeToTerms;
    }

    public function setAgreeToTerms(bool $agreeToTerms): void
    {
        if ($agreeToTerms !== true) {
            throw new ValidationException(['agreeToTerms' => ['Terms must be accepted.']]);
        }

        $this->agreeToTerms = $agreeToTerms;
    }
}
