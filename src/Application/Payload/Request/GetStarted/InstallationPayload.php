<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/installation',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Installation',
    slug: 'installation',
    summary: 'Create the project, prepare `.env`, and bring up the Semitexa runtime the supported way.',
    order: 1,
    highlights: ['install.sh', 'bin/semitexa server:start', 'self-test', 'routes:list'],
    entryLine: 'The first useful Semitexa experience should end with a running app and an operator shell you can trust, not with a half-finished checklist.',
    learnMoreLabel: 'See the installation flow →',
    deepDiveLabel: 'What to verify after boot →',
)]
final class InstallationPayload
{
}
