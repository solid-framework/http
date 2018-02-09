<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
trait RequestBuilderTrait
{
    /**
     * @param array $server
     * @return string|null
     */
    protected function getMethod(array $server): ?string
    {
        if (array_key_exists('REQUEST_METHOD', $server)) {
            return $server['REQUEST_METHOD'];
        }

        return null;
    }

    /**
     * @param array $server
     * @return string|null
     */
    protected function getProtocolVersion(array $server): ?string
    {
        if (!array_key_exists('SERVER_PROTOCOL', $server)) {
            return null;
        }

        $protocolSplit = explode('/', $server['SERVER_PROTOCOL']);
        $protocolVersion = end($protocolSplit);

        if (strlen($protocolVersion) === 3 && ($protocolVersion)) {
            return $protocolVersion;
        }

        return null;
    }
}
