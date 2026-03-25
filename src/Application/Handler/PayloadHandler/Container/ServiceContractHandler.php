<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\ServiceContractPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ServiceContractPayload::class, resource: DemoFeatureResource::class)]
final class ServiceContractHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ServiceContractPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Service contracts let handlers depend on <strong>interfaces</strong>, not implementations. '
            . 'The container resolves the binding at boot using the <code>#[SatisfiesServiceContract]</code> attribute.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "// Interface — what you inject:\ninterface MailerInterface { public function send(Mail \$mail): void; }\n\n"
                . "// Implementation — what gets resolved:\n#[SatisfiesServiceContract(of: MailerInterface::class)]\nfinal class SmtpMailer implements MailerInterface { ... }\n\n"
                . "// Handler — only knows the interface:\n#[InjectAsReadonly]\nprotected MailerInterface \$mailer;"
            )
            . '</pre>'
            . '<p class="note">Swap the implementation (e.g. for testing or a different provider) '
            . 'by pointing <code>#[SatisfiesServiceContract]</code> at a different class — '
            . 'no handler code changes required.</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('di', 'contracts') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Service Contracts — Semitexa Demo')
            ->withSection('di')
            ->withSlug('contracts')
            ->withTitle('Service Contracts')
            ->withSummary('Depend on interfaces, not implementations — swap adapters without touching handlers.')
            ->withEntryLine('Depend on interfaces, not implementations — swap adapters without touching handlers.')
            ->withHighlights(['#[SatisfiesServiceContract]', '#[SatisfiesRepositoryContract]', 'interface binding', 'swap implementations'])
            ->withLearnMoreLabel('See contract attributes →')
            ->withDeepDiveLabel('How contract resolution works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
