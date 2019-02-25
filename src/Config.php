<?php

declare(strict_types=1);

namespace FileJet;

class Config
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $storageId;
    /** @var bool */
    private $autoMode;

    public function __construct(string $apiKey, string $storageId, bool $autoMode = true)
    {
        $this->apiKey = $apiKey;
        $this->storageId = $storageId;
        $this->autoMode = $autoMode;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
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
