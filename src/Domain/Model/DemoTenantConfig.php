<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

/**
 * Value object representing per-tenant branding and configuration.
 * Three demo tenants ship with the package — no database needed.
 */
final class DemoTenantConfig
{
    private string $tenantId;        // 'acme', 'globex', 'initech'
    private string $displayName;     // 'Acme Corp'
    private string $primaryColor;    // '#1e40af'
    private string $fontFamily;      // 'Georgia, serif'
    private string $logoPath;        // '@project-static-Demo/tenants/acme-logo.svg'
    private string $currencyCode;    // 'USD'
    private string $ratingStyle;     // 'stars' | 'numeric' | 'stars-half'
    private array $featureFlags;     // ['reviews_enabled' => true, 'wishlist_enabled' => false]
    private array $supportedLocales; // ['en', 'de', 'uk']
    private string $defaultLocale;   // 'en'

    public function __construct(
        string $tenantId,
        string $displayName,
        string $primaryColor,
        string $fontFamily,
        string $logoPath,
        string $currencyCode,
        string $ratingStyle,
        array $featureFlags,
        array $supportedLocales,
        string $defaultLocale,
    ) {
        $this->tenantId = $tenantId;
        $this->displayName = $displayName;
        $this->primaryColor = $primaryColor;
        $this->fontFamily = $fontFamily;
        $this->logoPath = $logoPath;
        $this->currencyCode = $currencyCode;
        $this->ratingStyle = $ratingStyle;
        $this->featureFlags = $featureFlags;
        $this->supportedLocales = $supportedLocales;
        $this->defaultLocale = $defaultLocale;
    }

    public function getTenantId(): string { return $this->tenantId; }
    public function setTenantId(string $tenantId): void { $this->tenantId = $tenantId; }

    public function getDisplayName(): string { return $this->displayName; }
    public function setDisplayName(string $displayName): void { $this->displayName = $displayName; }

    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function setPrimaryColor(string $primaryColor): void { $this->primaryColor = $primaryColor; }

    public function getFontFamily(): string { return $this->fontFamily; }
    public function setFontFamily(string $fontFamily): void { $this->fontFamily = $fontFamily; }

    public function getLogoPath(): string { return $this->logoPath; }
    public function setLogoPath(string $logoPath): void { $this->logoPath = $logoPath; }

    public function getCurrencyCode(): string { return $this->currencyCode; }
    public function setCurrencyCode(string $currencyCode): void { $this->currencyCode = $currencyCode; }

    public function getRatingStyle(): string { return $this->ratingStyle; }
    public function setRatingStyle(string $ratingStyle): void { $this->ratingStyle = $ratingStyle; }

    public function getFeatureFlags(): array { return $this->featureFlags; }
    public function setFeatureFlags(array $featureFlags): void { $this->featureFlags = $featureFlags; }

    public function getSupportedLocales(): array { return $this->supportedLocales; }
    public function setSupportedLocales(array $supportedLocales): void { $this->supportedLocales = $supportedLocales; }

    public function getDefaultLocale(): string { return $this->defaultLocale; }
    public function setDefaultLocale(string $defaultLocale): void { $this->defaultLocale = $defaultLocale; }
}
