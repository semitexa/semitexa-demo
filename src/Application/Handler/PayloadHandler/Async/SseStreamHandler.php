<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Environment;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Async\SseStreamPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SseStreamPayload::class, resource: DemoFeatureResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SseStreamPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'events',
            slug: 'sse',
            entryLine: 'This demo now receives real backend-generated SSE messages over one long-lived HTTP connection, not client-side simulated updates.',
            learnMoreLabel: 'See the SSE handler →',
            deepDiveLabel: 'SSE connection lifecycle →',
            relatedSlugs: [],
            fallbackTitle: 'SSE Stream',
            fallbackSummary: 'Real-time server push without WebSockets — connect once and receive real backend events over plain HTTP.',
            fallbackHighlights: ['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'],
            explanation: $this->explanationProvider->getExplanation('events', 'sse') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Client JS' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Static/js/sse-demo.js'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/sse-stream.html.twig', $this->buildPreviewData());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPreviewData(): array
    {
        $returnTo = '/demo/events/sse';
        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;

        return [
            'authorizationRequired' => true,
            'isAuthenticated' => !$auth->isGuest(),
            'displayName' => $googleUser?->getDisplayName() ?? ($user?->getId() ?? null),
            'email' => $googleUser?->getEmail(),
            'pictureUrl' => $googleUser?->getPictureUrl(),
            'hostedDomain' => $googleUser?->getHostedDomain(),
            'emailVerified' => $googleUser?->emailVerified ?? false,
            'authPageUrl' => '/demo/auth/google?return_to=' . rawurlencode($returnTo),
            'startUrl' => '/demo/auth/google/start?return_to=' . rawurlencode($returnTo),
            'logoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode($returnTo),
            'authActionLabel' => DemoAuthMode::actionLabel(),
            'authSignedInLabel' => DemoAuthMode::signedInLabel(),
            'sseEndpoint' => Environment::getEnvValue('SSE_ENDPOINT', '/sse'),
            'authRequiredMessage' => DemoAuthMode::isLocalLoginEnabled()
                ? 'Local sign-in is required to open the long-lived SSE stream used by this demo.'
                : 'Authorization is required to open the long-lived SSE stream used by this demo.',
        ];
    }
}
