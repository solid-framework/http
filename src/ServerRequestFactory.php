<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Solid\Collection\ArrayCollection;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    use RequestBuilderTrait;
    use UriBuilderTrait;

    /**
     * @var \Interop\Http\Factory\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var \Interop\Http\Factory\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * @param \Interop\Http\Factory\UriFactoryInterface          $uriFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface       $streamFactory
     * @param \Interop\Http\Factory\UploadedFileFactoryInterface $uploadedFileFactory
     */
    public function __construct(
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    /**
     * @param string              $method
     * @param UriInterface|string $uri
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createServerRequest($method, $uri): ServerRequestInterface
    {
        return new ServerRequest(
            $method,
            $uri instanceof UriInterface ? $uri : $this->uriFactory->createUri($uri),
            '1.1',
            new ArrayCollection(),
            $this->streamFactory->createStream(''),
            [],
            [],
            []
        );
    }

    /**
     * @param array $server
     * @return \Psr\Http\Message\ServerRequestInterface
     * @throws \InvalidArgumentException
     */
    public function createServerRequestFromArray(array $server): ServerRequestInterface
    {
        if (is_null($method = $this->getMethod($server))) {
            throw new InvalidArgumentException('Could not determine request method');
        }

        if (is_null($uri = $this->getUri($server))) {
            throw new InvalidArgumentException('Could not determine request uri');
        }

        return new ServerRequest(
            $method,
            $this->uriFactory->createUri($uri),
            $this->getProtocolVersion($server) ?? '1.1',
            new ArrayCollection(),
            $this->streamFactory->createStream(''),
            [],
            [],
            $server
        );
    }
}
