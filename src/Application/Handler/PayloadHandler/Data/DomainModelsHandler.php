<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Api\Application\Db\MySQL\Model\MachineCredentialMapper;
use Semitexa\Api\Application\Db\MySQL\Model\MachineCredentialTableModel;
use Semitexa\Api\Application\Db\MySQL\Repository\MachineCredentialRepository;
use Semitexa\Api\Domain\Contract\MachineCredentialRepositoryInterface;
use Semitexa\Api\Domain\Model\MachineCredential;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Data\DomainModelsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
#[AsPayloadHandler(payload: DomainModelsPayload::class, resource: DemoFeatureResource::class)]
final class DomainModelsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DomainModelsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('data', 'domain-models') ?? [];

        return $resource
            ->pageTitle('Domain-Level Models — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'domain-models',
                'infoWhat' => $explanation['what'] ?? 'Resources map storage; domain models carry business meaning and behavior.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('domain-models')
            ->withTitle('Domain-Level Models')
            ->withSummary('Semitexa separates persistence table models from business models. Table models map storage; domain models carry behavior and invariants.')
            ->withEntryLine('Table models exist for persistence. Domain models exist for business behavior. Explicit mappers and repositories bridge them instead of collapsing them into one class.')
            ->withHighlights(['TableModel', 'mapper', '#[AsMapper]', '#[SatisfiesRepositoryContract]', 'DomainRepository'])
            ->withLearnMoreLabel('See both layers side by side →')
            ->withDeepDiveLabel('How repositories bridge the layers →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/domain-models-showcase.html.twig', [
                'painPoints' => [
                    'When one class is both ORM mapping and business model, storage concerns leak into business code immediately.',
                    'That usually pushes handlers and services to pass persistence resources around as if they were the real business objects.',
                    'Semitexa keeps the separation explicit: repositories can expose domain models while still using TableModel + mapper for persistence.',
                ],
                'layers' => [
                    [
                        'badge' => 'Persistence layer',
                        'title' => 'TableModel + mapper',
                        'tone' => 'base',
                        'summary' => 'Maps columns, table name, and explicit conversion rules into the domain layer.',
                        'chips' => ['#[FromTable]', '#[Column]', '#[AsMapper]', 'explicit mapping'],
                    ],
                    [
                        'badge' => 'Business layer',
                        'title' => 'Domain model',
                        'tone' => 'good',
                        'summary' => 'Holds business semantics like revoke(), rotateSecretHash(), recordUsage(), hasScope().',
                        'chips' => ['behavior', 'invariants', 'business language', 'no ORM attrs'],
                    ],
                ],
                'rows' => [
                    [
                        'concern' => 'Primary responsibility',
                        'resource' => 'Describe how one table row is stored and rehydrated.',
                        'domain' => 'Describe what the business object means and how it behaves.',
                    ],
                    [
                        'concern' => 'Allowed dependencies',
                        'resource' => 'ORM attributes, SQL-facing concerns, mapping metadata.',
                        'domain' => 'Only domain language and behavior, no storage metadata.',
                    ],
                    [
                        'concern' => 'Repository default reads',
                        'resource' => 'Built as TableModel and then mapped explicitly inside the repository implementation.',
                        'domain' => 'Returned by the repository contract through the canonical ORM path.',
                    ],
                    [
                        'concern' => 'Mutation path',
                        'resource' => 'Useful for low-level persistence mutation or infrastructure-only workflows.',
                        'domain' => 'Preferred for business flows through contract repositories like MachineCredentialRepositoryInterface.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/domain-models-rules.html.twig', [
                'rules' => [
                    'Repository contracts at the application boundary should usually speak in domain models, not ORM resources.',
                    'TableModel and mapper classes are still first-class, but mostly for mapping, hydration, and persistence-specific operations.',
                    'When a workflow truly needs raw persistence shape, keep that path explicit and infrastructure-scoped.',
                    'The canonical repository read path should remain TableModel -> mapper -> domain model.',
                ],
            ])
            ->withSourceCode([
                'Domain Model' => $this->sourceCodeReader->readClassSource(MachineCredential::class),
                'TableModel' => $this->sourceCodeReader->readClassSource(MachineCredentialTableModel::class),
                'Mapper' => $this->sourceCodeReader->readClassSource(MachineCredentialMapper::class),
                'Repository Contract' => $this->sourceCodeReader->readClassSource(MachineCredentialRepositoryInterface::class),
                'ORM Repository Implementation' => $this->sourceCodeReader->readClassSource(MachineCredentialRepository::class),
            ])
            ->withExplanation($explanation);
    }
}
