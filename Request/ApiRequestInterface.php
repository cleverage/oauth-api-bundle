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
interface ApiRequestInterface
{
    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getClassName(): string;

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return array
     */
    public function getSerializationContext(): array;

    /**
     * @return array
     */
    public function getDeserializationContext(): array;
}
