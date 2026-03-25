<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\MachineAuthPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: MachineAuthPayload::class, resource: DemoFeatureResource::class)]
final class MachineAuthHandler implements TypedHandlerInterface
{
    private const DEMO_TOKEN = 'demo-client-id:demo-secret-key-abc123';

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(MachineAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Machine auth uses a <strong>Bearer token</strong> with the format <code>{id}:{secret}</code>.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars("# Try this in your terminal:\ncurl -H \"Authorization: Bearer " . self::DEMO_TOKEN . "\" \\\n  https://your-app.com/api/products")
            . '</pre>'
            . '<table class="data-table">'
            . '<thead><tr><th>Property</th><th>Value</th></tr></thead>'
            . '<tbody>'
            . '<tr><td>Format</td><td><code>Bearer {client_id}:{secret}</code></td></tr>'
            . '<tr><td>Handler priority</td><td>50 (runs before session auth)</td></tr>'
            . '<tr><td>Scopes</td><td>Stored on <code>MachineCredential</code>, checked per route</td></tr>'
            . '<tr><td>Revocation</td><td>Set <code>revoked_at</code> — takes effect immediately</td></tr>'
            . '<tr><td>Audit</td><td>Every request logged with credential ID + timestamp</td></tr>'
            . '</tbody></table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('auth', 'machine') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Machine Auth — Semitexa Demo')
            ->withSection('auth')
            ->withSlug('machine')
            ->withTitle('Machine Auth')
            ->withSummary('Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.')
            ->withEntryLine('Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.')
            ->withHighlights(['MachineAuthHandler', 'Bearer {id}:{secret}', 'MachineCredential', 'scopes', 'revocation'])
            ->withLearnMoreLabel('See the Bearer token format →')
            ->withDeepDiveLabel('Machine auth verification pipeline →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
