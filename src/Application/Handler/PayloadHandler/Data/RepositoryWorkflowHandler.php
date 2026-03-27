<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Api\Application\Db\MySQL\Model\MachineCredentialResource;
use Semitexa\Api\Application\Db\MySQL\Repository\MachineCredentialRepository;
use Semitexa\Api\Domain\Contract\MachineCredentialRepositoryInterface;
use Semitexa\Api\Domain\Model\MachineCredential;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Data\RepositoryWorkflowPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Query\SelectQuery;

#[AsPayloadHandler(payload: RepositoryWorkflowPayload::class, resource: DemoFeatureResource::class)]
final class RepositoryWorkflowHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(RepositoryWorkflowPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('data', 'repository-workflow') ?? [];

        return $resource
            ->pageTitle('Repository Workflow — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'repository-workflow',
                'infoWhat' => $explanation['what'] ?? 'Handlers should depend on repository contracts and work with domain models, not persistence resources.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('repository-workflow')
            ->withTitle('Repository Workflow')
            ->withSummary('The canonical Semitexa path: handlers depend on repository contracts, repositories return domain models, and persistence resources stay behind the boundary.')
            ->withEntryLine('The demo should sell the canon: business code speaks domain language, and ORM resources stay inside the persistence layer.')
            ->withHighlights(['repository contract', 'domain model', 'DomainMappable', '#[SatisfiesRepositoryContract]', 'fetchOne()'])
            ->withLearnMoreLabel('See the canonical flow →')
            ->withDeepDiveLabel('Where resource reads still belong →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow.html.twig', [
                'principles' => [
                    'Handlers should ask for repository contracts, not ORM implementations.',
                    'The default read path should return domain/business models when the resource maps to a domain type.',
                    'save(domainModel) and update(domainModel) are the happy path; raw resource reads are an explicit infrastructure escape hatch.',
                ],
                'lanes' => [
                    [
                        'badge' => 'Best practice',
                        'title' => 'Application / domain path',
                        'tone' => 'good',
                        'summary' => 'Contract repository returns MachineCredential domain objects with business behavior intact.',
                        'chips' => ['MachineCredentialRepositoryInterface', 'MachineCredential', 'save(domain)', 'update(domain)'],
                    ],
                    [
                        'badge' => 'Infrastructure-only',
                        'title' => 'Resource path',
                        'tone' => 'warning',
                        'summary' => 'Resource reads still exist, but only when you explicitly need raw persistence shape for low-level mutation.',
                        'chips' => ['MachineCredentialResource', 'fetchOneAsResource()', 'fetchAllAsResource()', 'mapping only'],
                    ],
                ],
                'steps' => [
                    [
                        'name' => 'Read',
                        'flow' => 'Handler -> repository contract -> fetchOne()/findById() -> domain model',
                        'why' => 'Business code gets behavior and invariants, not ORM metadata.',
                    ],
                    [
                        'name' => 'Mutate',
                        'flow' => 'Domain object methods change state, e.g. revoke() or recordUsage()',
                        'why' => 'The business rule lives on the business object, not in a persistence DTO.',
                    ],
                    [
                        'name' => 'Persist',
                        'flow' => 'Repository implementation converts domain -> resource via fromDomain()',
                        'why' => 'Storage mapping stays in the persistence layer where it belongs.',
                    ],
                    [
                        'name' => 'Escape hatch',
                        'flow' => 'Use fetchOneAsResource() only when you intentionally need the raw resource shape',
                        'why' => 'The low-level path exists, but the framework does not pretend it is the canonical one.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow-rules.html.twig', [
                'rules' => [
                    'Resource-level CRUD is a capability, not the headline practice that the demo should teach first.',
                    'If a screen is demonstrating application architecture, show repository contracts and domain models, not direct resource mutation.',
                    'Use fetchOneAsResource() and fetchAllAsResource() only when the persistence shape is the actual concern of the workflow.',
                    'If a feature is mostly about domain behavior, the source tabs should foreground the domain model and contract before the ORM resource.',
                ],
            ])
            ->withSourceCode([
                'Repository Contract' => $this->sourceCodeReader->readClassSource(MachineCredentialRepositoryInterface::class),
                'Domain Model' => $this->sourceCodeReader->readClassSource(MachineCredential::class),
                'ORM Repository Implementation' => $this->sourceCodeReader->readClassSource(MachineCredentialRepository::class),
                'Resource Model' => $this->sourceCodeReader->readClassSource(MachineCredentialResource::class),
                'SelectQuery Read Split' => $this->sourceCodeReader->readClassSource(SelectQuery::class),
            ])
            ->withExplanation($explanation);
    }
}
