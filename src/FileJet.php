<?php

declare(strict_types=1);

namespace FileJet;

use FileJet\Messages\DownloadInstruction;
use FileJet\Messages\UploadInstruction;
use FileJet\Messages\UploadInstructionFactory;
use FileJet\Messages\UploadRequest;

final class FileJet
{
    /** @var HttpClient */
    private $httpClient;
    /** @var Config */
    private $config;
    /** @var Mutation */
    private $mutation;

    public function __construct(HttpClient $httpClient, Config $config, Mutation $mutation)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->mutation = $mutation;
    }

    public function getUrl(FileInterface $file): string
    {
        $url = "{$this->config->getPublicUrl()}/{$this->normalizeId($file->getIdentifier())}";

        if ($this->config->isAutoMode() && $this->mutation->autoIsEnabled($file->getMutation())) {
            $file = new File($file->getIdentifier(), $this->mutation->toAutoMutation($file->getMutation()));
        }

        if ($this->config->isAutoMode() && false === $this->mutation->autoIsEnabled($file->getMutation())) {
            $file = new File($file->getIdentifier(), $this->mutation->removeAutoMutation($file->getMutation()));
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

    public function getExternalUrl(string $url, string $mutation = '')
    {

        if ($this->config->isAutoMode() && $this->mutation->autoIsEnabled($mutation)) {
            $mutation = $this->mutation->toAutoMutation($mutation);
        }

        if ($this->config->isAutoMode() && false === $this->mutation->autoIsEnabled($mutation)) {
            $mutation = $this->mutation->removeAutoMutation($mutation);
        }

        if ($mutation === null) $mutation = '';

        return "{$this->config->getPublicUrl()}/ext/{$mutation}?src={$this->sign($url)}";
    }

    public function uploadFile(UploadRequest $request): UploadInstruction
    {
        return UploadInstructionFactory::createFromResponse(
            $this->request('file.requestUpload', [
                'contentType' => $request->getContentType(),
                'expires' => $request->getExpires(),
                'access' => $request->getAccess(),
            ])
        );
    }

    /**
     * @param UploadRequest[] $requests
     *
     * @return UploadInstruction[]
     */
    public function bulkUploadFiles(array $requests): array
    {
        $body = [];
        foreach ($requests as $request) {
            $body[] = [
                'contentType' => $request->getContentType(),
                'expires' => $request->getExpires(),
                'access' => $request->getAccess(),
            ];
        }

        $decodedBulkResponse = json_decode($this->request('file.requestUpload', $body)->getBody()->getContents(), true);
        $uploadInstructions = [];
        /** @var string[][] $instructionData */
        foreach ($decodedBulkResponse as $instructionData) {
            $uploadInstructions[] = UploadInstructionFactory::createFromArray($instructionData);
        }

        return $uploadInstructions;
    }

    public function deleteFile(string $fileId): void
    {
        $this->request('file.delete', ['fileId' => $this->normalizeId($fileId)]);
    }

    private function request(string $operation, array $body)
    {
        return $this->httpClient->sendRequest(
            HttpClient::METHOD_POST,
            "{$this->config->getStorageManagerUrl()}/$operation",
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

    private function sign(string $url): string
    {
        if ($this->config->getSignatureSecret() === null) return $url;

        return urlencode($url).'&sig='.hash_hmac('sha256', $url, $this->config->getSignatureSecret());
    }

    /**
     * @deprecated Please do use the FileJet/Mutation
     */
    public function toMutation(FileInterface $file, string $mutation = null) : ?string {
        return $this->mutation->toMutation($file, $mutation);
    }
}
