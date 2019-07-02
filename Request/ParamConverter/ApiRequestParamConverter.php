<?php declare(strict_types=1);
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Request\ParamConverter;

use CleverAge\OAuthApiBundle\Model\ApiRequestInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Converts any incoming body request with the class in option
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class ApiRequestParamConverter implements ParamConverterInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $value = $request->getContent();
        if (!$value && $configuration->isOptional()) {
            return false;
        }

        // @todo dynamic format guessing from request content-type?
        $convertedValue = $this->serializer->deserialize($value, $configuration->getClass(), 'json');

        if (null === $convertedValue && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(
                "Unable to deserialize '{$configuration->getClass()}' with data '{$value}'"
            );
        }

        $request->attributes->set($configuration->getName(), $convertedValue);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() && is_a($configuration->getClass(), ApiRequestInterface::class, true);
    }
}
