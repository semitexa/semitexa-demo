<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Environment;

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

        return $this->postForm(self::TOKEN_URL, [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserInfo(string $accessToken): array
    {
        return $this->getJson(self::USERINFO_URL, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ]);
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
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    private function getJson(string $url, array $headers = []): array
    {
        $response = $this->request('GET', $url, null, $headers);
        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Google OAuth endpoint returned invalid JSON.');
        }

        return $decoded;
    }

    /**
     * @param array<string, string> $form
     * @return array<string, mixed>
     */
    private function postForm(string $url, array $form): array
    {
        $response = $this->request('POST', $url, http_build_query($form, '', '&', PHP_QUERY_RFC3986), [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ]);
        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Google OAuth endpoint returned invalid JSON.');
        }

        return $decoded;
    }

    /**
     * @param array<int, string> $headers
     */
    private function request(string $method, string $url, ?string $body = null, array $headers = []): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                throw new \RuntimeException('Unable to initialize cURL.');
            }

            $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
            ];

            if ($body !== null) {
                $options[CURLOPT_POSTFIELDS] = $body;
            }

            curl_setopt_array($ch, $options);
            $raw = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($raw === false) {
                throw new \RuntimeException('Google OAuth request failed: ' . ($error !== '' ? $error : 'unknown error'));
            }

            if ($status < 200 || $status >= 300) {
                throw new \RuntimeException('Google OAuth request failed with HTTP ' . $status . '.');
            }

            return $raw;
        }

        $streamContext = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $streamContext);
        if ($raw === false) {
            throw new \RuntimeException('Google OAuth request failed.');
        }

        return $raw;
    }
}
