<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/module-structure',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Module Structure',
    slug: 'module-structure',
    summary: 'See the minimal Semitexa module spine first, then expand it into the full demo stack with catalog, shell, and SEO layers.',
    order: 2,
    highlights: ['Payload', 'Handler', 'Resource', 'Template', 'Catalog', 'SEO'],
    relatedPayloads: ['get-started/installation', 'get-started/beyond-controllers'],
    entryLine: 'The smallest useful module is easy to explain: the request boundary, the use case, the response shape, and the template are all owned explicitly.',
    learnMoreLabel: 'See the minimal stack →',
    deepDiveLabel: 'See the full module map →',
)]
final class ModuleStructurePayload
{
}
