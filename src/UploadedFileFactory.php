<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param \Interop\Http\Factory\StreamFactoryInterface $streamFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param resource|string $file
     * @param int|null        $size
     * @param int             $error
     * @param string|null     $clientFilename
     * @param string|null     $clientMediaType
     * @return \Psr\Http\Message\UploadedFileInterface
     * @throws \InvalidArgumentException
     */
    public function createUploadedFile(
        $file,
        $size = null,
        $error = UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    ): UploadedFileInterface {
        if (!is_resource($file)) {
            $file = $this->createTemporaryResource($file);
        }

        if (!$this->fileIsReadable($file)) {
            throw new InvalidArgumentException('The file is not readable');
        }

        if (is_null($size)) {
            $size = $this->getFileSize($file);
        }

        return new UploadedFile(
            $file,
            $size,
            new UploadedFileError($error),
            $clientFilename,
            $clientMediaType,
            $this->streamFactory
        );
    }

    /**
     * @param $file
     * @return resource
     */
    protected function createTemporaryResource($file)
    {
        $resource = tmpfile();

        fwrite($resource, $file, strlen($file));
        rewind($resource);

        return $resource;
    }

    /**
     * @param resource $resource
     * @return int
     */
    protected function getFileSize($resource): int
    {
        return fstat($resource)['size'];
    }

    /**
     * @param resource $file
     * @return bool
     */
    protected function fileIsReadable($file): bool
    {
        $metaData = stream_get_meta_data($file);

        return in_array($metaData['mode'], StreamMode::readable(), true);
    }
}
