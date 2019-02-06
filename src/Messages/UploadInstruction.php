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

    public function __construct(string $identifier, UploadFormat $uploadFormat)
    {
        $this->identifier = $identifier;
        $this->uploadFormat = $uploadFormat;
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
