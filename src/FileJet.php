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

    public function __construct(HttpClient $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    public function getUrl(FileInterface $file): string
    {
        $url = "{$this->config->getPublicUrl()}/{$this->normalizeId($file->getIdentifier())}";

        if ($this->config->isAutoMode() && $this->autoIsEnabled($file)) {
            $file = new File($file->getIdentifier(), $this->toAutoMutation($file));
        }

        if ($this->config->isAutoMode() && false === $this->autoIsEnabled($file)) {
            $file = new File($file->getIdentifier(), $this->removeAutoMutation($file));
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

    public function toMutation(FileInterface $file, string $mutation = null) : ?string
    {
        $output = $file->getMutation() ?? '';
        $separator = empty($output) || empty($mutation) ? '' : ',';

        return "{$output}{$separator}{$mutation}";
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

    private function autoIsEnabled(FileInterface $file): bool
    {
        return strpos($file->getMutation() ?? '', 'auto=false') === false;
    }

    private function toAutoMutation(FileInterface $file): string
    {
        return $file->getMutation() ? "{$file->getMutation()},auto" : 'auto';
    }

    private function removeAutoMutation(FileInterface $file): ?string
    {
        $mutation = preg_replace('/,?auto=false/m', '', $file->getMutation());

        return $mutation === '' ? null : $mutation;
    }
}
