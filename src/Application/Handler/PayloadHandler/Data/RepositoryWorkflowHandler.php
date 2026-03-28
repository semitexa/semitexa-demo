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
use Semitexa\Demo\Application\Payload\Request\Data\RepositoryWorkflowPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
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
            ->withSummary('The canonical Semitexa path: handlers depend on repository contracts, repositories return domain models, and persistence table models stay behind the boundary.')
            ->withEntryLine('Business code should work with domain models, while TableModel and mapper logic stay inside the persistence layer.')
            ->withHighlights(['repository contract', 'domain model', 'TableModel', 'mapper', '#[SatisfiesRepositoryContract]'])
            ->withLearnMoreLabel('See the canonical flow →')
            ->withDeepDiveLabel('Where resource reads still belong →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow.html.twig', [
                'principles' => [
                    'Handlers should ask for repository contracts, not ORM implementations.',
                    'The default read path should return domain/business models through the explicit TableModel -> mapper -> domain pipeline.',
                    'insert(domainModel) and update(domainModel) are the happy path; low-level TableModel reads are an explicit infrastructure concern.',
                ],
                'lanes' => [
                    [
                        'badge' => 'Best practice',
                        'title' => 'Application / domain path',
                        'tone' => 'good',
                        'summary' => 'Contract repository returns MachineCredential domain objects with business behavior intact.',
                        'chips' => ['MachineCredentialRepositoryInterface', 'MachineCredential', 'insert(domain)', 'update(domain)'],
                    ],
                    [
                        'badge' => 'Infrastructure-only',
                        'title' => 'Persistence path',
                        'tone' => 'warning',
                        'summary' => 'TableModel and mapper classes still matter, but their job is to describe storage and convert data between persistence and domain.',
                        'chips' => ['MachineCredentialTableModel', 'MachineCredentialMapper', 'DomainRepository', 'mapping only'],
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
                        'flow' => 'Repository implementation converts domain -> table model via explicit mapper and persists through the ORM core',
                        'why' => 'Storage mapping stays in the persistence layer where it belongs.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow-rules.html.twig', [
                'rules' => [
                    'TableModel-level persistence is a capability, not the headline practice that the demo should teach first.',
                    'If a screen is demonstrating application architecture, show repository contracts and domain models, not direct TableModel mutation.',
                    'Mapper and TableModel code should stay clearly visible, but behind the repository boundary.',
                    'If a feature is mostly about domain behavior, the source tabs should foreground the domain model and contract before the persistence model.',
                ],
            ])
            ->withSourceCode([
                'Repository Contract' => $this->sourceCodeReader->readClassSource(MachineCredentialRepositoryInterface::class),
                'Domain Model' => $this->sourceCodeReader->readClassSource(MachineCredential::class),
                'ORM Repository Implementation' => $this->sourceCodeReader->readClassSource(MachineCredentialRepository::class),
                'TableModel' => $this->sourceCodeReader->readClassSource(MachineCredentialTableModel::class),
                'Mapper' => $this->sourceCodeReader->readClassSource(MachineCredentialMapper::class),
            ])
            ->withExplanation($explanation);
    }
}
