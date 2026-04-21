<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Api\Application\Db\MySQL\Model\MachineCredentialMapper;
use Semitexa\Api\Application\Db\MySQL\Model\MachineCredentialResourceModel;
use Semitexa\Api\Application\Db\MySQL\Repository\MachineCredentialRepository;
use Semitexa\Api\Domain\Contract\MachineCredentialRepositoryInterface;
use Semitexa\Api\Domain\Model\MachineCredential;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\RepositoryWorkflowPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: RepositoryWorkflowPayload::class, resource: DemoFeatureResource::class)]
final class RepositoryWorkflowHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(RepositoryWorkflowPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'repository-workflow',
            entryLine: 'Business code should work with domain models, while ResourceModel and mapper logic stay inside the persistence layer.',
            learnMoreLabel: 'See the canonical flow →',
            deepDiveLabel: 'Where resource reads still belong →',
            relatedSlugs: [],
            fallbackTitle: 'Repository Workflow',
            fallbackSummary: 'The canonical Semitexa path: handlers depend on repository contracts, repositories return domain models, and persistence resources stay behind the boundary.',
            fallbackHighlights: ['repository contract', 'domain model', 'ResourceModel', 'mapper', '#[SatisfiesRepositoryContract]'],
            explanation: $this->explanationProvider->getExplanation('data', 'repository-workflow') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Repository Contract' => $this->sourceCodeReader->readClassSource(MachineCredentialRepositoryInterface::class),
                'Domain Model' => $this->sourceCodeReader->readClassSource(MachineCredential::class),
                'ORM Repository Implementation' => $this->sourceCodeReader->readClassSource(MachineCredentialRepository::class),
                'ResourceModel' => $this->sourceCodeReader->readClassSource(MachineCredentialResourceModel::class),
                'Mapper' => $this->sourceCodeReader->readClassSource(MachineCredentialMapper::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow.html.twig', [
                'principles' => [
                    'Handlers should ask for repository contracts, not ORM implementations.',
                    'The default read path should return domain/business models through the explicit ResourceModel -> mapper -> domain pipeline.',
                    'insert(domainModel) and update(domainModel) are the happy path; low-level ResourceModel reads are an explicit infrastructure concern.',
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
                        'summary' => 'ResourceModel and mapper classes still matter, but their job is to describe storage and convert data between persistence and domain.',
                        'chips' => ['MachineCredentialResourceModel', 'MachineCredentialMapper', 'DomainRepository', 'mapping only'],
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
                        'flow' => 'Repository implementation converts domain -> resource model via explicit mapper and persists through the ORM core',
                        'why' => 'Storage mapping stays in the persistence layer where it belongs.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/repository-workflow-rules.html.twig', [
                'rules' => [
                    'ResourceModel-level persistence is a capability, not the headline practice that the demo should teach first.',
                    'If a screen is demonstrating application architecture, show repository contracts and domain models, not direct ResourceModel mutation.',
                    'Mapper and ResourceModel code should stay clearly visible, but behind the repository boundary.',
                    'If a feature is mostly about domain behavior, the source tabs should foreground the domain model and contract before the persistence model.',
                ],
            ]);
    }
}
