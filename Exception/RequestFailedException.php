<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Exception;

use Psr\Http\Message\ResponseInterface;

class RequestFailedException extends \RuntimeException
{
    /** @var ResponseInterface */
    protected $response;

    public function __construct(ResponseInterface $response, $message = 'Request have failed')
    {
        $message .= " (status code {$response->getStatusCode()})";

        parent::__construct($message, 0, null);
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public static function createFromResponse(ResponseInterface $response): self
    {
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            return new ClientRequestFailedException($response);
        } elseif ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            return new ServerRequestFailedException($response);
        } else {
            return new RequestFailedException($response);
        }
    }
}
