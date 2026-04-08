<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocaleSetupPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: LocaleSetupPayload::class, resource: DemoFeatureResource::class)]
final class LocaleSetupHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(LocaleSetupPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'Semitexa Locale should start as a tiny, explicit contract: one default locale, one supported-locale list, one or two JSON catalogs, and one translated string rendered through Twig.',
            'how' => 'The locale package reads `LOCALE_*` settings from the environment, loads module locale files from `Application/View/locales/{lang}.json`, resolves the active locale through the configured resolver chain, and exposes translations through `trans()` in Twig.',
            'why' => 'If locale setup starts with hidden defaults and scattered translation files, every later language rule becomes harder to trust. The minimal setup should be explainable from `.env` and two JSON files alone.',
            'keywords' => [
                ['term' => 'LOCALE_DEFAULT', 'definition' => 'The primary locale used when no other resolver picks a different one.'],
                ['term' => 'LOCALE_SUPPORTED', 'definition' => 'Comma-separated list of locales the app is willing to resolve.'],
                ['term' => 'Application/View/locales/{lang}.json', 'definition' => 'Per-module JSON translation catalogs loaded at worker boot.'],
                ['term' => 'trans()', 'definition' => 'Twig function that resolves a translation key for the current locale.'],
                ['term' => 'LOCALE_URL_PREFIX', 'definition' => 'Optional switch that turns locale-prefixed URLs like `/uk/...` on or off.'],
            ],
        ];

        return $resource
            ->pageTitle('Locale Setup — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'locale-setup',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('locale-setup')
            ->withTitle('Locale Setup')
            ->withSummary('Configure the minimal Semitexa Locale contract: default locale, supported locales, JSON catalogs, and one Twig translation check.')
            ->withEntryLine('Locale should become a boring, explicit part of project setup: pick the default, declare supported locales, add JSON catalogs, and verify one translated string end to end.')
            ->withHighlights(['LOCALE_DEFAULT', 'LOCALE_SUPPORTED', 'Application/View/locales/{lang}.json', 'trans()'])
            ->withLearnMoreLabel('See the minimal locale setup →')
            ->withDeepDiveLabel('What to verify before adding more languages →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Locale Baseline',
                'title' => 'Add the smallest locale setup that is still real',
                'summary' => 'Declare the locale boundary in `.env`, add one JSON file per language, render one translated string through Twig, and restart once so the catalogs reload cleanly.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Declare the locale contract in `.env`',
                        'summary' => 'Keep the first setup tiny. You usually need only the default locale, fallback locale, supported locales, and whether locale prefixes belong in URLs.',
                        'commands' => [
                            'LOCALE_DEFAULT=en',
                            'LOCALE_FALLBACK=en',
                            'LOCALE_SUPPORTED=en,uk',
                            'LOCALE_URL_PREFIX=false',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Add one JSON catalog per language',
                        'summary' => 'Locale files are module-local. Start with one or two keys, not a giant spreadsheet dump.',
                        'commands' => [
                            'src/Application/View/locales/en.json',
                            '{"ContactForm.title":"Contact us","ContactForm.submit":"Send"}',
                            'src/Application/View/locales/uk.json',
                            '{"ContactForm.title":"Zvjazhit`sja z namy","ContactForm.submit":"Nadislaty"}',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Render and verify one translated string',
                        'summary' => 'Use `trans()` in Twig, restart once so the worker reloads catalogs, then verify the output through the locale path or request headers.',
                        'commands' => [
                            "{{ trans('ContactForm.title') }}",
                            'bin/semitexa server:restart',
                            "curl -H 'Accept-Language: uk' http://semitexa.test",
                            'http://semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Important Defaults',
                    'rules' => [
                        'If you installed from `semitexa/ultimate`, the locale package is already part of the baseline stack.',
                        'JSON locale catalogs are loaded at worker boot, so restart or reload after editing them.',
                        'Turn `LOCALE_URL_PREFIX=true` on only when you really want locale-prefixed URLs like `/uk/...`.',
                        'Keep keys module-scoped from day one. `ContactForm.title` ages much better than `title`.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Minimal Means Reviewable',
                'title' => 'What the first locale pass should already prove',
                'summary' => 'The first locale setup does not need plural rules, per-tenant overrides, or a language switcher yet. It only needs to prove that the contract is real and inspectable.',
                'rules' => [
                    'Keep `LOCALE_SUPPORTED` short until you have real copy for each language.',
                    'Use one translated Twig key as a smoke test before you spread `trans()` across the app.',
                    'If locale output looks wrong, check `.env`, then the JSON file path, then restart the worker before debugging deeper.',
                    'When tenant-specific locale defaults matter, continue with the Tenancy and platform locale pages after this baseline is working.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
