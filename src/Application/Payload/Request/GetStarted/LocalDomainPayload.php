<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/local-domain',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Local Domain',
    slug: 'local-domain',
    summary: 'Register `.test` domains through the built-in local-domain helper instead of relying on ad-hoc host setup.',
    order: 2,
    highlights: ['TENANCY_BASE_DOMAIN', 'bin/semitexa local-domain:add', 'bin/semitexa local-domain:list', 'server:restart'],
    entryLine: 'A framework with tenancy should not be introduced through localhost forever. Register a stable local domain early and let the runtime behave like a product host.',
    learnMoreLabel: 'See the local domain flow →',
    deepDiveLabel: 'Why domain-first local work matters →',
)]
final class LocalDomainPayload
{
}
