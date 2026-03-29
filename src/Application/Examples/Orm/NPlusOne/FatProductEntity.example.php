<?php

declare(strict_types=1);

namespace LegacyOrm\Entity;

final class Product
{
    public string $id;
    public string $name;
    public string $description;
    public string $status;
    public string $price;
    public string $createdAt;
    public string $updatedAt;

    private ?Category $category = null;

    /** @var list<Review>|null */
    private ?array $reviews = null;

    public function getCategory(): ?Category
    {
        // Hidden database query on first access.
        if ($this->category === null) {
            $this->category = $this->lazyLoadCategory();
        }

        return $this->category;
    }

    /** @return list<Review> */
    public function getReviews(): array
    {
        // Another hidden query per row.
        if ($this->reviews === null) {
            $this->reviews = $this->lazyLoadReviews();
        }

        return $this->reviews;
    }
}
