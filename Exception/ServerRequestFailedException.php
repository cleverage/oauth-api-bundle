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

class ServerRequestFailedException extends RequestFailedException
{
    public function __construct(ResponseInterface $response, $message = 'Request have failed on server side')
    {
        if ($response->getStatusCode() < 500 || $response->getStatusCode() >= 600) {
            throw new \InvalidArgumentException('Exception reserved to 5xx responses');
        }

        parent::__construct($response, $message);
    }
}
