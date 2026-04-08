<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Environment;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Payload\Request\Async\SseStreamPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SseStreamPayload::class, resource: DemoFeatureResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(SseStreamPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $authorizationRequired = true;
        $returnTo = '/demo/events/sse';
        $explanation = $this->explanationProvider->getExplanation('events', 'sse') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Client JS' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Static/js/sse-demo.js'),
        ];

        return $resource
            ->pageTitle('SSE Stream — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'events',
                'currentSlug' => 'sse',
                'infoWhat' => $explanation['what'] ?? 'Real-time server push without WebSockets — a persistent HTTP connection that streams events.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('events')
            ->withSlug('sse')
            ->withTitle('SSE Stream')
            ->withSummary('Real-time server push without WebSockets — connect once and let the backend stream named events into the page.')
            ->withEntryLine('This demo now receives real backend-generated SSE messages over one long-lived HTTP connection, not client-side simulated updates.')
            ->withHighlights(['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'])
            ->withLearnMoreLabel('See the SSE handler →')
            ->withDeepDiveLabel('SSE connection lifecycle →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/sse-stream.html.twig', [
                'authorizationRequired' => $authorizationRequired,
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
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
