<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Uri implements UriInterface
{
    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $scheme;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $username;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $password;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $host;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $path;

    /**
     * @internal
     * @since 0.1.0
     * @var int
     */
    protected $port;

    /**
     * @internal
     * @since 0.1.0
     * @var QueryContainer
     */
    protected $query;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $fragment;

    /**
     * @api
     * @since 0.1.0
     * @param string|null $url A well formated url.
     */
    public function __construct(string $url = null)
    {
        // set a default protocol so that parse_url behaves correctly
        if (false === strpos($url, '://')) {
            $url = 'parse-url://' . $url;
        }

        $urlParts = parse_url($url);

        $this->scheme = $urlParts['scheme'] !== 'parse-url' ? $urlParts['scheme'] : null;
        $this->username = $urlParts['user'] ?? null;
        $this->password = $urlParts['pass'] ?? null;
        $this->host = $urlParts['host'] ?? null;
        $this->path = $urlParts['path'] ?? null;
        $this->fragment = $urlParts['fragment'] ?? null;

        $port = $urlParts['port'] ?? null;

        if (!is_null($port) && !$this->checkPortRange($port)) {
            throw new InvalidArgumentException(
                "The given port \"{$port}\" is not within the established TCP/UDP port range"
            );
        }

        $this->port = $port;
        $this->query = new QueryContainer($urlParts['query'] ?? null);
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getScheme(): string
    {
        return !is_null($this->scheme) ? strtolower($this->scheme) : '';
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = '';

        // check required components
        if (!is_null($this->host)) {
            $userInfo = $this->getUserInfo();

            if (strlen($userInfo) > 0) {
                $authority .= $userInfo . '@';
            }

            $authority .= $this->host;

            if (!is_null($port = $this->getPort())) {
                $authority .= ':' . $port;
            }
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
        $userInfo = '';

        if (!is_null($this->username)) {
            $userInfo .= $this->username;

            if (!is_null($this->password)) {
                $userInfo .= ':' . $this->password;
            }
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
        return !is_null($this->host) ? strtolower($this->host) : '';
    }

    /**
     * @api
     * @since 0.1.0
     * @return int|null
     */
    public function getPort()
    {
        return (!is_null($this->port) && !$this->isStandardPort($this->port)) ? $this->port : null;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getPath(): string
    {
        return !is_null($this->path) ?
            // decode the path before encoding to avoid double encoding
            implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', $this->path)))) :
            '';
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getQuery(): string
    {
        return (string) $this->query;
    }

    /**
     * @api
     * @since 0.1.0
     * @return QueryContainer
     */
    public function getQueryContainer(): QueryContainer
    {
        return $this->query;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getFragment(): string
    {
        return !is_null($this->fragment) ? rawurlencode(rawurldecode($this->fragment)) : '';
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $scheme The new scheme to use.
     * @return Uri
     */
    public function withScheme($scheme): self
    {
        $newUrl = clone $this;

        $newUrl->scheme = $scheme ?? null;

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string      $user     The new user to use.
     * @param string|null $password The new password to use.
     * @return Uri
     */
    public function withUserInfo($user, $password = null): self
    {
        $newUrl = clone $this;

        $newUrl->username = $user ?? null;
        $newUrl->password = $password;

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $host The new host to use.
     * @return Uri
     */
    public function withHost($host): self
    {
        $newUrl = clone $this;

        $newUrl->host = $host ?? null;

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int|null $port The new port to use.
     * @return Uri
     * @throws InvalidArgumentException
     */
    public function withPort($port): self
    {
        if (!is_null($port) && !$this->checkPortRange($port)) {
            throw new InvalidArgumentException("The given port: {$port} is not supported");
        }

        $newUrl = clone $this;

        $newUrl->port = $port;

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $path The new path to use.
     * @return Uri
     */
    public function withPath($path): self
    {
        $newUrl = clone $this;

        $newUrl->path = $path;

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $query The new query to use.
     * @return Uri
     */
    public function withQuery($query): self
    {
        $newUrl = clone $this;

        $newUrl->query = new QueryContainer($query);

        return $newUrl;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $fragment The new fragment to use.
     * @return Uri
     */
    public function withFragment($fragment): self
    {
        $newUrl = clone $this;

        $newUrl->fragment = !empty($fragment) ? $fragment : null;

        return $newUrl;
    }

    /**
     * Returns true if the given port is within the established TCP and UDP port ranges
     *
     * @internal
     * @since 0.1.0
     * @param int $port The port to check.
     * @return bool
     */
    protected function checkPortRange(int $port): bool
    {
        return
            $port >= 1 && $port <= 223 ||
            $port >= 242 && $port <= 246 ||
            $port >= 256 && $port <= 600 ||
            $port >= 606 && $port <= 611 ||
            $port >= 747 && $port <= 754 ||
            $port >= 758 && $port <= 765 ||
            $port >= 769 && $port <= 776 ||
            $port >= 996 && $port <= 1000 ||
            $port >= 1030 && $port <= 1032 ||
            $port >= 1067 && $port <= 1068 ||
            $port >= 1083 && $port <= 1084 ||
            $port >= 1346 && $port <= 1527 ||
            $port >= 1650 && $port <= 1655 ||
            $port >= 1661 && $port <= 1666 ||
            $port >= 1986 && $port <= 2002 ||
            $port >= 2004 && $port <= 2028 ||
            $port >= 2032 && $port <= 2035 ||
            $port >= 2040 && $port <= 2049 ||
            $port >= 2500 && $port <= 2501 ||
            $port >= 3984 && $port <= 3986 ||
            $port >= 4132 && $port <= 4133 ||
            $port >= 5300 && $port <= 5305 ||
            $port >= 6000 && $port <= 6063 ||
            $port >= 6141 && $port <= 6147 ||
            $port >= 7000 && $port <= 7010 ||
            in_array($port, [
                634, 666, 704, 709, 729, 730, 731, 741, 742, 744, 767, 780, 786,
                800, 801, 1025, 1080, 1155, 1222, 1248, 1529, 1600, 2030, 2038,
                2065, 2067, 2201, 2564, 2784, 3049, 3264, 3333, 3421, 3900, 4343,
                4444, 4672, 5000, 5001, 5002, 5010, 5011, 5050, 5145, 5190, 5236,
                6111, 6558, 7100, 7200, 9535, 17007
            ]);
    }

    /**
     * @internal
     * @since 0.1.0
     * @param int $port The port to check.
     * @return bool
     */
    protected function isStandardPort(int $port): bool
    {
        switch ($this->scheme) {
            case 'http':
                return $port === 80;
                break;
            case 'https':
                return $port === 443;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        $url = '';

        $scheme = $this->getScheme();

        if (!empty($scheme)) {
            $url .= $scheme . ':';
        }

        $authority = $this->getAuthority();

        if (strlen($authority) > 0) {
            $url .= '//' . $authority;
        }

        $path = $this->getPath();

        if (!is_null($path) && strlen($path) > 0) {
            if (strpos($path, '/') !== 0 && strlen($authority) > 0) {
                $path = '/' . $path;
            } elseif (substr($path, 0, 2) === '//' && strlen($authority) === 0) {
                $path = '/' . ltrim($path, '/');
            }

            $url .= $path;
        }

        $query = $this->getQuery();

        if (strlen($query) > 0) {
            $url .= '?' . $query;
        }

        $fragment = $this->getFragment();

        if (strlen($fragment) > 0) {
            $url .= '#' . $fragment;
        }

        return $url;
    }
}
