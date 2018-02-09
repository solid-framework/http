<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
interface DeserializerInterface
{
    /**
     * @return array
     */
    public function getContentTypes(): array;

    /**
     * @param \Psr\Http\Message\StreamInterface $body
     * @return array|object|null
     */
    public function deserialize(StreamInterface $body);
}
