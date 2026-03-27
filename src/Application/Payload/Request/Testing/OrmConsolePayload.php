<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/testing/orm-console',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'testing',
    title: 'ORM Console Toolkit',
    slug: 'orm-console',
    summary: 'The ORM ships with a practical CLI surface: status, diff, sync, and seed commands with dry-run safety and SQL plan export.',
    order: 1,
    highlights: ['orm:status', 'orm:diff', 'orm:sync', 'orm:seed', '--output'],
    entryLine: 'Framework credibility also lives in operations. The ORM CLI should tell you what will change before it changes anything.',
    learnMoreLabel: 'See the command surface →',
    deepDiveLabel: 'What each command is for →',
)]
final class OrmConsolePayload
{
}
