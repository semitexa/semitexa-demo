<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Environment;
use Semitexa\Core\Http\Client\HttpCallOptions;
use Semitexa\Core\Http\Client\HttpClientInterface;
use Semitexa\Core\Http\Client\HttpResponse;
use Semitexa\Core\Http\Exception\HttpTransportException;

#[AsService]
class GoogleOAuthClient
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $redirectUri = null;
    protected ?string $allowedDomain = null;

    #[InjectAsReadonly]
    protected HttpClientInterface $http;

    public function isConfigured(): bool
    {
        return $this->getClientId() !== '' && $this->getClientSecret() !== '' && $this->getRedirectUri() !== '';
    }

    public function getClientId(): string
    {
        return trim((string) ($this->clientId ?? Environment::getEnvValue('GOOGLE_CLIENT_ID', '')));
    }

    public function getClientSecret(): string
    {
        return trim((string) ($this->clientSecret ?? Environment::getEnvValue('GOOGLE_CLIENT_SECRET', '')));
    }

    public function getRedirectUri(): string
    {
        return trim((string) ($this->redirectUri ?? Environment::getEnvValue('GOOGLE_REDIRECT_URI', '')));
    }

    public function getAllowedDomain(): string
    {
        return trim((string) ($this->allowedDomain ?? Environment::getEnvValue('GOOGLE_ALLOWED_DOMAIN', '')));
    }

    public function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function buildAuthorizationUrl(string $state): string
    {
        $this->assertConfigured();

        $query = http_build_query([
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTH_URL . '?' . $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function exchangeCodeForToken(string $code): array
    {
        $this->assertConfigured();

        return $this->decoded($this->call(fn (HttpCallOptions $options): HttpResponse => $this->http->postForm(self::TOKEN_URL, [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
        ], ['Accept' => 'application/json'], $options)));
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserInfo(string $accessToken): array
    {
        return $this->decoded($this->call(fn (HttpCallOptions $options): HttpResponse => $this->http->get(
            self::USERINFO_URL,
            ['Authorization' => 'Bearer ' . $accessToken, 'Accept' => 'application/json'],
            $options,
        )));
    }

    public function isAllowedDomain(?string $hostedDomain, string $email): bool
    {
        $allowedDomain = $this->getAllowedDomain();
        if ($allowedDomain === '') {
            return true;
        }

        $hostedDomain = trim((string) $hostedDomain);
        if ($hostedDomain !== '' && strcasecmp($hostedDomain, $allowedDomain) === 0) {
            return true;
        }

        $emailDomain = strtolower((string) preg_replace('/^.*@/', '', $email));
        return $emailDomain !== '' && strcasecmp($emailDomain, $allowedDomain) === 0;
    }

    public function sanitizeReturnTo(?string $returnTo, string $default = '/demo/rendering/deferred'): string
    {
        $returnTo = trim((string) $returnTo);
        if ($returnTo === '' || !str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            return $default;
        }

        if ($returnTo === '/demo' || $returnTo === '/demo/') {
            return '/';
        }

        return $returnTo;
    }

    private function assertConfigured(): void
    {
        if ($this->getClientId() === '' || $this->getClientSecret() === '' || $this->getRedirectUri() === '') {
            throw new \RuntimeException(
                'Google OAuth is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI.'
            );
        }
    }

    /**
     * One call to Google, refused unless it answers 2xx. Not retried: the
     * authorization code is single-use, and a resent exchange fails anyway.
     *
     * @param \Closure(HttpCallOptions): HttpResponse $send
     */
    private function call(\Closure $send): HttpResponse
    {
        try {
            $response = $send(new HttpCallOptions(timeout: 15.0, connectTimeout: 5.0));
        } catch (HttpTransportException $e) {
            throw new \RuntimeException('Google OAuth request failed: ' . $e->detail, 0, $e);
        }

        if (!$response->isSuccessful()) {
            throw new \RuntimeException('Google OAuth request failed with HTTP ' . $response->status . '.');
        }

        return $response;
    }

    /** @return array<string, mixed> */
    private function decoded(HttpResponse $response): array
    {
        $decoded = json_decode($response->body, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Google OAuth endpoint returned invalid JSON.');
        }

        return $decoded;
    }
}
