<?php

declare(strict_types=1);

use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Contract\ValidatablePayload;
use Semitexa\Core\Http\PayloadValidationResult;
use Semitexa\Core\Validation\Trait\EmailValidationTrait;
use Semitexa\Core\Validation\Trait\NotBlankValidationTrait;

#[AsPayload(
    path: '/checkout',
    methods: ['POST'],
    responseWith: CheckoutResultResource::class,
)]
final class CreateCheckoutPayload implements ValidatablePayload
{
    use EmailValidationTrait;
    use NotBlankValidationTrait;

    protected string $email = '';
    protected ?string $coupon = null;
    protected bool $agreeToTerms = false;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = trim($email);
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
        $this->agreeToTerms = $agreeToTerms;
    }

    public function validate(): PayloadValidationResult
    {
        $errors = [];

        $this->validateNotBlank('email', $this->email, $errors);
        $this->validateEmail('email', $this->email, $errors);

        if ($this->agreeToTerms !== true) {
            $errors['agreeToTerms'][] = 'Terms must be accepted.';
        }

        return new PayloadValidationResult($errors === [], $errors);
    }
}
