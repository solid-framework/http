<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Solid\Collection\CollectionInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Request extends Message implements RequestInterface
{
    /**
     * @since 0.1.0
     * @var string
     */
    protected $method;

    /**
     * @since 0.1.0
     * @var \Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * @since 0.1.0
     * @var string
     */
    protected $target;

    /**
     * @api
     * @since 0.1.0
     * @param string                                $method
     * @param \Psr\Http\Message\UriInterface        $uri
     * @param string                                $protocolVersion
     * @param \Solid\Collection\CollectionInterface $headers
     * @param \Psr\Http\Message\StreamInterface     $body
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        string $protocolVersion,
        CollectionInterface $headers,
        StreamInterface $body
    ) {
        parent::__construct($protocolVersion, $headers, $body);

        $host = $uri->getHost();

        if (strlen($host) > 0 && strlen($this->getheaderLine('Host')) === 0) {
            $headers->set('Host', [$host]);
        }

        $this->method = $method;
        $this->uri = $uri;
    }

    /**
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        $request = "{$this->getMethod()} {$this->getRequestTarget()} HTTP/{$this->getProtocolVersion()}\n";

        foreach ($this->getHeaders() as $name => $values) {
            $headerLine = implode(',', $values);
            $request .= "{$name}: {$headerLine}\n";
        }

        $request .= "\n{$this->getBody()}";

        return $request;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getRequestTarget(): string
    {
        if (!is_null($this->target)) {
            return $this->target;
        }

        $path = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if (strlen($query) > 0) {
            $path .= '?' . $query;
        }

        return $path;
    }

    /**
     * @api
     * @param mixed $requestTarget
     * @return \Solid\Http\Request
     */
    public function withRequestTarget($requestTarget): Request
    {
        $request = clone $this;

        $request->target = $requestTarget;

        return $request;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $method
     * @return \Solid\Http\Request
     * @throws \InvalidArgumentException
     */
    public function withMethod($method): Request
    {
        if (!in_array(strtolower($method), array_map('strtolower', RequestMethods::values()))) {
            throw new InvalidArgumentException("The method: {$method} is not a valid HTTP request method");
        }

        $request = clone $this;

        $request->method = $method;

        return $request;
    }

    /**
     * @api
     * @since 0.1.0
     * @return \Psr\Http\Message\UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param \Psr\Http\Message\UriInterface $uri
     * @param bool                           $preserveHost
     * @return Request
     */
    public function withUri(UriInterface $uri, $preserveHost = false): Request
    {
        $request = clone $this;

        $request->uri = $uri;

        $host = $uri->getHost();

        if (strlen($host) > 0) {
            if (!$preserveHost || ($preserveHost && strlen($this->getHeaderLine('Host')) === 0)) {
                $request->headers->set('Host', [$uri->getHost()]);
            }
        }

        return $request;
    }
}
