<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\StreamFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * @param string $content
     * @return \Psr\Http\Message\StreamInterface
     */
    public function createStream($content = ''): StreamInterface
    {
        return new StringStream($content);
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return \Psr\Http\Message\StreamInterface
     * @throws \InvalidArgumentException
     */
    public function createStreamFromFile($filename, $mode = 'r'): StreamInterface
    {
        if (($resource = @fopen($filename, $mode)) === false) {
            throw new InvalidArgumentException("Could not open: {$filename} using mode: {$mode}");
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * @param resource $resource
     * @return \Psr\Http\Message\StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Expected a valid resource');
        }

        return new ResourceStream($resource);
    }
}