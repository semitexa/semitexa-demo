<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/payload-shield',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'routing',
    title: 'Payload As A Shield',
    slug: 'payload-shield',
    summary: 'Hydration, type casting, and validation happen before the handler, so business code receives one trusted object instead of raw external input.',
    order: 3,
    highlights: ['ValidatablePayload', 'RequestDtoHydrator', 'PayloadValidator', '422 before handler'],
    entryLine: 'A payload is the one trusted boundary: external data is normalized and validated before application code runs.',
    learnMoreLabel: 'See the boundary in code →',
    deepDiveLabel: 'How the shield works →',
)]
final class PayloadShieldPayload
{
}
