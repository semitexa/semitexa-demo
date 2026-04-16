<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\ServiceContractPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ServiceContractPayload::class, resource: DemoFeatureResource::class)]
final class ServiceContractHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ServiceContractPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'di',
            'contracts',
            'Service Contracts',
            'Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.',
            ['#[SatisfiesServiceContract]', 'module-owned capability', 'closed-world factory', 'deterministic binding'],
        );
        $explanation = $this->explanationProvider->getExplanation('di', 'contracts') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'contracts',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('contracts')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See contract attributes →')
            ->withDeepDiveLabel('How contract resolution works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Interface Binding',
                'title' => 'Handlers depend on contracts',
                'summary' => 'The container resolves a contract at boot from explicit ownership and implementation metadata, not from hidden container lookups.',
                'codeSnippet' => "// Contract owned by the module:\ninterface MailerInterface { public function send(Mail \$mail): void; }\n\n// Explicit implementation:\n#[SatisfiesServiceContract(of: MailerInterface::class)]\nfinal class SmtpMailer implements MailerInterface { ... }\n\n// Handler only knows the contract:\n#[InjectAsReadonly]\nprotected MailerInterface \$mailer;",
                'note' => 'The goal is not unlimited runtime swapping. The goal is a reviewable, deterministic binding graph.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
