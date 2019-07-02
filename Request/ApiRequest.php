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
 * Represents an API request for the ApiClient
 */
class ApiRequest implements ApiRequestInterface
{
    /** @var string */
    protected $method;

    /** @var string */
    protected $path;

    /** @var string */
    protected $className;

    /** @var mixed */
    protected $content;

    /** @var array */
    protected $serializationContext = [];

    /** @var array */
    protected $deserializationContext = [];

    /**
     * @param string $path
     * @param string $className
     * @param string $method
     */
    public function __construct(string $path, string $className, string $method = 'GET')
    {
        $this->path = $path;
        $this->className = $className;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     *
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array
     */
    public function getSerializationContext(): array
    {
        return $this->serializationContext;
    }

    /**
     * @param array $serializationContext
     *
     * @return self
     */
    public function setSerializationContext(array $serializationContext): self
    {
        $this->serializationContext = $serializationContext;

        return $this;
    }

    /**
     * @return array
     */
    public function getDeserializationContext(): array
    {
        return $this->deserializationContext;
    }

    /**
     * @param array $deserializationContext
     *
     * @return self
     */
    public function setDeserializationContext(array $deserializationContext): self
    {
        $this->deserializationContext = $deserializationContext;

        return $this;
    }
}
