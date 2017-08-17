<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class StringStream implements StreamInterface
{
    /**
     * @since 0.1.0
     * @var string
     */
    protected $content;

    /**
     * @since 0.1.0
     * @var int
     */
    protected $pointer;

    /**
     * @since 0.1.0
     * @var bool
     */
    protected $isReadable;

    /**
     * @since 0.1.0
     * @var bool
     */
    protected $isWritable;

    /**
     * @since 0.1.0
     * @var bool
     */
    protected $isSeekable;

    /**
     * @api
     * @since 0.1.0
     * @param string $content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->pointer = 0;
        $this->isReadable = true;
        $this->isWritable = true;
        $this->isSeekable = true;
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * @api
     * @since 0.1.0
     */
    public function close(): void
    {
        $this->content = '';
        $this->isReadable = false;
        $this->isWritable = false;
        $this->isSeekable = false;
    }

    /**
     * @api
     * @since 0.1.0
     * @return resource|null
     */
    public function detach(): ?resource
    {
        $this->close();

        return null;
    }

    /**
     * @api
     * @since 0.1.0
     * @return int|null
     */
    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    /**
     * @api
     * @since 0.1.0
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return $this->pointer;
    }

    /**
     * @api
     * @since 0.1.0
     * @return bool
     */
    public function eof(): bool
    {
        return $this->pointer === $this->getSize();
    }

    /**
     * @api
     * @since 0.1.0
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->isSeekable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int $offset
     * @param int $whence
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        switch ($whence) {
            case SEEK_SET:
                $calculatedOffset = $offset;
                break;
            case SEEK_CUR:
                $calculatedOffset = $this->pointer + $offset;
                break;
            case SEEK_END:
                $calculatedOffset = $this->getSize() + $offset;
                break;
            default:
                throw new RuntimeException('The given whence must be one of: SEEK_SET, SEEK_CUR or SEEK_END');
        }

        if ($calculatedOffset < 0 || $calculatedOffset > $this->getSize()) {
            throw new RuntimeException('The given offset is outside the stream\'s seek range');
        }

        $this->pointer = $calculatedOffset;
    }

    /**
     * @api
     * @since 0.1.0
     * @throws \RuntimeException
     */
    public function rewind(): void
    {
        $this->pointer = 0;
    }

    /**
     * @api
     * @since 0.1.0
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $string
     * @return int
     * @throws \RuntimeException
     */
    public function write($string): int
    {
        $this->content = substr_replace($this->content, $string, $this->pointer, 0);

        return strlen($string);
    }

    /**
     * @api
     * @since 0.1.0
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int $length
     * @return string
     * @throws \RuntimeException
     */
    public function read($length): string
    {
        return substr($this->content, $this->pointer, $length);
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        return substr($this->content, $this->pointer);
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $key
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'timed_out' => false,
            'blocked' => false,
            'eof' => $this->eof(),
            'unread_bytes' => $this->getSize() - $this->pointer,
            'stream_type' => 'string',
            'wrapper_type' => 'php://',
            'wrapper_data' => null,
            'mode' => $this->isReadable() && $this->isWritable() ? 'r+' : '',
            'seekable' => $this->isSeekable(),
            'uri' => ''
        ];

        return is_null($key) ?
            $metadata :
            (array_key_exists($key, $metadata) ? $metadata[$key] : null);
    }
}
