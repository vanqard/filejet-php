<?php

declare(strict_types=1);

namespace FileJet\Messages;

final class UploadFormat
{
    /** @var string */
    private string $uri;
    /** @var string */
    private string $requestMethod;
    /** @var array */
    private array $headers;

    public function __construct(string $uri, string $requestMethod, array $headers = [])
    {
        $this->uri = $uri;
        $this->requestMethod = $requestMethod;
        $this->headers = $headers;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
