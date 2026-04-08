<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/locale-setup',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Locale Setup',
    slug: 'locale-setup',
    summary: 'Configure the minimal Semitexa Locale contract: default locale, supported locales, JSON catalogs, and one Twig translation check.',
    order: 4,
    highlights: ['LOCALE_DEFAULT', 'LOCALE_SUPPORTED', 'Application/View/locales/{lang}.json', 'trans()'],
    entryLine: 'Locale should become a boring, explicit part of project setup: pick the default, declare supported locales, add JSON catalogs, and verify one translated string end to end.',
    learnMoreLabel: 'See the minimal locale setup →',
    deepDiveLabel: 'What to verify before adding more languages →',
)]
final class LocaleSetupPayload
{
}
