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
 */
class StringStream implements StreamInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var int
     */
    protected $pointer;

    /**
     * @var bool
     */
    protected $isReadable;

    /**
     * @var bool
     */
    protected $isWritable;

    /**
     * @var bool
     */
    protected $isSeekable;

    /**
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
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void
    {
        $this->content = '';
        $this->pointer = 0;
        $this->isReadable = false;
        $this->isWritable = false;
        $this->isSeekable = false;
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $this->close();

        return null;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return $this->pointer;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return $this->tell() === $this->getSize();
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->isSeekable;
    }

    /**
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
     * @throws \RuntimeException
     */
    public function rewind(): void
    {
        $this->pointer = 0;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    /**
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
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    /**
     * @param int $length
     * @return string
     * @throws \RuntimeException
     */
    public function read($length): string
    {
        return substr($this->content, $this->pointer, $length);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        return substr($this->content, $this->pointer);
    }

    /**
     * @param string|null $key
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

            // The stream is either read/write or detached/closed
            'mode' => $this->isReadable() && $this->isWritable() ? 'r+' : '',
            'seekable' => $this->isSeekable(),
            'uri' => ''
        ];

        return is_null($key) ?
            $metadata :
            (array_key_exists($key, $metadata) ? $metadata[$key] : null);
    }
}
