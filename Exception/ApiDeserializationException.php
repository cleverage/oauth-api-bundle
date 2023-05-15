<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Exception;

/**
 * Thrown when not able to deserialize a response from the API.
 */
class ApiDeserializationException extends \RuntimeException
{
    protected ?string $responseBody;

    protected string $className;

    protected mixed $errorData;

    public static function create(
        \Exception $exception,
        ?string $responseBody,
        string $className,
        mixed $errorObject = null
    ): self {
        $exception = new self("Unable to deserialize data from response: {$responseBody}", 0, $exception);
        $exception->responseBody = $responseBody;
        $exception->className = $className;
        $exception->errorData = $errorObject;

        return $exception;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getErrorData(): mixed
    {
        return $this->errorData;
    }
}
