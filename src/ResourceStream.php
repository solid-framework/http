<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class ResourceStream implements StreamInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $metaData;

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
     * @var array
     */
    protected static $readableModes = [
        'r',
        'r+',
        'w+',
        'a+',
        'x+',
        'c+'
    ];

    /**
     * @var array
     */
    protected static $writableModes = [
        'r+',
        'w',
        'w+',
        'a',
        'a+',
        'x',
        'x+',
        'c',
        'c+'
    ];

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Expected a valid resource');
        }

        $metaData = stream_get_meta_data($resource);

        $this->resource = $resource;
        $this->isReadable = in_array($metaData['mode'], self::$readableModes, true);
        $this->isWritable = in_array($metaData['mode'], self::$writableModes, true);
        $this->isSeekable = $metaData['seekable'];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->read($this->getSize());
        } catch (RuntimeException $exception) {
            return '';
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if (is_resource($resource = $this->detach())) {
            fclose($resource);
        }
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $resource = $this->resource;

        $this->resource = null;
        $this->isReadable = false;
        $this->isWritable = false;
        $this->isSeekable = false;

        return $resource;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return is_resource($this->resource) ? fstat($this->resource)['size'] : null;
    }

    /**
     * @return int
     * @throws \RuntimeException on error.
     */
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException();
        }

        return (int)ftell($this->resource);
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
        if (!$this->isSeekable()) {
            throw new RuntimeException('The resource does not support seeking');
        }

        if (@fseek($this->resource, $offset, $whence) !== 0) {
            throw new RuntimeException('Unable to seek resource');
        }
    }

    /**
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Could not rewind resource: resource is not seekable');
        }

        $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable()
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
        $bytesToWrite = strlen($string);

        if (($bytesWritten = @fwrite($this->resource, $string, $bytesToWrite)) !== $bytesToWrite) {
            throw new RuntimeException('Could not write to resource');
        }

        return $bytesWritten;
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
        if ($length <= 0) {
            throw new RuntimeException('Length must be greater than 0');
        }

        if (strlen($content = @fread($this->resource, $length)) !== $length) {
            throw new RuntimeException('Could not read from resource');
        }

        return $content;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function getContents(): string
    {
        $remainingContentSize = $this->getSize() - $this->tell();

        if (strlen($content = @fread($this->resource, $remainingContentSize)) !== $remainingContentSize) {
            throw new RuntimeException('Could not read from resource');
        }

        return $content;
    }

    /**
     * @param string|null $key Specific metadata to retrieve.
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if (!is_resource($this->resource)) {
            return is_null($key) ? [] : null;
        }

        $metaData = stream_get_meta_data($this->resource);

        return is_null($key) ?
            $metaData :
            (array_key_exists($key, $metaData) ? $metaData[$key] : null);
    }
}
