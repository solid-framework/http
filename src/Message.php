<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Message implements MessageInterface
{
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
     * @var StreamInterface
     */
    protected $body;

    /**
     * @api
     * @since 0.1.0
     * @param string|null          $protocolVersion  The protocol version to use.
     * @param HeaderContainer|null $headers          The request headers to use.
     * @param StreamInterface|null $body             The request body to use.
     */
    public function __construct(
        $protocolVersion = null,
        HeaderContainer $headers = null,
        StreamInterface $body = null
    ) {
        $this->protocolVersion = (string) ($protocolVersion ?? '1.1');
        $this->headers = $headers ?? new HeaderContainer;
        $this->body = $body ?? new StringStream;

        $this->headers->set('Content-Length', $this->body->getSize());
    }

    /**
     * @api
     * @since 0.1.0
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version): self
    {
        $newMessage = clone $this;
        $newMessage->protocolVersion = $version;

        return $newMessage;
    }

    /**
     * @api
     * @since 0.1.0
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers->get();
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name The header to check for.
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name The header to get.
     * @return array
     */
    public function getHeader($name): array
    {
        return $this->headers->get($name);
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name The header to get.
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->headers->get($name));
    }

    /**
     * @api
     * @since 0.1.0
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws InvalidArgumentException
     */
    public function withHeader($name, $value): self
    {
        $newMessage = clone $this;
        $newMessage->headers->set($name, $value);

        return $newMessage;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws InvalidArgumentException
     */
    public function withAddedHeader($name, $value): self
    {
        $newMessage = clone $this;
        $newMessage->headers->add($name, $value);

        return $newMessage;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $name The header to remove.
     * @return self
     */
    public function withoutHeader($name): self
    {
        $newMessage = clone $this;
        $newMessage->headers->remove($name);

        return $newMessage;
    }

    /**
     * @api
     * @since 0.1.0
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @api
     * @since 0.1.0
     * @param StreamInterface $body The new body to use.
     * @return self
     * @throws InvalidArgumentException
     */
    public function withBody(StreamInterface $body): self
    {
        $newMessage = clone $this;
        $newMessage->body = $body;
        $newMessage->headers->set('Content-Length', $body->getSize());

        return $newMessage;
    }
}
