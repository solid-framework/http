<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Solid\Collection\CollectionInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $protocolVersion;

    /**
     * @var \Solid\Collection\CollectionInterface
     */
    protected $headers;

    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $body;

    /**
     * @param string                                $protocolVersion
     * @param \Solid\Collection\CollectionInterface $headers
     * @param \Psr\Http\Message\StreamInterface     $body
     */
    public function __construct(string $protocolVersion, CollectionInterface $headers, StreamInterface $body)
    {
        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;

        if (strlen($this->getHeaderLine('Content-Length')) === 0) {
            $this->headers->set($this->getHeaderKey('Content-Length') ?? 'Content-Length', [$body->getSize() ?? 0]);
        }
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $version
     * @return \Solid\Http\Message
     */
    public function withProtocolVersion($version): Message
    {
        $message = clone $this;

        $message->protocolVersion = $version;

        return $message;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return !is_null($this->getHeaderKey($name));
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader($name): array
    {
        return (array)$this->headers->get($this->getHeaderKey($name) ?? $name, []);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return implode(',', (array)$this->headers->get($this->getHeaderKey($name) ?? $name, []));
    }

    /**
     * @param string       $name
     * @param string|array $value
     * @return \Solid\Http\Message
     * @throws \InvalidArgumentException
     */
    public function withHeader($name, $value): Message
    {
        if (!$this->isValidHeaderName($name)) {
            throw new InvalidArgumentException('Invalid header name:' . $name);
        }

        if (!$this->isValidHeaderValue($value)) {
            throw new InvalidArgumentException('Invalid header value:' . $value);
        }

        $message = clone $this;

        $message->headers->set($this->getHeaderKey($name) ?? $name, (array)$value);

        return $message;
    }

    /**
     * @param string       $name
     * @param string|array $value
     * @return \Solid\Http\Message
     * @throws \InvalidArgumentException
     */
    public function withAddedHeader($name, $value): Message
    {
        if (!$this->isValidHeaderName($name)) {
            throw new InvalidArgumentException('Invalid header name:' . $name);
        }

        if (!$this->isValidHeaderValue($value)) {
            throw new InvalidArgumentException('Invalid header value:' . $value);
        }

        $message = clone $this;

        $header = $message->getHeader($name);

        foreach ((array)$value as $v) {
            $header[] = $v;
        }

        $message->headers->set($this->getHeaderKey($name) ?? $name, $header);

        return $message;
    }

    /**
     * @param string $name
     * @return \Solid\Http\Message
     */
    public function withoutHeader($name): Message
    {
        $message = clone $this;

        $message->headers->remove($this->getHeaderKey($name));

        return $message;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @param \Psr\Http\Message\StreamInterface $body
     * @return \Solid\Http\Message
     * @throws \InvalidArgumentException
     */
    public function withBody(StreamInterface $body): Message
    {
        $message = clone $this;

        $message->body = $body;
        $message->headers->set($this->getHeaderKey('Content-Length') ?? 'Content-Length', $body->getSize() ?? 0);

        return $message;
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function getHeaderKey(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === strtolower($name)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isValidHeaderName(string $name): bool
    {
        return ctype_print($name);
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function isValidHeaderValue(string $value): bool
    {
        return strpos($value, PHP_EOL) === false;
    }
}
