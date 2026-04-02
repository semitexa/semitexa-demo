<?php

declare(strict_types=1);

use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Contract\ValidatablePayload;
use Semitexa\Core\Http\PayloadValidationResult;

#[AsPayload(
    path: '/products/{slug}',
    methods: ['GET'],
    responseWith: ProductShowcaseResource::class,
    requirements: ['slug' => '[a-z0-9-]+'],
    defaults: ['slug' => 'wireless-headphones'],
)]
final class ProductShowcasePayload implements ValidatablePayload
{
    protected string $slug = 'wireless-headphones';

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $normalized = strtolower(trim($slug));
        $normalized = preg_replace('/[^a-z0-9-]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');

        $this->slug = $normalized;
    }

    public function validate(): PayloadValidationResult
    {
        $errors = [];

        if ($this->slug === '') {
            $errors['slug'][] = 'Product slug is required.';
        }

        if (strlen($this->slug) > 120) {
            $errors['slug'][] = 'Product slug must stay below 120 characters.';
        }

        if (preg_match('/^[a-z0-9-]+$/', $this->slug) !== 1) {
            $errors['slug'][] = 'Product slug may only contain lowercase letters, numbers, and dashes.';
        }

        return new PayloadValidationResult($errors === [], $errors);
    }
}
