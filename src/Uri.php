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
 * @todo Throw appropriate exceptions.
 */
class Uri implements UriInterface
{
    use UriTrait;

    /**
     * @var string|null
     */
    protected $scheme;

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
     */
    protected $password;

    /**
     * @var string|null
     */
    protected $host;

    /**
     * @var int|null
     */
    protected $port;

    /**
     * @var string|null
     */
    protected $path;

    /**
     * @var string|null
     */
    protected $query;

    /**
     * @var string|null
     */
    protected $fragment;

    /**
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
        ?string $scheme = null,
        ?string $username = null,
        ?string $password = null,
        ?string $host = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null
    ) {
        $this->scheme = !is_null($scheme) ? $this->normalizeScheme($scheme) : null;
        $this->username = $username;
        $this->password = $password;
        $this->host = !is_null($host) ? $this->normalizeHost($host) : null;
        $this->port = $port;
        $this->path = !is_null($path) ? $this->encodePath($path) : null;
        $this->query = !is_null($query) ? $this->encodeQuery($query) : null;
        $this->fragment = !is_null($fragment) ? $this->encodeFragment($fragment) : null;
    }

    /**
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
     * @return string
     */
    public function getScheme(): string
    {
        return (string)$this->scheme;
    }

    /**
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
     * @return string
     */
    public function getHost(): string
    {
        return (string)$this->host;
    }

    /**
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
     * @return string
     */
    public function getPath(): string
    {
        return (string)$this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return (string)$this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return (string)$this->fragment;
    }

    /**
     * @param string $scheme
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function withScheme($scheme): UriInterface
    {
        $uri = clone $this;

        $uri->scheme = strlen($scheme) > 0 ? self::normalizeScheme($scheme) : null;

        return $uri;
    }

    /**
     * @param string      $user
     * @param string|null $password
     * @return \Psr\Http\Message\UriInterface
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $uri = clone $this;

        $uri->username = strlen($user) > 0 ? $user : null;
        $uri->password = $password;

        return $uri;
    }

    /**
     * @param string $host
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function withHost($host): UriInterface
    {
        $uri = clone $this;

        $uri->host = strlen($host) > 0 ? self::normalizeHost($host) : null;

        return $uri;
    }

    /**
     * @param int|null $port
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function withPort($port): UriInterface
    {
        $uri = clone $this;

        $uri->port = $port;

        return $uri;
    }

    /**
     * @param string $path
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function withPath($path): UriInterface
    {
        $uri = clone $this;

        $uri->path = self::encodePath($path);

        return $uri;
    }

    /**
     * @param string $query
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function withQuery($query): UriInterface
    {
        $uri = clone $this;

        $uri->query = strlen($query) > 0 ? self::encodeQuery($query) : null;

        return $uri;
    }

    /**
     * @param string $fragment
     * @return \Psr\Http\Message\UriInterface
     */
    public function withFragment($fragment): UriInterface
    {
        $uri = clone $this;

        $uri->fragment = strlen($fragment) > 0 ? self::encodeFragment($fragment) : null;

        return $uri;
    }

    /**
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
}
