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
trait UriBuilderTrait
{
    /**
     * @param array $server
     * @return string|null
     */
    protected function getUri(array $server): ?string
    {
        if (is_null($host = $this->getHost($server))) {
            return null;
        }

        $scheme = $this->isSsl($server) ? 'https' : 'http';
        $credentials = $this->getCredentials($server);
        $port = $this->getPort($server);
        $requestUri = $this->getRequestUri($server);

        return "{$scheme}://{$credentials}{$host}{$port}{$requestUri}";
    }

    /**
     * @param array $server
     * @return bool
     */
    protected function isSsl(array $server): bool
    {
        if (array_key_exists('HTTPS', $server) && $server['HTTPS'] === 'on') {
            return true;
        }

        if (array_key_exists('SERVER_PORT', $server) && $server['SERVER_PORT'] === '443') {
            return true;
        }

        return false;
    }

    /**
     * @param array $server
     * @return string
     */
    protected function getCredentials(array $server): string
    {
        if (!array_key_exists('PHP_AUTH_USER', $server)) {
            return '';
        }

        $credentials = $server['PHP_AUTH_USER'];

        if (array_key_exists('PHP_AUTH_PW', $server)) {
            $credentials .= ":{$server['PHP_AUTH_PW']}";
        }

        return "{$credentials}@";
    }

    /**
     * @param array $server
     * @return string|null
     */
    protected function getHost(array $server): ?string
    {
        return $server['HTTP_HOST'] ?? null;
    }

    /**
     * @param array $server
     * @return string
     */
    protected function getPort(array $server): string
    {
        if (array_key_exists('SERVER_PORT', $server)) {
            return ":{$server['SERVER_PORT']}";
        }

        return '';
    }

    protected function getRequestUri(array $server): string
    {
        return $server['REQUEST_URI'] ?? '';
    }
}
