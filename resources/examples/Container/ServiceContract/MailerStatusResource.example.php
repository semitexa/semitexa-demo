<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

use Semitexa\Ssr\Http\Response\HtmlResponse;

final class MailerStatusResource extends HtmlResponse
{
    public function withResolvedMailer(string $className): self
    {
        return $this->with('resolvedMailer', $className);
    }
}
