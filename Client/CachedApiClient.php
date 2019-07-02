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

use CleverAge\OAuthApiBundle\Request\ApiRequestInterface;
use CleverAge\OAuthApiBundle\Request\CachedApiRequestInterface;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Api client improved with caching capabilities
 */
class CachedApiClient extends ApiClient implements CachedApiClientInterface
{
    /** @var TagAwareAdapterInterface|null */
    protected $cacheAdapter;

    /**
     * @param HttpClient               $client
     * @param SerializerInterface      $serializer
     * @param LoggerInterface          $logger
     * @param string                   $baseUrl
     * @param TagAwareAdapterInterface $cacheAdapter
     */
    public function __construct(
        HttpClient $client,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $baseUrl,
        TagAwareAdapterInterface $cacheAdapter
    ) {
        parent::__construct($client, $serializer, $logger, $baseUrl);
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * {@inheritDoc}
     */
    public function query(ApiRequestInterface $apiRequest)
    {
        $request = $this->getRequest($apiRequest);
        if ($apiRequest instanceof CachedApiRequestInterface) {
            $normalizedData = $this->getCachedResponseData($apiRequest, $request);
        } else {
            $normalizedData = $this->getResponseData($request);
        }

        return $this->deserialize($apiRequest, $normalizedData);
    }

    /**
     * @param array $tags
     * @param bool  $private
     *
     * @throws InvalidArgumentException
     */
    public function invalidate(array $tags, bool $private = false): void
    {
        $this->cacheAdapter->invalidateTags($this->convertTags($tags, $private));
    }

    /**
     * @param CachedApiRequestInterface $apiRequest
     * @param Request                   $request
     *
     * @return mixed
     */
    public function getCachedResponseData(
        CachedApiRequestInterface $apiRequest,
        Request $request
    ) {
        if (0 === $apiRequest->getTTL()) {
            return $this->getResponseData($request);
        }

        $cacheKey = $this->getCacheKey($request, $apiRequest->isPrivate());

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

        $body = $this->getResponseData($request);

        if ($result) {
            $result->set($body);
            $result->tag($this->convertTags($apiRequest->getTags(), $apiRequest->isPrivate()));
            $result->expiresAfter($apiRequest->getTTL());
            $this->cacheAdapter->save($result);
        }

        return $body;
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
            if (!$client instanceof OAuthTokenAwareHttpClientInterface) {
                throw new \UnexpectedValueException('Unable to store private API cache, no private token available');
            }

            // Append authorization token to cache key if private
            $subCacheKey .= $client->getToken()->getAuthorization();
        }

        return sha1($subCacheKey);
    }

    /**
     * @param array $tags
     * @param bool  $private
     *
     * @return array
     */
    protected function convertTags(array $tags, bool $private): array
    {
        if (!$private) {
            return $tags;
        }
        if (!$this->client instanceof OAuthTokenAwareHttpClientInterface) {
            throw new \UnexpectedValueException('Unable to store private API cache, no private token available');
        }

        // Append authorization token to cache key if private
        $subCacheKey = $this->client->getToken()->getAuthorization();
        $privateTags = [];
        foreach ($tags as $tag) {
            $privateTags[] = $tag.'_'.$subCacheKey;
        }

        return $privateTags;
    }
}
