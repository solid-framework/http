<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class StringStream implements StreamInterface
{
    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $content;

    /**
     * @internal
     * @since 0.1.0
     * @var int
     */
    protected $pointer;

    /**
     * @internal
     * @since 0.1.0
     * @var bool
     */
    protected $readable;

    /**
     * @internal
     * @since 0.1.0
     * @var bool
     */
    protected $writable;

    /**
     * @internal
     * @since 0.1.0
     * @var bool
     */
    protected $seekable;

    /**
     * @api
     * @since 0.1.0
     * @param string $content The content string to wrap.
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->pointer = 0;
        $this->readable = true;
        $this->writable = true;
        $this->seekable = true;
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
     * @return void
     */
    public function close()
    {
        $this->content = '';
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
    }

    /**
     * @api
     * @since 0.1.0
     * @return resource|null
     */
    public function detach()
    {
        $this->close();

        return null;
    }

    /**
     * @api
     * @since 0.1.0
     * @return int|null
     */
    public function getSize(): int
    {
        return strlen($this->content);
    }

    /**
     * @api
     * @since 0.1.0
     * @return int
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
        return $this->pointer >= (strlen($this->content) - 1);
    }

    /**
     * @api
     * @since 0.1.0
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int $offset The stream offset.
     * @param int $whence The seek method to use.
     * @return void
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->pointer = $offset;
                break;
            case SEEK_CUR:
                $this->pointer += $offset;
                break;
            case SEEK_END:
                $this->pointer = $this->getSize() + $offset;
                break;
            default:
                throw new \RuntimeException("The given whence must be one of: SEEK_SET, SEEK_CUR or SEEK_END");
                break;
        }
    }

    /**
     * @api
     * @since 0.1.0
     * @return void
     */
    public function rewind()
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
        return $this->writable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $string The string that is to be written.
     * @return int
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
        return $this->readable;
    }

    /**
     * @api
     * @since 0.1.0
     * @param int $length The amount of bytes to read.
     * @return string
     */
    public function read($length): string
    {
        return $this->getSize() > 0 ? substr($this->content, $this->pointer, $length) : '';
    }

    /**
     * @api
     * @since 0.1.0
     * @return string
     */
    public function getContents(): string
    {
        return substr($this->content, $this->pointer, $this->getSize());
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'timed_out' => false,
            'blocked' => false,
            'eof' => $this->eof(),
            'unread_bytes' => strlen($this->getContents()),
            'stream_type' => 'string',
            'wrapper_type' => 'php://',
            'wrapp_data' => null,
            'mode' => sprintf(
                '%s%s',
                $this->isReadable() ? 'r' : '',
                $this->isWritable() ? '+' : ''
            ),
            'seekable' => true,
            'uri' => ''
        ];

        return is_null($key) ? $metadata : $metadata[$key] ?? null;
    }
}
