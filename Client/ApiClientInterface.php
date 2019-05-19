<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Client;

/**
 * Base logic used to fetch remote objects
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
interface ApiClientInterface
{
    /**
     * @param string $path
     * @param string $className
     * @param array  $deserializationContext
     * @param int    $ttl
     * @param bool   $private
     *
     * @return object
     */
    public function getRemoteObject(
        string $path,
        string $className,
        array $deserializationContext = [],
        int $ttl = 0,
        bool $private = false
    );

    /**
     * @param string       $path
     * @param array|object $content
     * @param string       $className
     * @param array        $serializationContext
     * @param array        $deserializationContext
     *
     * @return object
     */
    public function postRemoteObject(
        string $path,
        $content,
        string $className,
        array $serializationContext = [],
        array $deserializationContext = []
    );
}
