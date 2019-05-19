<?php
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
use Http\Client\Exception as HttpException;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
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

    /** @var AdapterInterface|null */
    protected $cacheAdapter;

    /** @var string|null */
    protected $errorClass;

    /**
     * @param HttpClient            $client
     * @param SerializerInterface   $serializer
     * @param LoggerInterface       $logger
     * @param string                $baseUrl
     * @param AdapterInterface|null $cacheAdapter
     * @param string|null           $errorClass
     */
    public function __construct(
        HttpClient $client,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $baseUrl,
        AdapterInterface $cacheAdapter = null,
        string $errorClass = null
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
        $this->cacheAdapter = $cacheAdapter;
        $this->errorClass = $errorClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemoteObject(
        string $path,
        string $className,
        array $deserializationContext = [],
        int $ttl = 0,
        bool $private = false
    ) {
        $uri = $this->baseUrl.$path;
        $request = new Request('GET', $uri);

        return $this->fetchRemoteObject($request, $className, $deserializationContext, $ttl, $private);
    }

    /**
     * {@inheritDoc}
     */
    public function postRemoteObject(
        string $path,
        $content,
        string $className,
        array $serializationContext = [],
        array $deserializationContext = []
    ) {
        $serializedData = $this->serializer->serialize($content, 'json', $serializationContext);
//        dump(\json_decode($serializedData)); // @todo remove me

        $uri = $this->baseUrl.$path;
        $request = new Request(
            'POST',
            $uri,
            [
                'Content-Type' => 'application/json',
            ],
            $serializedData
        );

        return $this->fetchRemoteObject($request, $className, $deserializationContext);
    }

    /**
     * @param Request $request
     * @param string  $className
     * @param array   $deserializationContext
     * @param int     $ttl
     * @param bool    $private
     *
     * @return object
     */
    protected function fetchRemoteObject(
        Request $request,
        string $className,
        array $deserializationContext = [],
        int $ttl = 0,
        bool $private = false
    ) {
        $normalizedData = $this->getResponseData($request, $ttl, $private);
//        dump(json_decode($normalizedData)); // @todo remove me

        try {
            return $this->serializer->deserialize(
                $normalizedData,
                $className,
                'json',
                $deserializationContext
            );
        } catch (\Exception $e) {
            throw $this->handleError($e, $normalizedData, $className, $deserializationContext);
        }
    }

    /**
     * @param Request $request
     * @param int     $ttl
     *
     * @param bool    $private
     *
     * @return string|null
     */
    protected function getResponseData(
        Request $request,
        int $ttl = 0,
        bool $private = false
    ): ?string {
        if (!$this->cacheAdapter || 0 === $ttl) {
            return $this->doGetResponseData($request);
        }

        $cacheKey = $this->getCacheKey($request, $private);

        $result = null;
        try {
            $result = $this->cacheAdapter->getItem($cacheKey);
            if ($result->isHit()) {
                $this->logger->info("Cache hit for request {$request->getUri()}");

                return $result->get();
            }
        } catch (InvalidArgumentException $e) {
            $this->logger->alert(
                "Unable to access api cache: {$e->getMessage()}",
                [
                    'exception' => $e,
                ]
            );
        }

        $body = $this->doGetResponseData($request);
        $result->set($body);
        $result->expiresAfter($ttl);
        $this->cacheAdapter->save($result);

        return $body;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function doGetResponseData(Request $request): string
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (HttpException $e) {
            throw ApiRequestException::create($request->getUri(), $e);
        }

        return (string) $response->getBody();
    }

    /**
     * @param Request $request
     * @param bool    $private
     *
     * @return string
     */
    protected function getCacheKey(Request $request, bool $private): string
    {
        $subCacheKey = $request->getMethod().$request->getUri().$request->getBody();
        if ($private) {
            $client = $this->client;
            if ($client instanceof OAuthTokenAwareHttpClientInterface) {
                // Append authorization token to cache key if private
                $subCacheKey .= $client->getToken()->getAuthorization();
            } else {
                throw new \UnexpectedValueException('Unable to store private API cache, no private token available');
            }
        }

        return sha1($subCacheKey);
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
        if ($this->errorClass) {
            try {
                $errorObject = $this->serializer->deserialize(
                    $normalizedData,
                    $this->errorClass,
                    'json',
                    $deserializationContext
                );
            } catch (\Exception $e) {
                $errorObject = \json_decode($normalizedData, true);
            }
        } else {
            $errorObject = \json_decode($normalizedData, true);
        }

        return ApiDeserializationException::create($exception, $normalizedData, $className, $errorObject);
    }
}
