<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Solid\Kernel\Request as KernelRequest;
use Solid\Kernel\RequestInterface as KernelRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Request extends Message implements KernelRequestInterface, RequestInterface
{
    /**
     * @api
     * @since 0.1.0
     * @var array
     */
    const SUPPORTED_METHODS = [
        'HEAD',
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'TRACE',
        'OPTIONS',
        'CONNECT',
        'PATCH'
    ];

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $method;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $protocolVersion;

    /**
     * @internal
     * @since 0.1.0
     * @var HeaderContainer
     */
    protected $headers;

    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $target;

    /**
     * @internal
     * @since 0.1.0
     * @var UriInterface
     */
    protected $uri;

    /**
     * @internal
     * @since 0.1.0
     * @var StreamInterface
     */
    protected $body;

    /**
     * @api
     * @since 0.1.0
     * @param string|null          $method  The request method to use.
     * @param UriInterface|null    $uri     The request uri to use.
     * @param HeaderContainer|null $headers The request headers to use.
     * @param StreamInterface|null $body    The request body to use.
     */
    public function __construct(
        string $method = null,
        UriInterface $uri = null,
        HeaderContainer $headers = null,
        StreamInterface $body = null
    ) {
        parent::__construct('1.1', $headers ?? new HeaderContainer, $body ?? new StringStream);

        $this->method = $method;
        $this->uri = $uri ?? new Uri;

        if (strlen($this->uri->getHost()) > 0 && !$this->headers->has('Host')) {
            $this->headers->set('Host', $this->uri->getHost());
        }
    }

    /**
     * @api
     * @since 0.1.0
     */
    public function __clone()
    {
        parent::__clone();
        $this->uri = clone $this->uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString()
    {
        return <<<REQUEST
{$this->getMethod()} {$this->getRequestTarget()} HTTP/{$this->getProtocolVersion()}
{$this->headers}

{$this->getBody()}
REQUEST;
    }

    /**
     * @api
     * @since 0.1.0
     * @param KernelRequest $request A kernel request to generate the implementation specific request from.
     * @return KernelRequestInterface
     */
    public static function fromKernelRequest(KernelRequest $request): KernelRequestInterface
    {
        $serverParameters = self::normalizeServerParameters($request->getServerParameters());

        $userInfo = $serverParameters['username'];
        $host = $serverParameters['host'];

        if (!is_null($serverParameters['port'])) {
            $host .= ':' . $serverParameters['port'];
        }

        if (!empty($userInfo)) {
            if (!empty($serverParameters['password'])) {
                $userInfo .= ':' . $serverParameters['password'];
            }

            $userInfo .= '@';
        }

        $body = '';

        if (in_array(strtolower($serverParameters['method']), ['post', 'put'])) {
            $body = file_get_contents('php://input');

            if ($body === false || strlen($body) === 0) {
                $body = http_build_query($request->getRequestParameters(), null, '&', PHP_QUERY_RFC3986);
            }
        }

        return new self(
            $serverParameters['method'],
            new Uri(
                $serverParameters['scheme'] . '://' .
                $userInfo .
                $host .
                $serverParameters['path']
            ),
            new HeaderContainer($request->getHeaderParameters()),
            new StringStream($body)
        );
    }

    /**
     * @internal
     * @since 0.1.0
     * @param array $serverParameters The server parameters to normalize.
     * @return array
     */
    private static function normalizeServerParameters(array $serverParameters): array
    {
        $method = $serverParameters['REQUEST_METHOD'] ?? '';
        $port = $serverParameters['SERVER_PORT'] ?? null;

        $scheme = 'http';

        if (array_key_exists('REQUEST_SCHEME', $serverParameters)) {
            $scheme = $serverParameters['REQUEST_SCHEME'];
        } elseif ((array_key_exists('HTTPS', $serverParameters) && $serverParameters['HTTPS'] === 'on') || $port === 443) {
            $scheme = 'https';
        }

        $username = $serverParameters['PHP_AUTH_USER'] ?? '';
        $password = $serverParameters['PHP_AUTH_PW'] ?? '';
        $host = $serverParameters['HTTP_HOST'] ?? '';
        $path = $serverParameters['REQUEST_URI'] ?? '';

        return compact('method', 'scheme', 'host', 'port', 'path', 'username', 'password');
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

        return strlen($path) > 0 ? $path : '/';
    }

    /**
     * @api
     * @since 0.1.0
     * @param mixed $requestTarget The new request target to use.
     * @return self
     */
    public function withRequestTarget($requestTarget): self
    {
        $newRequest = clone $this;
        $newRequest->target = $requestTarget;

        return $newRequest;
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
     * @param string $method The new request method to use.
     * @return self
     * @throws InvalidArgumentException
     */
    public function withMethod($method): self
    {
        if (!in_array(strtolower($method), array_map('strtolower', self::SUPPORTED_METHODS))) {
            throw new InvalidArgumentException('Unsupported request method: ' . $method);
        }

        $newRequest = clone $this;
        $newRequest->method = $method;

        return $newRequest;
    }

    /**
     * @api
     * @since 0.1.0
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @api
     * @since 0.1.0
     * @param UriInterface $uri          The new request uri to use.
     * @param bool         $preserveHost Whether to preserve the original host.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $newRequest = clone $this;
        $newRequest->uri = $uri;

        $host = $newRequest->getUri()->getHost();

        if (strlen($host) > 0) {
            if (!$preserveHost || !$newRequest->headers->has('Host')) {
                $newRequest->headers->set('Host', $host);
            }
        }

        return $newRequest;
    }
}
