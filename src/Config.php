<?php

declare(strict_types=1);

namespace FileJet;

class Config
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $storageId;
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $signatureSecret;
    /** @var bool */
    private $autoMode;

    public function __construct(string $apiKey, string $storageId, string $baseUrl = null, string $signatureSecret = null, bool $autoMode = true)
    {
        $this->apiKey = $apiKey;
        $this->storageId = $storageId;
        $this->baseUrl = $baseUrl;
        $this->signatureSecret = $signatureSecret;
        $this->autoMode = $autoMode;
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
        return "https://{$this->storageId}.5gcdn.net";
    }
}
