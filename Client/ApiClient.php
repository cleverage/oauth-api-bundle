<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Exception\ApiDeserializationException;
use CleverAge\OAuthApiBundle\Exception\ApiRequestException;
use CleverAge\OAuthApiBundle\Exception\RequestFailedException;
use CleverAge\OAuthApiBundle\Request\ApiRequestInterface;
use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @see ApiClientInterface
 */
class ApiClient implements ApiClientInterface
{
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected SerializerInterface $serializer,
        protected LoggerInterface $logger,
    ) {
    }

    public function query(ApiRequestInterface $apiRequest): object
    {
        $request = $this->getRequest($apiRequest);
        $normalizedData = $this->getResponseData($request);

        return $this->deserialize($apiRequest, $normalizedData);
    }

    protected function deserialize(ApiRequestInterface $apiRequest, ?string $normalizedData): object
    {
        try {
            return $this->serializer->deserialize(
                $normalizedData,
                $apiRequest->getClassName(),
                $apiRequest->getDeserializationFormat(),
                $apiRequest->getDeserializationContext()
            );
        } catch (\Exception $e) {
            throw $this->handleError(
                $e,
                $apiRequest,
                $normalizedData,
            );
        }
    }

    protected function getRequest(ApiRequestInterface $apiRequest): RequestInterface
    {
        $request = $this->requestFactory->createRequest($apiRequest->getMethod(), $apiRequest->getPath())
            ->withHeader('Content-Type', $apiRequest->getContentType());

        foreach ($apiRequest->getHeaders() as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        if (null === $apiRequest->getContent()) {
            return $request;
        }

        $serializedContent = $this->serializer->serialize(
            $apiRequest->getContent(),
            $apiRequest->getSerializationFormat(),
            $apiRequest->getSerializationContext()
        );

        return $request->withBody(Stream::create($serializedContent));
    }

    protected function getResponseData(
        RequestInterface $request
    ): ?string {
        $this->logger->debug(
            "API Request: {$request->getMethod()} {$request->getUri()}",
            [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'body' => (string) $request->getBody(),
            ]
        );
        try {
            $response = $this->client->sendRequest($request);
        } catch (RequestExceptionInterface $e) {
            throw ApiRequestException::create((string) $request->getUri(), $e);
        }
        $body = (string) $response->getBody();
        $this->logger->debug(
            "API Response: {$request->getMethod()} {$response->getStatusCode()} {$request->getUri()}",
            [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'status_code' => $response->getStatusCode(),
                'body' => $body,
            ]
        );

        if ($response->getStatusCode() >= 400) {
            throw RequestFailedException::createFromResponse($response);
        }

        return $body;
    }

    protected function handleError(
        \Exception $exception,
        ApiRequestInterface $apiRequest,
        string $normalizedData,
    ): ApiDeserializationException {
        $errorClass = $deserializationContext['error_class'] ?? null;
        if ($errorClass) {
            try {
                $errorObject = $this->serializer->deserialize(
                    $normalizedData,
                    $errorClass,
                    $apiRequest->getDeserializationFormat(),
                    $apiRequest->getDeserializationContext(),
                );
            } catch (\Exception) {
                $errorObject = \json_decode($normalizedData, true, 512, JSON_THROW_ON_ERROR);
            }
        } else {
            $errorObject = \json_decode($normalizedData, true, 512, JSON_THROW_ON_ERROR);
        }

        return ApiDeserializationException::create(
            $exception,
            $normalizedData,
            $apiRequest->getClassName(),
            $errorObject
        );
    }
}
