<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Environment;

final class DemoAuthMode
{
    public static function isLocalLoginEnabled(): bool
    {
        $env = strtolower(trim((string) (Environment::getEnvValue('APP_ENV', '') ?? '')));
        $debug = filter_var((string) (Environment::getEnvValue('APP_DEBUG', '0') ?? '0'), FILTER_VALIDATE_BOOLEAN);

        return $env === 'dev' && $debug;
    }

    public static function actionLabel(): string
    {
        return self::isLocalLoginEnabled() ? 'Sign in locally' : 'Sign in with Google';
    }

    public static function signedInLabel(): string
    {
        return self::isLocalLoginEnabled() ? 'Signed in locally as' : 'Authorized as';
    }

    public static function providerLabel(): string
    {
        return self::isLocalLoginEnabled() ? 'Local demo account' : 'Google Account';
    }

    public static function signInTitle(): string
    {
        return self::isLocalLoginEnabled()
            ? 'Sign in locally to unlock advanced demos'
            : 'Sign in with Google to unlock advanced demos';
    }

    public static function configurationMessage(): string
    {
        return self::isLocalLoginEnabled()
            ? 'Local demo login is enabled in APP_ENV=dev with APP_DEBUG=1, so the demo skips the Google OAuth round-trip.'
            : 'Google OAuth is not configured. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI before signing in.';
    }
}
