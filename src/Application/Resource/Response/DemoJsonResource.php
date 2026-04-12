<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Core\Http\Response\ResponseFormat;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(format: ResponseFormat::Json)]
class DemoJsonResource extends HtmlResponse implements ResourceInterface
{
    public function withData(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->with((string) $key, $value);
        }

        return $this;
    }
}
