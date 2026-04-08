<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocalDomainPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: LocalDomainPayload::class, resource: DemoFeatureResource::class)]
final class LocalDomainHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(LocalDomainPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'A serious local setup should have real hostnames. Semitexa already ships DNS and proxy helpers, so local tenant work should happen through `.test` domains, not endless `localhost` tabs.',
            'how' => 'The runtime reads `TENANCY_BASE_DOMAIN`, the CLI registers local hosts through `local-domain:add`, and a restart lets the running stack pick up the new local-domain and proxy shape consistently.',
            'why' => 'Tenancy, domain routing, cookie scope, and absolute URL behavior become much easier to reason about when local development already uses real hostnames.',
            'keywords' => [
                ['term' => 'TENANCY_BASE_DOMAIN', 'definition' => 'The base local domain used by tenancy resolution, typically a `.test` host.'],
                ['term' => 'bin/semitexa local-domain:add', 'definition' => 'Registers a local domain in the Semitexa local-domain helper instead of relying on ad-hoc manual host edits.'],
                ['term' => 'local-domain:list', 'definition' => 'Shows which local domains are currently registered in the Semitexa local-domain helper.'],
                ['term' => 'server:restart', 'definition' => 'Restarts the local runtime so new environment, DNS, and proxy settings are applied cleanly.'],
            ],
        ];

        return $resource
            ->pageTitle('Local Domain — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'local-domain',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('local-domain')
            ->withTitle('Local Domain')
            ->withSummary('Register `.test` domains through the built-in local-domain helper instead of relying on ad-hoc host setup.')
            ->withEntryLine('A framework with tenancy should not be introduced through localhost forever. Register a stable local domain early and let the runtime behave like a product host.')
            ->withHighlights(['TENANCY_BASE_DOMAIN', 'bin/semitexa local-domain:add', 'local-domain:list', 'server:restart'])
            ->withLearnMoreLabel('See the local domain flow →')
            ->withDeepDiveLabel('Why domain-first local work matters →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Local Host Strategy',
                'title' => 'Make local URLs look like product hosts',
                'summary' => 'Choose one `.test` base domain, register hosts through the Semitexa local-domain helper, and restart so the runtime resolves them predictably.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Choose the base domain',
                        'summary' => 'Keep the local base memorable and stable. The common example is `semitexa.test`.',
                        'commands' => [
                            'TENANCY_BASE_DOMAIN=semitexa.test',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Register the browser hosts',
                        'summary' => 'Register both the main host and any tenant-specific host you plan to open during local work.',
                        'commands' => [
                            'bin/semitexa local-domain:add semitexa.test',
                            'bin/semitexa local-domain:add acme.semitexa.test',
                            'bin/semitexa local-domain:list',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Restart and open the host',
                        'summary' => 'After domain changes, restart once so the runtime, proxy, and DNS layer agree on the same environment.',
                        'commands' => [
                            'bin/semitexa server:restart',
                            'http://semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Why Not /etc/hosts?',
                    'rules' => [
                        'The CLI-managed local-domain path is repeatable across projects and easier to inspect later with `local-domain:list`.',
                        'It keeps the local proxy and DNS story in the same operator surface instead of splitting it between shell scripts and OS edits.',
                        'It makes tenant hosts easier to add and remove as the project evolves.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Practical Rules',
                'title' => 'Keep local domain work boring and consistent',
                'summary' => 'The point of local domains is to reduce ambiguity, not to create a custom networking hobby project.',
                'rules' => [
                    'Prefer one `.test` base domain per project instead of mixing many local suffixes.',
                    'Register domains with `bin/semitexa local-domain:add` and remove stale ones with `bin/semitexa local-domain:remove`.',
                    'Restart after meaningful DNS or `.env` changes so the runtime shape stays coherent.',
                    'Open the real host in the browser once local DNS is in place, not only `localhost:9502`.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
