<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\StreamFactoryInterface;
use phpmock\phpunit\PHPMock;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Solid\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Solid\Http\UploadedFileError;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\UploadedFile
 */
class UploadedFileTest extends TestCase
{
    use PHPMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamMock;

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
     * @var array
     */
    protected $file;

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();

        $this->file = [
            'name' => self::$fileName,
            'type' => self::$fileType,
            'size' => self::$fileSize,
            'tmp_name' => self::$fileTmpName,
            'error' => UPLOAD_ERR_OK
        ];
        $this->streamFactoryMock = $this->getMockBuilder(StreamFactoryInterface::class)
                                    ->setMethods([
                                        'createStream',
                                        'createStreamFromFile',
                                        'createStreamFromResource'
                                    ])
                                    ->getMock();
        $this->streamMock = $this->getMockBuilder(StreamInterface::class)
                                 ->getMock();

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
    public function shouldImplementPsrUploadedFileInterface(): void
    {
        $this->assertContains(UploadedFileInterface::class, class_implements(UploadedFile::class));
    }

    /**
     * @test
     * @covers ::getStream
     * @covers ::__construct
     */
    public function shouldReturnStream(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->streamFactoryMock->expects($this->once())
                                ->method('createStreamFromResource')
                                ->with($file)
                                ->willReturn($this->streamMock);

        $stream = $uploadedFile->getStream();

        $this->assertSame($this->streamMock, $stream);
    }

    /**
     * @test
     * @covers ::getStream
     * @expectedException \RuntimeException
     */
    public function getStreamShouldThrowExceptionIfFileHasBeenMoved(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $uploadedFile->moveTo(__DIR__ . '/Fixtures/' . self::$fileName);
        $uploadedFile->getStream();
    }

    /**
     * @test
     * @covers ::moveTo
     */
    public function shouldMoveTheUploadedFileToTargetPath(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->assertFalse(file_exists(self::$fileNameFull));

        $uploadedFile->moveTo(self::$fileNameFull);

        $this->assertTrue(file_exists(self::$fileNameFull));
        $this->assertSame(self::$fileContent, file_get_contents(self::$fileNameFull));
    }

    /**
     * @test
     * @covers ::moveTo
     */
    public function moveToShouldRemoveTemporaryFileAfterMove(): void
    {
        $this->assertTrue(file_exists(self::$fileTmpName));

        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $uploadedFile->moveTo(self::$fileNameFull);

        $this->assertFalse(file_exists(self::$fileTmpName));
    }

    /**
     * @test
     * @covers ::moveTo
     * @expectedException \RuntimeException
     */
    public function moveToShouldThrowExceptionOnSuccessiveCalls(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $uploadedFile->moveTo(self::$fileNameFull);
        $uploadedFile->moveTo(self::$fileNameFull);
    }

    /**
     * @test
     * @covers ::moveTo
     * @expectedException \InvalidArgumentException
     */
    public function moveToShouldThrowExceptionIfTargetPathIsNotWritable(): void
    {
        $this->markTestSkipped(
            'Mocking fopen fails because UploadedFile uses it in previous tests, this test works fine on its own'
        );

        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $fopen = $this->getFunctionMock('Solid\Http', 'fopen');
        $fopen->expects($this->once())
              ->willReturn(false);

        $uploadedFile->moveTo(self::$fileNameFull);
    }

    /**
     * @test
     * @covers ::getSize
     */
    public function shouldReturnFileSize(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->assertSame(self::$fileSize, $uploadedFile->getSize());
    }

    /**
     * @test
     * @covers ::getError
     */
    public function shouldReturnFileUploadError(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
    }

    /**
     * @test
     * @covers ::getClientFilename
     */
    public function shouldGetClientFilename(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            self::$fileName,
            null,
            $this->streamFactoryMock
        );

        $this->assertSame(self::$fileName, $uploadedFile->getClientFilename());
    }

    /**
     * @test
     * @covers ::getClientFilename
     */
    public function getClientFilenameShouldReturnNullIfThereIsNoFilename(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->assertNull($uploadedFile->getClientFilename());
    }

    /**
     * @test
     * @covers ::getClientMediaType
     */
    public function shouldGetClientMediaType(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            self::$fileType,
            $this->streamFactoryMock
        );

        $this->assertSame(self::$fileType, $uploadedFile->getClientMediaType());
    }

    /**
     * @test
     * @covers ::getClientMediaType
     */
    public function getClientMediaTypeShouldReturnNullIfThereIsNoMediaType(): void
    {
        $file = fopen(self::$fileTmpName, 'r');

        /** @noinspection PhpParamsInspection */
        $uploadedFile = new UploadedFile(
            $file,
            self::$fileSize,
            new UploadedFileError(UPLOAD_ERR_OK),
            null,
            null,
            $this->streamFactoryMock
        );

        $this->assertNull($uploadedFile->getClientMediaType());
    }
}
