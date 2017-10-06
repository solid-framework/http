<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Solid\Http\StreamMode;
use Solid\Http\UploadedFileFactory;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\UploadedFileFactory
 */
class UploadedFileFactoryTest extends TestCase
{
    /**
     * @var string
     */
    protected static $fileName = 'filename.txt';

    /**
     * @var string
     */
    protected static $fileNameFull = __DIR__ . '/Fixtures/filename.txt';

    /**
     * @var string
     */
    protected static $fileTmpName = __DIR__ . '/Fixtures/tmp-filename.txt';

    /**
     * @var string
     */
    protected static $fileType = 'text/plain';

    /**
     * @var string
     */
    protected static $fileContent = 'Uploaded file content';

    /**
     * @var int
     */
    protected static $fileSize = 21;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamFactoryMock;

    /**
     * @var \Solid\Http\UploadedFileFactory
     */
    protected $uploadedFileFactory;

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();

        $this->streamFactoryMock = $this->getMockBuilder(StreamFactoryInterface::class)
                                        ->getMock();

        /** @noinspection PhpParamsInspection */
        $this->uploadedFileFactory = new UploadedFileFactory($this->streamFactoryMock);

        $resource = fopen(self::$fileTmpName, 'w');
        fwrite($resource, self::$fileContent, self::$fileSize);
        fclose($resource);
    }

    /**
     * @after
     */
    public function tearDown()
    {
        parent::tearDown();

        @unlink(self::$fileTmpName);
        @unlink(__DIR__ . '/Fixtures/' . self::$fileName);
    }
    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrUploadedFileFactoryInterface(): void
    {
        $this->assertContains(UploadedFileFactoryInterface::class, class_implements(UploadedFileFactory::class));
    }

    /**
     * @test
     * @covers ::createUploadedFile
     * @covers ::fileIsReadable
     * @covers ::__construct
     */
    public function shouldCreateUploadedFileFromResource(): void
    {
        $file = fopen(self::$fileTmpName, StreamMode::R);
        $uploadedFile = $this->uploadedFileFactory->createUploadedFile(
            $file,
            self::$fileSize,
            UPLOAD_ERR_OK,
            self::$fileName,
            self::$fileType
        );

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame(self::$fileSize, $uploadedFile->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame(self::$fileName, $uploadedFile->getClientFilename());
        $this->assertSame(self::$fileType, $uploadedFile->getClientMediaType());
    }

    /**
     * @test
     * @covers ::createUploadedFile
     * @covers ::createTemporaryResource
     * @covers ::__construct
     */
    public function shouldCreateUploadedFileFromString(): void
    {
        $fileContent = 'This is the file content';
        $uploadedFile = $this->uploadedFileFactory->createUploadedFile(
            $fileContent,
            strlen($fileContent),
            UPLOAD_ERR_OK,
            null,
            null
        );

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());
    }

    /**
     * @test
     * @covers ::createUploadedFile
     * @covers ::getFileSize
     */
    public function shouldDetermineFileSizeIfNoneIsGiven(): void
    {
        $file = fopen(self::$fileTmpName, StreamMode::R);
        $uploadedFile = $this->uploadedFileFactory->createUploadedFile(
            $file,
            null,
            UPLOAD_ERR_OK,
            null,
            null
        );

        $this->assertSame(self::$fileSize, $uploadedFile->getSize());
    }

	/**
	 * @test
	 * @covers ::createUploadedFile
	 * @expectedException \InvalidArgumentException
	 */
    public function shouldThrowExceptionIfResourceIsNotReadable(): void
    {
	    $file = fopen(self::$fileTmpName, StreamMode::W);

	    $this->uploadedFileFactory->createUploadedFile(
		    $file,
		    null,
		    UPLOAD_ERR_OK,
		    null,
		    null
	    );
    }
}
