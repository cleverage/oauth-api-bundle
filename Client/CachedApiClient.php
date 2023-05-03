<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Client;

use CleverAge\OAuthApiBundle\Request\ApiRequestInterface;
use CleverAge\OAuthApiBundle\Request\CachedApiRequestInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Api client improved with caching capabilities
 */
class CachedApiClient extends ApiClient implements CachedApiClientInterface
{
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        protected TagAwareCacheInterface $cache,
    ) {
        parent::__construct(
            client: $client,
            requestFactory: $requestFactory,
            serializer: $serializer,
            logger: $logger,
        );
    }

    public function query(ApiRequestInterface $apiRequest): object
    {
        $request = $this->getRequest($apiRequest);
        if ($apiRequest instanceof CachedApiRequestInterface) {
            $normalizedData = $this->getCachedResponseData($apiRequest, $request);
        } else {
            $normalizedData = $this->getResponseData($request);
        }

        return $this->deserialize($apiRequest, $normalizedData);
    }

    public function invalidate(array $tags, bool $private = false): void
    {
        $this->cache->invalidateTags($this->convertTags($tags, $private));
    }

    public function getCachedResponseData(
        CachedApiRequestInterface $apiRequest,
        RequestInterface $request
    ): ?string {
        if (0 === $apiRequest->getTTL()) {
            return $this->getResponseData($request);
        }

        $cacheKey = $this->getCacheKey($request, $apiRequest->isPrivate());

        return $this->cache->get(
            $cacheKey,
            function (ItemInterface $item) use ($apiRequest, $request) {
                $item
                    ->tag($this->convertTags($apiRequest->getTags(), $apiRequest->isPrivate()))
                    ->expiresAfter($apiRequest->getTTL());

                return $this->getResponseData($request);
            },
        );
    }

    protected function getCacheKey(RequestInterface $request, bool $private): string
    {
        $subCacheKey = $request->getMethod().$request->getUri().$request->getBody();
        if ($private) {
            if (!$this->requestFactory instanceof OAuthTokenAwareRequestFactoryInterface) {
                throw new \UnexpectedValueException('Unable to store private API cache, no private token available');
            }
            // Append authorization token to cache key if private
            $subCacheKey .= $this->requestFactory->getToken()->getAuthorization();
        }

        return sha1($subCacheKey);
    }

    protected function convertTags(array $tags, bool $private): array
    {
        if (!$private) {
            return $tags;
        }
        if (!$this->requestFactory instanceof OAuthTokenAwareRequestFactoryInterface) {
            throw new \UnexpectedValueException('Unable to store private API cache, no private token available');
        }

        // Append authorization token to cache key if private
        $subCacheKey = $this->requestFactory->getToken()->getAuthorization();
        $privateTags = [];
        foreach ($tags as $tag) {
            $privateTags[] = $tag.'_'.$subCacheKey;
        }

        return $privateTags;
    }
}
