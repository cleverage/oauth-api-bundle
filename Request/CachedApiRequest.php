<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Request;

/**
 * {@inheritDoc}
 */
class CachedApiRequest extends ApiRequest implements CachedApiRequestInterface
{
    /** @var int */
    protected $ttl = 0;

    /** @var bool */
    protected $private = false;

    /** @var array */
    protected $tags = [];

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return self
     */
    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     *
     * @return self
     */
    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return CachedApiRequest
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
