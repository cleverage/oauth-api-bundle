<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 * Copyright (C) 2017-2023 Clever-Age
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace CleverAge\OAuthApiBundle\Exception;

use Psr\Http\Message\ResponseInterface;

class RequestFailedException extends \RuntimeException
{
    public function __construct(
        protected ResponseInterface $response,
        $message = 'Request have failed',
    ) {
        $message .= " (status code {$response->getStatusCode()})";

        parent::__construct($message);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public static function createFromResponse(ResponseInterface $response): self
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400 && $statusCode < 500) {
            return new ClientRequestFailedException($response);
        }
        if ($statusCode >= 500 && $statusCode < 600) {
            return new ServerRequestFailedException($response);
        }
        return new RequestFailedException($response);
    }
}
