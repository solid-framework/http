<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Solid\Collection\ArrayCollection;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param \Interop\Http\Factory\StreamFactoryInterface $streamFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param int $code
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse($code = 200): ResponseInterface
    {
        return new Response('1.1', $code, new ArrayCollection(), $this->streamFactory->createStream());
    }
}
