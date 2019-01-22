<?php

declare(strict_types=1);

namespace FileJet;

use FileJet\Messages\DownloadInstruction;
use FileJet\Messages\UploadInstruction;
use FileJet\Messages\UploadRequest;

final class FileJet
{
    /** @var HttpClient */
    private $httpClient;
    /** @var Config */
    private $config;

    public function __construct(HttpClient $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    public function getUrl(FileInterface $file): string
    {
        $url = "{$this->config->getPublicUrl()}/{$this->config->getStorageId()}/{$this->normalizeId($file->getIdentifier())}";

        if ($this->config->isAutoMode() && $this->autoIsDisabled($file)) {
            $file = new File($file->getIdentifier(), $this->toAutoMutation($file));
        }

        if ($file->getMutation() !== null) {
            $url = "{$url}/{$file->getMutation()}";
        }

        return $url;
    }

    public function getPrivateUrl(string $fileId, int $expires): DownloadInstruction
    {
        return new DownloadInstruction(
            $this->request('file.privateUrl', ['fileId' => $this->normalizeId($fileId), 'expires' => $expires])
        );
    }

    public function uploadFile(UploadRequest $request): UploadInstruction
    {
        return new UploadInstruction(
            $this->request('file.requestUpload', [
                'contentType' => $request->getContentType(),
                'expires' => $request->getExpires(),
                'access' => $request->getAccess()
            ])
        );
    }

    public function deleteFile(string $fileId): void
    {
        $this->request('file.delete', ['fileId' => $this->normalizeId($fileId)]);
    }

    private function request(string $operation, array $body)
    {
        return $this->httpClient->sendRequest(
            HttpClient::METHOD_POST,
            "{$this->config->getStorageManagerUrl()}/{$this->config->getStorageId()}/$operation",
            [
                'Authorization' => $this->config->getApiKey(),
                'Content-Type' => 'application/json',
            ],
            json_encode($body)
        );
    }

    private function normalizeId(string $fileId): string
    {
        return preg_replace('/[^a-z0-9]/', 'x', strtolower($fileId));
    }

    private function autoIsDisabled(FileInterface $file): bool
    {
        return strpos($file->getMutation() ?? '', 'auto=false') === false;
    }

    private function toAutoMutation(FileInterface $file): string
    {
        return $file->getMutation() ? "{$file->getMutation()},auto" : 'auto';
    }
}
