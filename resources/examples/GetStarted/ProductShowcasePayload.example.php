<?php

declare(strict_types=1);

use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Exception\ValidationException;

#[AsPayload(
    path: '/products/{slug}',
    methods: ['GET'],
    responseWith: ProductShowcaseResource::class,
    requirements: ['slug' => '[a-z0-9-]+'],
    defaults: ['slug' => 'wireless-headphones'],
)]
final class ProductShowcasePayload
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

        if ($normalized === '') {
            throw new ValidationException(['slug' => ['Product slug is required.']]);
        }

        if (strlen($normalized) > 120) {
            throw new ValidationException(['slug' => ['Product slug must stay below 120 characters.']]);
        }

        if (preg_match('/^[a-z0-9-]+$/', $normalized) !== 1) {
            throw new ValidationException(['slug' => ['Product slug may only contain lowercase letters, numbers, and dashes.']]);
        }

        $this->slug = $normalized;
    }
}
