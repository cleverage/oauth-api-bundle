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

class ClientRequestFailedException extends RequestFailedException
{
    public function __construct(ResponseInterface $response, $message = 'Request have failed on client side')
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode < 400 || $statusCode >= 500) {
            throw new \InvalidArgumentException('Exception reserved to 4xx responses');
        }

        parent::__construct($response, $message);
    }
}
