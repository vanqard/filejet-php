<?php

declare(strict_types=1);

namespace FileJet\Messages;

use Psr\Http\Message\ResponseInterface;

final class UploadInstruction
{
    /** @var string */
    private $identifier;
    /** @var UploadFormat */
    private $storageRequest;

    public function __construct(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        $uploadFormat = $data['uploadFormat'];

        $this->identifier = $data['id'];
        $this->storageRequest = new UploadFormat(
            $uploadFormat['url'],
            $uploadFormat['httpMethod'],
            $uploadFormat['headers']
        );
    }

    public function getFileIdentifier(): string
    {
        return $this->identifier;
    }

    public function getStorageRequest(): UploadFormat
    {
        return $this->storageRequest;
    }
}
