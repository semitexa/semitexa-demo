<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class MailerStatusResource
{
    public function withResolvedMailer(string $className): self
    {
        return $this;
    }
}
