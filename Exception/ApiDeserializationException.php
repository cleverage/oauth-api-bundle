<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Exception;

/**
 * Thrown when not able to deserialize a response from the API
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ApiDeserializationException extends \RuntimeException
{
    /** @var string|null */
    protected $responseBody;

    /** @var string */
    protected $className;

    /** @var object */
    protected $errorObject;

    /**
     * @param \Exception  $exception
     * @param string|null $responseBody
     * @param string      $className
     * @param object      $errorObject
     *
     * @return ApiDeserializationException
     */
    public static function create(
        \Exception $exception,
        ?string $responseBody,
        string $className,
        $errorObject = null
    ): self {
        $exception = new self("Unable to deserialize data from response: {$responseBody}", 0, $exception);
        $exception->responseBody = $responseBody;
        $exception->className = $className;
        $exception->errorObject = $errorObject;

        return $exception;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return object
     */
    public function getErrorObject(): object
    {
        return $this->errorObject;
    }
}
