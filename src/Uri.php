<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Psr\Http\Message\UriInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @todo Throw appropriate exceptions.
 */
class Uri implements UriInterface
{
    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $scheme;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $username;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $password;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $host;

    /**
     * @since 0.1.0
     * @var int|null
     */
    protected $port;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $path;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $query;

    /**
     * @since 0.1.0
     * @var string|null
     */
    protected $fragment;

    /**
     * @api
     * @since 0.1.0
     * @param string|null $scheme
     * @param string|null $username
     * @param string|null $password
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $path
     * @param string|null $query
     * @param string|null $fragment
     */
    public function __construct(
        ?string $scheme,
        ?string $username,
        ?string $password,
        ?string $host,
        ?int $port,
        ?string $path,
        ?string $query,
        ?string $fragment
    ) {
        $this->scheme = $scheme;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $uri
     * @return \Solid\Http\Uri
     */
    public static function fromString(string $uri): Uri
    {
        // Set a default protocol so that parse_url behaves correctly.
        if (strpos($uri, '://') === false) {
            $uri = 'parse-url://' . $uri;
        }

        $uriComponents = parse_url($uri);

        $scheme = isset($uriComponents['scheme']) && $uriComponents['scheme'] !== 'parse-url' ?
            self::normalizeScheme($uriComponents['scheme']) :
            null;

        $username = $uriComponents['user'] ?? null;
        $password = $uriComponents['pass'] ?? null;
        $host = isset($uriComponents['host']) ? self::normalizeHost($uriComponents['host']) : null;
        $port = $uriComponents['port'] ?? null;
        $path = isset($uriComponents['path']) ? self::encodePath($uriComponents['path']) : null;
        $query = isset($uriComponents['query']) ? self::encodeQuery($uriComponents['query']) : null;
        $fragment = isset($uriComponents['fragment']) ? self::encodeFragment($uriComponents['fragment']) : null;

        return new static($scheme, $username, $password, $host, $port, $path, $query, $fragment);
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getScheme(): string
    {
        return (string)$this->scheme;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = $this->getHost();

        if (strlen($authority) === 0) {
            return $authority;
        }

        $userInfo = $this->getUserInfo();

        if (strlen($userInfo) > 0) {
            $authority = "{$userInfo}@{$authority}";
        }

        $port = $this->getPort();

        if (!is_null($port)) {
            $authority .= ":{$port}";
        }

        return $authority;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getUserInfo(): string
    {
        $userInfo = (string)$this->username;

        if (!is_null($this->password)) {
            $userInfo .= ":{$this->password}";
        }

        return $userInfo;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getHost(): string
    {
        return (string)$this->host;
    }

    /**
     * @api
     * @since 0.1.0
     * @return int|null
     */
    public function getPort(): ?int
    {
        if (!is_null($this->port) && !$this->isStandardPort($this->port)) {
            return $this->port;
        }

        return null;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getPath(): string
    {
        return (string)$this->path;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getQuery(): string
    {
        return (string)$this->query;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getFragment(): string
    {
        return (string)$this->fragment;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $scheme
     * @return \Solid\Http\Uri
     * @throws \InvalidArgumentException
     */
    public function withScheme($scheme): Uri
    {
        $uri = clone $this;

        $uri->scheme = strlen($scheme) > 0 ? self::normalizeScheme($scheme) : null;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string      $user
     * @param string|null $password
     * @return \Solid\Http\Uri
     */
    public function withUserInfo($user, $password = null): Uri
    {
        $uri = clone $this;

        $uri->username = strlen($user) > 0 ? $user : null;
        $uri->password = $password;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $host
     * @return \Solid\Http\Uri
     * @throws \InvalidArgumentException
     */
    public function withHost($host): Uri
    {
        $uri = clone $this;

        $uri->host = strlen($host) > 0 ? self::normalizeHost($host) : null;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int|null $port
     * @return \Solid\Http\Uri
     * @throws \InvalidArgumentException
     */
    public function withPort($port): Uri
    {
        $uri = clone $this;

        $uri->port = $port;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $path
     * @return \Solid\Http\Uri
     * @throws \InvalidArgumentException
     */
    public function withPath($path): Uri
    {
        $uri = clone $this;

        $uri->path = self::encodePath($path);

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $query
     * @return \Solid\Http\Uri
     * @throws \InvalidArgumentException
     */
    public function withQuery($query): Uri
    {
        $uri = clone $this;

        $uri->query = strlen($query) > 0 ? self::encodeQuery($query) : null;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $fragment
     * @return \Solid\Http\Uri
     */
    public function withFragment($fragment): Uri
    {
        $uri = clone $this;

        $uri->fragment = strlen($fragment) > 0 ? self::encodeFragment($fragment) : null;

        return $uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        $uri = !is_null($this->scheme) ? $this->scheme . ':' : '';

        $authority = $this->getAuthority();

        if (strlen($authority) > 0) {
            $uri .= '//' . $authority;
        }

        $path = $this->getPath();

        if (strlen($path)) {
            // If the path is rootless and an authority is present, the path MUST be prefixed by "/".
            if (strpos($path, '/') !== 0 && strlen($authority) > 0) {
                $path = '/' . $path;
            }

            // If the path is starting with more than one "/" and no authority is present, the starting
            // slashes MUST be reduced to one.
            if ($path[1] === '/' && strlen($authority) === 0) {
                $path = '/' . ltrim($path, '/');
            }
        }

        $uri .= $path;

        if (!is_null($this->query)) {
            $uri .= '?' . $this->getQuery();
        }

        if (!is_null($this->fragment)) {
            $uri .= '#' . $this->getFragment();
        }

        return $uri;
    }

    /**
     * @since 0.1.0
     * @param int $port
     * @return bool
     */
    protected function isStandardPort(int $port): bool
    {
        switch ($this->scheme) {
            case 'http':
                return $port === 80;
            case 'https':
                return $port === 443;
            default:
                return false;
        }
    }

    /**
     * @since 0.1.0
     * @param string $scheme
     * @return string
     */
    protected static function normalizeScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    /**
     * @since 0.1.0
     * @param string $host
     * @return string
     */
    protected static function normalizeHost(string $host): string
    {
        return strtolower($host);
    }

    /**
     * @since 0.1.0
     * @param string $path
     * @return string
     */
    protected static function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', (string)$path))));
    }

    /**
     * @since 0.1.0
     * @param string $query
     * @return string
     */
    protected static function encodeQuery(string $query): string
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
    protected static function encodeFragment(string $fragment): string
    {
        return rawurlencode(rawurldecode((string)$fragment));
    }
}
