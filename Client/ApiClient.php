<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Exception\ApiDeserializationException;
use CleverAge\OAuthApiBundle\Exception\ApiRequestException;
use CleverAge\OAuthApiBundle\Exception\RequestFailedException;
use CleverAge\OAuthApiBundle\Request\ApiRequestInterface;
use Http\Client\Exception as HttpException;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * {@inheritDoc}
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ApiClient implements ApiClientInterface
{
    /** @var HttpClient */
    protected $client;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var string */
    protected $baseUrl;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param HttpClient          $client
     * @param SerializerInterface $serializer
     * @param LoggerInterface     $logger
     * @param string              $baseUrl
     */
    public function __construct(
        HttpClient $client,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $baseUrl
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function query(ApiRequestInterface $apiRequest)
    {
        $request = $this->getRequest($apiRequest);
        $normalizedData = $this->getResponseData($request);

        return $this->deserialize($apiRequest, $normalizedData);
    }

    /**
     * @param ApiRequestInterface $apiRequest
     * @param string|null         $normalizedData
     *
     * @return object
     */
    protected function deserialize(ApiRequestInterface $apiRequest, ?string $normalizedData)
    {
        try {
            return $this->serializer->deserialize(
                $normalizedData,
                $apiRequest->getClassName(),
                'json',
                $apiRequest->getDeserializationContext()
            );
        } catch (\Exception $e) {
            throw $this->handleError(
                $e,
                $normalizedData,
                $apiRequest->getClassName(),
                $apiRequest->getDeserializationContext()
            );
        }
    }

    /**
     * @param ApiRequestInterface $apiRequest
     *
     * @return Request
     */
    protected function getRequest(ApiRequestInterface $apiRequest): Request
    {
        $serializedContent = null;
        if ($apiRequest->getContent()) {
            $serializedContent = $this->serializer->serialize(
                $apiRequest->getContent(),
                'json',
                $apiRequest->getSerializationContext()
            );
        }

        return new Request(
            $apiRequest->getMethod(),
            $this->baseUrl.$apiRequest->getPath(),
            [
                'Content-Type' => 'application/json',
            ],
            $serializedContent
        );
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getResponseData(
        Request $request
    ): ?string {
        $this->logger->debug(
            "API Request",
            [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'body' => (string)$request->getBody(),
            ]
        );
        try {
            $response = $this->client->sendRequest($request);
        } catch (HttpException $e) {
            throw ApiRequestException::create((string)$request->getUri(), $e);
        }
        $body = (string)$response->getBody();
        $this->logger->debug(
            "API Response",
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

    /**
     * @param \Exception  $exception
     * @param string|null $normalizedData
     * @param string      $className
     * @param array       $deserializationContext
     *
     * @return ApiDeserializationException
     */
    protected function handleError(
        \Exception $exception,
        string $normalizedData,
        string $className,
        array $deserializationContext
    ): ApiDeserializationException {
        $errorObject = null;
        $errorClass = $deserializationContext['error_class'] ?? null;
        if ($errorClass) {
            try {
                $errorObject = $this->serializer->deserialize(
                    $normalizedData,
                    $errorClass,
                    'json',
                    $deserializationContext
                );
            } /** @noinspection BadExceptionsProcessingInspection */ catch (\Exception $e) {
                $errorObject = \json_decode($normalizedData, true);
            }
        } else {
            $errorObject = \json_decode($normalizedData, true);
        }

        return ApiDeserializationException::create($exception, $normalizedData, $className, $errorObject);
    }
}
