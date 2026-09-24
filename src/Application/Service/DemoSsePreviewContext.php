<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Environment;
use Semitexa\Demo\Auth\GooglePrincipal;

/** Authentication and links for the shared, real server-event preview. */
#[AsService]
final class DemoSsePreviewContext
{
    /**
     * @return array<string, mixed>
     */
    public function forPage(string $returnTo): array
    {
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
            'sseEndpoint' => Environment::getEnvValue('SSE_ENDPOINT', '/__semitexa_kiss'),
            'authRequiredMessage' => DemoAuthMode::isLocalLoginEnabled()
                ? 'Local sign-in is required to open the long-lived SSE stream used by this demo.'
                : 'Authorization is required to open the long-lived SSE stream used by this demo.',
        ];
    }
}
