<?php

declare(strict_types=1);

namespace App\Application\Payload\Events;

final class CreateDemoItemPayload
{
    protected string $id = '';
    protected string $name = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
