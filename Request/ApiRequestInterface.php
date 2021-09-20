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
interface ApiRequestInterface
{
    public function getMethod(): string;

    public function getPath(): string;

    public function getClassName(): string;

    public function getContent();

    public function getSerializationContext(): array;

    public function getDeserializationContext(): array;
}
