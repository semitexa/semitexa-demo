<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Discovery\DiscoveredRoute;
use Semitexa\Core\HttpResponse;
use Semitexa\Core\Pipeline\ResponseRenderer;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductDetailPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductSchemaPayload;

/**
 * The v1 product API answers only in what it declares.
 *
 * MEASURED 2026-10-16 (10 Accept headers x 6 demo API endpoints): the three
 * v1 endpoints answered `Accept: application/xml` and
 * `Accept: application/json;q=0` with 200 application/json, while
 * active-version, sunset-version and structured-errors answered 406. Two
 * causes: /products and /products/{slug} declared no `produces`, and the
 * renderer ignored the one /_schema declared because the response has no
 * render handle.
 *
 * The routes are built from the payloads' own attributes, so dropping a
 * declaration fails here, not on the live site.
 */
final class ProductApiNegotiationTest extends TestCase
{
    /** @return iterable<string, array{class-string, string|null, bool}> */
    public static function matrix(): iterable
    {
        $endpoints = [
            'collection' => ProductListPayload::class,
            'detail' => ProductDetailPayload::class,
            'schema' => ProductSchemaPayload::class,
        ];
        $accepts = [
            'no Accept' => [null, true],
            'anything' => ['*/*', true],
            'a browser' => ['text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', true],
            'an HTTP client' => ['application/json, text/plain, */*', true],
            'json' => ['application/json', true],
            'xml only' => ['application/xml', false],
            'json refused' => ['application/json;q=0', false],
            'a page only' => ['text/html', false],
        ];

        foreach ($endpoints as $endpoint => $payload) {
            foreach ($accepts as $label => [$accept, $answered]) {
                yield "{$endpoint}: {$label}" => [$payload, $accept, $answered];
            }
        }

        // JSON-LD is the collection's and the detail's second representation;
        // the schema is not offered in it.
        yield 'collection: json-ld' => [ProductListPayload::class, 'application/ld+json', true];
        yield 'detail: json-ld with json refused' => [ProductDetailPayload::class, 'application/ld+json, application/json;q=0', true];
        yield 'schema: json-ld' => [ProductSchemaPayload::class, 'application/ld+json', false];
    }

    /** @param class-string $payload */
    #[Test]
    #[DataProvider('matrix')]
    public function the_endpoint_answers_only_what_it_declares(string $payload, ?string $accept, bool $answered): void
    {
        $rendered = (new ResponseRenderer())->render($this->body(), null, $this->get($accept), $this->routeOf($payload));

        if ($answered) {
            self::assertNotInstanceOf(HttpResponse::class, $rendered);
            self::assertSame('{"data":[]}', $rendered->getContent());

            return;
        }

        self::assertInstanceOf(HttpResponse::class, $rendered);
        self::assertSame(406, $rendered->getStatusCode());
    }

    /** @param class-string $payload */
    private function routeOf(string $payload): DiscoveredRoute
    {
        $attributes = (new \ReflectionClass($payload))->getAttributes(AsPublicPayload::class);
        self::assertCount(1, $attributes, $payload . ' must carry #[AsPublicPayload]');
        $route = $attributes[0]->newInstance();

        return new DiscoveredRoute(
            path: (string) $route->path,
            methods: ['GET'],
            name: $payload,
            requestClass: $payload,
            responseClass: 'Stub\\Response',
            handlers: [],
            type: 'http_request',
            transport: null,
            produces: $route->produces,
            consumes: null,
            module: 'semitexa-demo',
        );
    }

    /** What DemoApiResponse is to the renderer: a body with no render handle. */
    private function body(): object
    {
        return new class {
            /** @var array<string, string> */
            private array $headers = ['Content-Type' => 'application/json'];
            private string $content = '{"data":[]}';

            public function getRenderHandle(): ?string
            {
                return null;
            }

            /** @return array<string, mixed> */
            public function getRenderContext(): array
            {
                return [];
            }

            public function getContent(): string
            {
                return $this->content;
            }

            public function setContent(string $content): void
            {
                $this->content = $content;
            }

            public function setHeader(string $name, string $value): void
            {
                $this->headers[$name] = $value;
            }

            /** @return array<string, string> */
            public function getHeaders(): array
            {
                return $this->headers;
            }
        };
    }

    private function get(?string $accept): Request
    {
        return new Request(
            method: 'GET',
            uri: '/demo/api/v1/products',
            headers: $accept === null ? [] : ['Accept' => $accept],
            query: [],
            post: [],
            server: ['request_uri' => '/demo/api/v1/products'],
            cookies: [],
        );
    }
}
