<?php

declare(strict_types=1);

namespace FileJet;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use PsrDiscovery\Discover;
use Psr\Http\Client\ClientExceptionInterface;


final class HttpClient
{
    public const METHOD_POST = 'POST';

    /** @var ClientInterface */
    private ClientInterface $client;

    /** @var RequestFactoryInterface */
    private RequestFactoryInterface $requestFactory;

    /** @var StreamFactoryInterface  */
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null
    ) {
        $this->client = ($client instanceof ClientInterface) ?
            $client:
            Discover::httpClient();

        $this->requestFactory = ($requestFactory instanceof RequestFactoryInterface) ?
            $requestFactory :
            Discover::httpRequestFactory();

        $this->streamFactory = ($streamFactory instanceof StreamFactoryInterface) ?
            $streamFactory :
            Discover::httpStreamFactory();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param string|null $body
     * @return ResponseInterface
     * @throws RemoteFileJetException
     * @throws ClientExceptionInterface
     */
    public function sendRequest(
        string $method,
        string $uri,
        array $headers = [],
        string $body = null
    ): ResponseInterface {

        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $headerKey => $headerValue) {
            $request = $request->withHeader($headerKey, $headerValue);
        }

        $body = $this->streamFactory->createStream($body);
        $request = $request->withBody($body);
        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new RemoteFileJetException($response);
        }

        return $response;
    }
}
