<?php

declare(strict_types=1);

namespace FileJet\Messages;

use Psr\Http\Message\ResponseInterface;

final class UploadInstruction
{
    /** @var string */
    private $identifier;
    /** @var UploadFormat */
    private $uploadFormat;

    public function __construct(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        $uploadFormatData = $data['uploadFormat'];

        $this->identifier = $data['id'];
        $this->uploadFormat = new UploadFormat(
            $uploadFormatData['url'],
            $uploadFormatData['httpMethod'],
            $uploadFormatData['headers']
        );
    }

    public function getFileIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUploadFormat(): UploadFormat
    {
        return $this->uploadFormat;
    }
}
