<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Auth\MachineAuthPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: MachineAuthPayload::class, resource: DemoFeatureResource::class)]
final class MachineAuthHandler implements TypedHandlerInterface
{
    private const DEMO_TOKEN = 'demo-client-id:demo-secret-key-abc123';

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(MachineAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'auth',
            slug: 'machine',
            entryLine: 'Documented bearer-token recipe. This page is reference material — the token shown below is a placeholder and is NOT validated by this handler. Real verification belongs to the semitexa/api package.',
            learnMoreLabel: 'See the Bearer token format →',
            deepDiveLabel: 'Machine auth verification pipeline →',
            relatedSlugs: [],
            fallbackTitle: 'Machine Auth',
            fallbackSummary: 'Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.',
            fallbackHighlights: ['MachineAuthHandler', 'Bearer {id}:{secret}', 'MachineCredential', 'scopes', 'revocation'],
            explanation: $this->explanationProvider->getExplanation('auth', 'machine') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/machine-auth.html.twig', [
                'curlExample' => "# Reference format only — this demo page does not validate tokens.\n# Real verification lives in the semitexa/api package.\ncurl -H \"Authorization: Bearer " . self::DEMO_TOKEN . "\" \\\n  https://your-app.com/api/products",
                'rows' => [
                    ['label' => 'Status on this page', 'value' => 'Reference only — tokens are not verified here'],
                    ['label' => 'Real verifier', 'value' => 'semitexa/api machine-auth pipeline'],
                    ['label' => 'Format', 'value' => 'Bearer {client_id}:{secret}'],
                    ['label' => 'Handler priority', 'value' => '50 (runs before session auth)'],
                    ['label' => 'Scopes', 'value' => 'Stored on MachineCredential, checked per route'],
                    ['label' => 'Revocation', 'value' => 'Set revoked_at for immediate effect'],
                    ['label' => 'Audit', 'value' => 'Credential ID + timestamp logged per request'],
                ],
            ]);
    }
}
