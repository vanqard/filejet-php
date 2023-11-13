<?php

declare(strict_types=1);

namespace FileJet;

use Psr\Http\Message\ResponseInterface;

final class RemoteFileJetException extends \Exception
{
    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            sprintf(
                "FileJet service returned response with status code %d and message: %s",
                $response->getStatusCode(),
                $response->getBody()
            )
        );
    }
}
