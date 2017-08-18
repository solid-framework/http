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
 * @author  Martin Pettersson <martin@solid-framework.com>
 */
trait UriTrait
{
    /**
     * @since 0.1.0
     * @param string $scheme
     * @return string
     */
    protected function normalizeScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    /**
     * @since 0.1.0
     * @param string $host
     * @return string
     */
    protected function normalizeHost(string $host): string
    {
        return strtolower($host);
    }

    /**
     * @since 0.1.0
     * @param string $path
     * @return string
     */
    protected function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', (string)$path))));
    }

    /**
     * @since 0.1.0
     * @param string $query
     * @return string
     */
    protected function encodeQuery(string $query): string
    {
        return implode('&', array_map(function ($parameter) {
            return implode('=', array_map('rawurlencode', array_map('rawurldecode', explode('=', $parameter))));
        }, explode('&', $query)));
    }

    /**
     * @since 0.1.0
     * @param string $fragment
     * @return string
     */
    protected function encodeFragment(string $fragment): string
    {
        return rawurlencode(rawurldecode((string)$fragment));
    }
}