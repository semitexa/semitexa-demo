<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocaleSetupPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: LocaleSetupPayload::class, resource: DemoFeatureResource::class)]
final class LocaleSetupHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(LocaleSetupPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'locale-setup',
            entryLine: 'Locale should become a boring, explicit part of project setup: pick the default, declare supported locales, add JSON catalogs, and verify one translated string end to end.',
            learnMoreLabel: 'See the minimal locale setup →',
            deepDiveLabel: 'What to verify before adding more languages →',
            relatedSlugs: [],
            fallbackTitle: 'Locale Setup',
            fallbackSummary: 'Configure the minimal Semitexa Locale contract: default locale, supported locales, JSON catalogs, and one Twig translation check.',
            fallbackHighlights: ['LOCALE_DEFAULT', 'LOCALE_SUPPORTED', 'Application/View/locales/{lang}.json', 'trans()'],
        ));
    }
}
