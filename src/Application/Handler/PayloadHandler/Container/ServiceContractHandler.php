<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Container\ServiceContractPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ServiceContractPayload::class, resource: DemoFeatureResource::class)]
final class ServiceContractHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ServiceContractPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'di',
            slug: 'contracts',
            entryLine: 'Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.',
            learnMoreLabel: 'See contract attributes →',
            deepDiveLabel: 'How contract resolution works →',
            relatedSlugs: [],
            fallbackTitle: 'Service Contracts',
            fallbackSummary: 'Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.',
            fallbackHighlights: ['#[SatisfiesServiceContract]', 'module-owned capability', 'closed-world factory', 'deterministic binding'],
            explanation: $this->explanationProvider->getExplanation('di', 'contracts') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Interface Binding',
                'title' => 'Handlers depend on contracts',
                'summary' => 'The container resolves a contract at boot from explicit ownership and implementation metadata, not from hidden container lookups.',
                'codeSnippet' => "// Contract owned by the module:\ninterface MailerInterface { public function send(Mail \$mail): void; }\n\n// Explicit implementation:\n#[SatisfiesServiceContract(of: MailerInterface::class)]\nfinal class SmtpMailer implements MailerInterface { ... }\n\n// Handler only knows the contract:\n#[InjectAsReadonly]\nprotected MailerInterface \$mailer;",
                'note' => 'The goal is not unlimited runtime swapping. The goal is a reviewable, deterministic binding graph.',
            ]);
    }
}
