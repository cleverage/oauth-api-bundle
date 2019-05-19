<?php
/*
 * This file is part of the CleverAge/OAuthApiBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\OAuthApiBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Redirect to URL in session if any else use default Symfony behaviour
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var string */
    public const SESSION_KEY = 'sso_redirect_url';

    /** @var AttributeBagInterface */
    protected $attributeBag;

    /**
     * @param HttpUtils             $httpUtils
     * @param array                 $options
     * @param AttributeBagInterface $attributeBag
     */
    public function __construct(HttpUtils $httpUtils, array $options = [], AttributeBagInterface $attributeBag = null)
    {
        parent::__construct($httpUtils, $options);
        $this->attributeBag = $attributeBag;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($this->attributeBag->has(self::SESSION_KEY)) {
            $redirectUrl = $this->attributeBag->get(self::SESSION_KEY);
            if ($redirectUrl) {
                return $this->httpUtils->createRedirectResponse($request, $redirectUrl);
            }
        }

        return parent::onAuthenticationSuccess($request, $token);
    }
}
