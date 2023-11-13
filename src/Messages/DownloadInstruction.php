<?php

declare(strict_types=1);

namespace FileJet\Messages;

use Psr\Http\Message\ResponseInterface;

final class DownloadInstruction
{
    /** @var string */
    private string $url;

    public function __construct(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);
        $this->url = $data['url'];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

}