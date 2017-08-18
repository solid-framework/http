<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\UriInterface;


/**
 * @package Solid\Http
 * @author  Martin Pettersson <martin@solid-framework.com>
 */
class UriFactory implements UriFactoryInterface
{
    use UriTrait;

    /**
     * @param string $uri
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function createUri($uri = ''): UriInterface
    {
        // Set a default protocol so that parse_url behaves correctly.
        if (strpos($uri, '://') === false) {
            $uri = 'parse-url://' . $uri;
        }

        $uriComponents = parse_url($uri);

        $scheme = isset($uriComponents['scheme']) && $uriComponents['scheme'] !== 'parse-url' ?
            $this->normalizeScheme($uriComponents['scheme']) :
            null;

        $username = $uriComponents['user'] ?? null;
        $password = $uriComponents['pass'] ?? null;
        $host = isset($uriComponents['host']) ? $this->normalizeHost($uriComponents['host']) : null;
        $port = $uriComponents['port'] ?? null;
        $path = isset($uriComponents['path']) ? $this->encodePath($uriComponents['path']) : null;
        $query = isset($uriComponents['query']) ? $this->encodeQuery($uriComponents['query']) : null;
        $fragment = isset($uriComponents['fragment']) ? $this->encodeFragment($uriComponents['fragment']) : null;

        return new Uri($scheme, $username, $password, $host, $port, $path, $query, $fragment);
    }
}