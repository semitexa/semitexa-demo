<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Closure;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Graphql\Domain\Contract\GraphqlProjectionInterface;

final class DemoApiResponse extends ResourceResponse implements ResourceInterface, GraphqlProjectionInterface
{
    /** Builds the typed GraphQL output; only a GraphQL read calls it, so REST pays nothing. */
    private ?Closure $graphqlProjection = null;

    public function withJsonPayload(array $payload, string $contentType = 'application/json'): self
    {
        $this->setContent(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        );
        $this->setHeader('Content-Type', $contentType);

        return $this;
    }

    /**
     * The body above is written with setContent(), so the render context stays
     * empty — filling it would make the HTTP renderer replace that body — and
     * GraphQL reads this projection instead.
     *
     * @param Closure(): object $projection
     */
    public function withGraphqlProjection(Closure $projection): self
    {
        $this->graphqlProjection = $projection;

        return $this;
    }

    public function toGraphqlValue(): mixed
    {
        return $this->graphqlProjection === null ? null : ($this->graphqlProjection)();
    }
}
