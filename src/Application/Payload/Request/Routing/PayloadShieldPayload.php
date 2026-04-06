<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/payload-shield',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Payload As A Shield',
    slug: 'payload-shield',
    summary: 'Hydration happens before the handler, and each setter owns the normalization and guard logic for its own field.',
    order: 3,
    highlights: ['PayloadHydrator', 'ValidationException', 'setter guards', '422 before handler'],
    entryLine: 'A payload is the one trusted boundary: external data is normalized inside setters before application code runs.',
    learnMoreLabel: 'See the boundary in code →',
    deepDiveLabel: 'How the shield works →',
)]
final class PayloadShieldPayload
{
}
