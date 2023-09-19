<?php

declare(strict_types=1);

namespace FileJet;

class Config
{
    /** @var string */
    private string $apiKey;
    /** @var string */
    private string $storageId;
    /** @var string|null */
    private ?string $signatureSecret = null;
    /** @var bool */
    private bool $autoMode = true;
    /** @var string|null */
    private ?string $baseUrl = null;
    /** @var string|null */
    private ?string $customDomain = null;

    public function __construct(
        string $apiKey,
        string $storageId,
        string $signatureSecret = null,
        bool $autoMode = true,
        string $baseUrl = null,
        string $customDomain = null
    ) {
        $this->apiKey = $apiKey;
        $this->storageId = $storageId;
        $this->signatureSecret = $signatureSecret;
        $this->autoMode = $autoMode;
        $this->baseUrl = $baseUrl;
        $this->customDomain = $customDomain;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function getSignatureSecret(): ?string
    {
        return $this->signatureSecret;
    }

    public function isAutoMode(): bool
    {
        return $this->autoMode;
    }

    public function getStorageManagerUrl(): string
    {
        return "https://api.filejet.io/{$this->storageId}";
    }

    public function getPublicUrl(): string
    {
        if ($this->customDomain) return "https://{$this->customDomain}";

        return "https://{$this->storageId}.5gcdn.net";
    }

    public function getCustomDomain(): ?string
    {
        return $this->customDomain;
    }
}
