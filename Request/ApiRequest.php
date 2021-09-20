<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package. * Copyright (C) 2017-2021 Clever-Age * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Request;

/**
 * Represents an API request for the ApiClient
 */
class ApiRequest implements ApiRequestInterface
{
    protected string $method = 'GET';

    protected mixed $content;

    protected array $serializationContext = [];

    protected array $deserializationContext = [];

    public function __construct(
        protected string $path,
        protected string $className,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getSerializationContext(): array
    {
        return $this->serializationContext;
    }

    public function getDeserializationContext(): array
    {
        return $this->deserializationContext;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function setContent(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setSerializationContext(array $serializationContext): self
    {
        $this->serializationContext = $serializationContext;

        return $this;
    }

    public function setDeserializationContext(array $deserializationContext): self
    {
        $this->deserializationContext = $deserializationContext;

        return $this;
    }
}
