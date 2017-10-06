<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\StreamFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * @var resource
     */
    protected $file;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var \Solid\Http\UploadedFileError
     */
    protected $error;

    /**
     * @var string|null
     */
    protected $clientFilename;

    /**
     * @var string|null
     */
    protected $clientMediaType;

    /**
     * @var bool
     */
    protected $hasBeenMoved = false;

    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param resource                                     $file
     * @param int                                          $size
     * @param \Solid\Http\UploadedFileError                $error
     * @param string|null                                  $clientFilename
     * @param string|null                                  $clientMediaType
     * @param \Interop\Http\Factory\StreamFactoryInterface $streamFactory
     */
    public function __construct(
        $file,
        int $size,
        UploadedFileError $error,
        ?string $clientFilename,
        ?string $clientMediaType,
        StreamFactoryInterface $streamFactory
    ) {
        $this->file = $file;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     * @throws \RuntimeException
     */
    public function getStream(): StreamInterface
    {
        if ($this->hasBeenMoved) {
            throw new RuntimeException('Uploaded file has been moved');
        }

        return $this->streamFactory->createStreamFromResource($this->file);
    }

    /**
     * @param string $targetPath
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function moveTo($targetPath): void
    {
        if ($this->hasBeenMoved) {
            throw new RuntimeException('Uploaded file has already been moved');
        }

        if (($targetFile = @fopen($targetPath, 'w')) === false) {
            throw new InvalidArgumentException('The given target path is not writable');
        }

        stream_copy_to_stream($this->file, $targetFile);

        $metaData = stream_get_meta_data($this->file);
        fclose($this->file);

        if (file_exists($metaData['uri'])) {
            unlink($metaData['uri']);
        }

        $this->hasBeenMoved = true;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error->getValue();
    }

    /**
     * @return string|null
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * @return string|null
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
