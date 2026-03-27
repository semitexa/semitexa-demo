<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\ServiceContractPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ServiceContractPayload::class, resource: DemoFeatureResource::class)]
final class ServiceContractHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ServiceContractPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('di', 'contracts') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Service Contracts — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'contracts',
                'infoWhat' => $explanation['what'] ?? 'Service contracts bind interfaces to implementations at boot, keeping handlers decoupled.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('contracts')
            ->withTitle('Service Contracts')
            ->withSummary('Depend on interfaces, not implementations — swap adapters without touching handlers.')
            ->withEntryLine('Depend on interfaces, not implementations — swap adapters without touching handlers.')
            ->withHighlights(['#[SatisfiesServiceContract]', '#[SatisfiesRepositoryContract]', 'interface binding', 'swap implementations'])
            ->withLearnMoreLabel('See contract attributes →')
            ->withDeepDiveLabel('How contract resolution works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Interface Binding',
                'title' => 'Handlers depend on contracts',
                'summary' => 'The container resolves an implementation at boot via a service contract attribute.',
                'codeSnippet' => "// Interface — what you inject:\ninterface MailerInterface { public function send(Mail \$mail): void; }\n\n// Implementation — what gets resolved:\n#[SatisfiesServiceContract(of: MailerInterface::class)]\nfinal class SmtpMailer implements MailerInterface { ... }\n\n// Handler — only knows the interface:\n#[InjectAsReadonly]\nprotected MailerInterface \$mailer;",
                'note' => 'Swap implementations for testing or providers without changing handler code.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
