<?php

declare(strict_types=1);

namespace FileJet;

class Config
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $storageId;

    public function __construct(string $apiKey, string $storageId)
    {
        $this->apiKey = $apiKey;
        $this->storageId = $storageId;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getStorageId(): string
    {
        return $this->storageId;
    }

    public function getStorageManagerUrl(): string
    {
        return 'https://api.filejet.io';
    }

    public function getPublicUrl(): string
    {
        return 'https://res.filejet.io';
    }
}
