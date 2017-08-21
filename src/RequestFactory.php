<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Solid\Collection\ArrayCollection;

/**
 * @package Solid\Http
 * @author  Martin Pettersson <martin@solid-framework.com>
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var \Interop\Http\Factory\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param \Interop\Http\Factory\UriFactoryInterface    $uriFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface $streamFactory
     */
    public function __construct(UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory)
    {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest($method, $uri): RequestInterface
    {
        return new Request(
            $method,
            $uri instanceof UriInterface ? $uri : $this->uriFactory->createUri($uri),
            '1.1',
            new ArrayCollection(),
            $this->streamFactory->createStream()
        );
    }
}