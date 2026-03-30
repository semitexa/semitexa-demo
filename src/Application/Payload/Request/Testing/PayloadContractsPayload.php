<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/testing/payload-contracts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'testing',
    title: 'Payload Contract Testing',
    slug: 'payload-contracts',
    summary: 'Scaffold one project-level contract test and let strategy profiles verify payload boundaries without hand-writing repetitive negative cases.',
    order: 2,
    highlights: ['#[TestablePayload]', 'test:init', 'test:run', 'StrictProfileStrategy', 'MonkeyTestingStrategy'],
    entryLine: 'Testing in Semitexa can start from the transport boundary itself: payloads declare what should be verified, and the framework runs the strategy suite.',
    learnMoreLabel: 'See the testing workflow →',
    deepDiveLabel: 'What the profiles actually buy you →',
)]
final class PayloadContractsPayload
{
}
