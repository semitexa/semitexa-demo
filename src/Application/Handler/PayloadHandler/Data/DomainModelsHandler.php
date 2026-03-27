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
use Semitexa\Demo\Application\Payload\Request\Data\DomainModelsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Query\SelectQuery;

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
            ->withSummary('Semitexa separates persistence resources from business models. Resources map tables; domain models carry behavior and invariants.')
            ->withEntryLine('Resource models exist for persistence. Domain models exist for business behavior. Repositories bridge them instead of collapsing them into one class.')
            ->withHighlights(['DomainMappable', 'fromDomain()', 'toDomain()', '#[SatisfiesRepositoryContract]', 'fetchOneAsResource()'])
            ->withLearnMoreLabel('See both layers side by side →')
            ->withDeepDiveLabel('How repositories bridge the layers →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/domain-models-showcase.html.twig', [
                'painPoints' => [
                    'When one class is both ORM mapping and business model, storage concerns leak into business code immediately.',
                    'That usually pushes handlers and services to pass persistence resources around as if they were the real business objects.',
                    'Semitexa keeps the separation explicit: repositories can expose domain models while still using resource models for persistence.',
                ],
                'layers' => [
                    [
                        'badge' => 'Persistence layer',
                        'title' => 'Resource model',
                        'tone' => 'base',
                        'summary' => 'Maps columns, indexes, table name, timestamps, and conversion rules.',
                        'chips' => ['#[FromTable]', '#[Column]', '#[Index]', 'toDomain()', 'fromDomain()'],
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
                        'resource' => 'ORM attributes, SQL-facing concerns, timestamps, index hints.',
                        'domain' => 'Only domain language and behavior, no storage metadata.',
                    ],
                    [
                        'concern' => 'Repository default reads',
                        'resource' => 'Available via fetchOneAsResource()/fetchAllAsResource() when mutation needs raw persistence shape.',
                        'domain' => 'Returned by fetchOne()/fetchAll() when the resource implements DomainMappable.',
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
                    'Resource models are still first-class, but mostly for mapping, hydration, and persistence-specific operations.',
                    'When a workflow truly needs the raw resource shape for mutation, use fetchOneAsResource() or fetchAllAsResource() deliberately.',
                    'If a resource declares mapTo and implements DomainMappable, the default repository read path already returns domain objects.',
                ],
            ])
            ->withSourceCode([
                'Domain Model' => $this->sourceCodeReader->readClassSource(MachineCredential::class),
                'Resource Model' => $this->sourceCodeReader->readClassSource(MachineCredentialResource::class),
                'Repository Contract' => $this->sourceCodeReader->readClassSource(MachineCredentialRepositoryInterface::class),
                'ORM Repository Implementation' => $this->sourceCodeReader->readClassSource(MachineCredentialRepository::class),
                'SelectQuery Read Split' => $this->sourceCodeReader->readClassSource(SelectQuery::class),
            ])
            ->withExplanation($explanation);
    }
}
