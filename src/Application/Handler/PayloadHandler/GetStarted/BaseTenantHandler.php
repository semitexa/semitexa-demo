<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BaseTenantPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BaseTenantPayload::class, resource: DemoFeatureResource::class)]
final class BaseTenantHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BaseTenantPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'The first tenant in Semitexa is configuration, not wizardry. You define tenant identity and domains through environment variables, then the tenancy layer resolves requests against that configuration.',
            'how' => 'The framework scans `TENANT_<ID>_...` variables, maps declared domains to that tenant id, and lets domain strategies resolve the active tenant before the rest of the request pipeline runs.',
            'why' => 'This keeps the first tenant explicit and reviewable. Engineers can read the local `.env` and immediately see which tenant ids, names, states, and hosts are supposed to exist.',
            'keywords' => [
                ['term' => 'TENANT_ACME_NAME', 'definition' => 'Human-readable tenant name attached to the tenant id `acme`.'],
                ['term' => 'TENANT_ACME_STATUS', 'definition' => 'The lifecycle state for the tenant, for example `active`.'],
                ['term' => 'TENANT_ACME_DOMAIN', 'definition' => 'The primary local domain that should resolve to the tenant `acme`.'],
                ['term' => 'DomainStrategy', 'definition' => 'The tenancy resolution step that maps concrete hosts to tenant ids.'],
            ],
        ];

        return $resource
            ->pageTitle('Base Tenant — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'base-tenant',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Get Started')
            ->withSlug('base-tenant')
            ->withTitle('Base Tenant')
            ->withSummary('Define the first tenant through environment variables and resolve it through a real local host.')
            ->withEntryLine('The first tenant is configuration, not ceremony: define it in `.env`, register the host, restart, and open the tenant like a real product surface.')
            ->withHighlights(['TENANT_ACME_NAME', 'TENANT_ACME_STATUS', 'TENANT_ACME_DOMAIN', 'DomainStrategy'])
            ->withLearnMoreLabel('See the tenant bootstrap flow →')
            ->withDeepDiveLabel('How Semitexa resolves that tenant →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Tenant Bootstrap',
                'title' => 'Add the first tenant to `.env` and open it through a real host',
                'summary' => 'There is no separate bootstrap wizard required for the normal local flow. Define the tenant in environment variables, register the host, restart, and open it in the browser.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Declare the tenant in `.env`',
                        'summary' => 'Use one tenant id prefix and keep the first tenant shape explicit.',
                        'commands' => [
                            'TENANCY_BASE_DOMAIN=semitexa.test',
                            'TENANT_ACME_NAME=Acme Workspace',
                            'TENANT_ACME_STATUS=active',
                            'TENANT_ACME_DOMAIN=acme.semitexa.test',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Register the tenant host',
                        'summary' => 'The domain must exist in the local DNS helper as well as in the tenancy configuration.',
                        'commands' => [
                            'bin/semitexa local-domain:add acme.semitexa.test',
                            'bin/semitexa server:restart',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Open the tenant',
                        'summary' => 'Browse the tenant host directly so resolution happens through the real request host.',
                        'commands' => [
                            'http://acme.semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Useful Variants',
                    'rules' => [
                        'Use `TENANT_<ID>_DOMAINS` when the same tenant should answer to more than one local host.',
                        'Use `TENANT_<ID>_PUBLIC_DOMAIN` and `TENANT_<ID>_PUBLIC_DOMAINS` when you separately model public production hosts.',
                        'The tenant id is the part between `TENANT_` and the field suffix. `TENANT_ACME_*` becomes tenant id `acme`.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Resolution Rules',
                'title' => 'What makes the first tenant reliable',
                'summary' => 'A tenant should be easy to explain from configuration alone. If it takes custom lore to know which host resolves to which tenant, the local setup is already too fragile.',
                'rules' => [
                    'Keep tenant ids stable and human-readable because they become part of local and operational reasoning.',
                    'Prefer one clear primary `TENANT_<ID>_DOMAIN` before introducing additional aliases.',
                    'When tenant resolution looks wrong, check `.env`, then `local-domain:list`, then the actual browser host in that order.',
                    'Use the Tenancy & Isolation pages after this setup if you want to inspect how Semitexa resolves and propagates tenant context internally.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
