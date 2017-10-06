<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Solid\Http\ResourceStream;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\ResourceStream
 */
class ResourceStreamTest extends TestCase
{
    /**
     * @var string
     */
    private static $resourceStreamFile = __DIR__ . '/Fixtures/resource-stream-file.txt';

    /**
     * @var string
     */
    private static $nonExistingFile = __DIR__ . '/Fixtures/non-existing-file.txt';

    /**
     * @var string
     */
    private static $resourceStreamFileContent = <<<TXT
Line one
Line two
Line three
TXT;

    /**
     * @var int
     */
    private static $resourceStreamFileSize = 28;

    /**
     * @var array
     */
    private static $resourceStreamFileMetadata = [
        'timed_out' => false,
        'blocked' => true,
        'eof' => false,
        'wrapper_type' => 'plainfile',
        'stream_type' => 'STDIO',
        'mode' => 'r',
        'unread_bytes' => 0,
        'seekable' => true,
        'uri' => __DIR__ . '/Fixtures/resource-stream-file.txt'
    ];

    /**
     * @var resource
     */
    private $resource;

    /**
     * @before
     */
    public function setUp(): void
    {
        parent::setUp();

        $resource = fopen(self::$resourceStreamFile, 'w');

        fwrite($resource, self::$resourceStreamFileContent, strlen(self::$resourceStreamFileContent));
        fclose($resource);
    }

    /**
     * @after
     */
    public function tearDown()
    {
        parent::tearDown();

        @fclose($this->resource);
        @unlink(self::$resourceStreamFile);
        @unlink(self::$nonExistingFile);
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrStreamInterface(): void
    {
        $this->assertContains(StreamInterface::class, class_implements(ResourceStream::class));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @covers ::__construct
     */
    public function shouldThrowExceptionIfNoResourceGiven(): void
    {
        new ResourceStream(null);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__toString
     */
    public function shouldReturnTheEntireStreamWhenCastToString(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertSame(self::$resourceStreamFileContent, (string)$resourceStream);
    }

    /**
     * @test
     * @covers ::__toString
     */
    public function shouldNotThrowExceptionWhenCastToString(): void
    {
        $resourceStreamMock = $this->getMockBuilder(ResourceStream::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['rewind', 'read'])
                                   ->getMock();
        $resourceStreamMock->method('read')->willThrowException(new RuntimeException());

        $this->assertSame('', (string)$resourceStreamMock);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::isReadable
     */
    public function shouldBeReadableWhenInitializedWithAReadableResource(): void
    {
        $readableModes = [
            'r',
	        'rt',
	        'rb',

            'r+',
	        'r+t',
	        'r+b',

            'w+',
	        'w+t',
	        'w+b',

            'a+',
	        'a+t',
	        'a+b',

            'c+',
            'c+t',
            'c+b'
        ];

        foreach ($readableModes as $mode) {
            $this->resource = fopen(self::$resourceStreamFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertTrue($resourceStream->isReadable());

            fclose($this->resource);
        }

        // Test mode that error on existing file.
	    $readableModes = [
	    	'x+',
		    'x+t',
		    'x+b'
	    ];

        foreach ($readableModes as $mode) {
	        $this->resource = fopen(self::$nonExistingFile, $mode);
	        $resourceStream = new ResourceStream($this->resource);

	        $this->assertTrue($resourceStream->isReadable());

	        fclose($this->resource);
	        unlink(self::$nonExistingFile);
        }
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::isWritable
     */
    public function shouldBeWritableWhenInitializedWithAWritableResource(): void
    {
        $writableModes = [
            'r+',
	        'r+t',
	        'r+b',

            'w',
	        'wt',
	        'wb',

            'w+',
	        'w+t',
	        'w+b',

            'a',
	        'at',
	        'ab',

            'a+',
	        'a+t',
	        'a+b',

            'c',
	        'ct',
	        'cb',

            'c+',
            'c+t',
            'c+b'
        ];

        foreach ($writableModes as $mode) {
            $this->resource = fopen(self::$resourceStreamFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertTrue($resourceStream->isWritable());

            fclose($this->resource);
        }

        // Test modes that error on existing file.
        $writableModes = [
            'x',
	        'xt',
	        'xb',

            'x+',
            'x+t',
            'x+b'
        ];

        foreach ($writableModes as $mode) {
            $this->resource = fopen(self::$nonExistingFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertTrue($resourceStream->isWritable());

            fclose($this->resource);
            unlink(self::$nonExistingFile);
        }
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::isSeekable
     */
    public function shouldBeSeekableWhenInitializedWithASeekableResource(): void
    {
        $seekableModes = [
            'r',
	        'rt',
	        'rb',

            'r+',
	        'r+t',
	        'r+b',

            'w',
	        'wt',
	        'wb',

            'w+',
	        'w+t',
	        'w+b',

            'a',
	        'at',
	        'ab',

            'a+',
	        'a+t',
	        'a+b',

            'c',
	        'ct',
	        'cb',

            'c+',
            'c+t',
            'c+b'
        ];

        foreach ($seekableModes as $mode) {
            $this->resource = fopen(self::$resourceStreamFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertTrue($resourceStream->isSeekable());

            fclose($this->resource);
        }

        // Test modes that error on existing file.
        $seekableModes = [
            'x',
	        'xt',
	        'xb',

            'x+',
            'x+t',
            'x+b'
        ];

        foreach ($seekableModes as $mode) {
            $this->resource = fopen(self::$nonExistingFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertTrue($resourceStream->isSeekable());

            fclose($this->resource);
            unlink(self::$nonExistingFile);
        }
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::isReadable
     */
    public function shouldNotBeReadableWhenInitializedWithANonReadableResource(): void
    {
        $nonReadableModes = [
            'w',
	        'wt',
	        'wb',

            'a',
	        'at',
	        'ab',

            'c',
	        'ct',
	        'cb'
        ];

        foreach ($nonReadableModes as $mode) {
            $this->resource = fopen(self::$resourceStreamFile, $mode);
            $resourceStream = new ResourceStream($this->resource);

            $this->assertFalse($resourceStream->isReadable());

            fclose($this->resource);
        }

        // Test mode that error on existing file.
        $this->resource = fopen(self::$nonExistingFile, 'x');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertFalse($resourceStream->isReadable());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::isWritable
     */
    public function shouldNotBeWritableWhenInitializedWithANonWritableResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertFalse($resourceStream->isWritable());
    }

    /**
     * @test
     * @covers ::close
     * @covers ::detach
     */
    public function shouldCloseTheResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->close();

        $this->assertFalse(is_resource($this->resource));
    }

    /**
     * @test
     * @covers ::close
     * @covers ::detach
     */
    public function shouldDetachResourceWhenClosing(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStreamMock = $this->getMockBuilder(ResourceStream::class)
                                   ->setConstructorArgs([$this->resource])
                                   ->setMethods(['detach'])
                                   ->getMock();

        $resourceStreamMock->expects($this->once())
                           ->method('detach')
                           ->willReturn($this->resource);

        /** @noinspection PhpUndefinedMethodInspection */
        $resourceStreamMock->close();
    }

    /**
     * @test
     * @covers ::detach
     */
    public function shouldReturnTheDetachedResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $detachedResource = $resourceStream->detach();

        $this->assertSame($this->resource, $detachedResource);
    }

    /**
     * @test
     * @covers ::detach
     */
    public function shouldNotBeAbleToCloseResourceWhenDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $detachedResource = $resourceStream->detach();

        $resourceStream->close();

        $this->assertTrue(is_resource($detachedResource));
    }

    /**
     * @test
     * @covers ::detach
     */
    public function shouldBeNonReadableWritableOrSeekableWhenDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $this->assertFalse($resourceStream->isReadable());
        $this->assertFalse($resourceStream->isWritable());
        $this->assertFalse($resourceStream->isSeekable());
    }

    /**
     * @test
     * @covers ::detach
     * @expectedException \RuntimeException
     */
    public function shouldBeInANonUsableStateWhenDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $resourceStream->tell();
    }

    /**
     * @test
     * @covers ::getSize
     */
    public function shouldGetSizeOfResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertSame(self::$resourceStreamFileSize, $resourceStream->getSize());
    }

    /**
     * @test
     * @covers ::getSize
     */
    public function getSizeShouldReturnNullIfResourceIsDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $this->assertNull($resourceStream->getSize());
    }

    /**
     * @test
     * @covers ::tell
     */
    public function shouldReturnThePositionOfTheResourcePointer(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertSame(0, $resourceStream->tell());

        fseek($this->resource, 12);

        $this->assertSame(12, $resourceStream->tell());
    }

    /**
     * @test
     * @covers ::tell
     * @expectedException \RuntimeException
     */
    public function tellShouldThrowExceptionIfResourceIsDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $resourceStream->tell();
    }

    /**
     * @test
     * @covers ::eof
     */
    public function shouldReturnFalseIfResourceIsNotAtEOF(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertFalse($resourceStream->eof());
    }

    /**
     * @test
     * @covers ::eof
     */
    public function shouldReturnTrueIfResourceIsAtEOF(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        fseek($this->resource, 0, SEEK_END);

        $this->assertTrue($resourceStream->eof());
    }

    /**
     * @test
     * @covers ::rewind
     * @covers ::seek
     */
    public function shouldRewindResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        fseek($this->resource, 12);

        $resourceStream = new ResourceStream($this->resource);
        $resourceStream->rewind();

        $this->assertSame(0, $resourceStream->tell());
    }

    /**
     * @test
     * @covers ::rewind
     * @expectedException \RuntimeException
     */
    public function rewindShouldThrowExceptionIfResourceIsNotSeekable(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStreamMock = $this->getMockBuilder(ResourceStream::class)
                                   ->setConstructorArgs([$this->resource])
                                   ->setMethods(['isSeekable'])
                                   ->getMock();
        $resourceStreamMock->expects($this->once())
                           ->method('isSeekable')
                           ->willReturn(false);

        /** @noinspection PhpUndefinedMethodInspection */
        $resourceStreamMock->rewind();
    }

    /**
     * @test
     * @covers ::seek
     */
    public function shouldSeekToSetPosition(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        fseek($this->resource, 8);

        $resourceStream->seek(12, SEEK_SET);

        $this->assertSame(12, ftell($this->resource));
    }

    /**
     * @test
     * @covers ::seek
     */
    public function seekShouldDefaultToSeekSet(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        fseek($this->resource, 8);

        $resourceStream->seek(12);

        $this->assertSame(12, ftell($this->resource));
    }

    /**
     * @test
     * @covers ::seek
     */
    public function shouldSeekFromCurrentPosition(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        fseek($this->resource, 8);

        $resourceStream->seek(4, SEEK_CUR);

        $this->assertSame(12, ftell($this->resource));
    }

    /**
     * @test
     * @covers ::seek
     */
    public function shouldSeekFromEnd(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->seek(-4, SEEK_END);

        $this->assertSame(self::$resourceStreamFileSize - 4, ftell($this->resource));
    }

    /**
     * @test
     * @covers ::seek
     * @expectedException \RuntimeException
     */
    public function seekShouldThrowExceptionIfResourceIsNotSeekable(): void
    {
        $this->resource = fopen('php://stdin', 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->seek(0);
    }

    /**
     * @test
     * @covers ::seek
     * @expectedException \RuntimeException
     */
    public function seekShouldThrowExceptionIfFailingToSeekResource(): void
    {
        $this->resource = fopen('php://stdin', 'r');
        $resourceStreamMock = $this->getMockBuilder(ResourceStream::class)
                                   ->setConstructorArgs([$this->resource])
                                   ->setMethods(['isSeekable'])
                                   ->getMock();
        $resourceStreamMock->expects($this->once())
                           ->method('isSeekable')
                           ->willReturn(true);

        /** @noinspection PhpUndefinedMethodInspection */
        $resourceStreamMock->seek(0);
    }

    /**
     * @test
     * @covers ::write
     */
    public function shouldWriteToResource(): void
    {
        $this->resource = fopen(self::$nonExistingFile, 'w+');
        $resourceStream = new ResourceStream($this->resource);
        $fileContent = 'File content';

        $bytesWritten = $resourceStream->write($fileContent);

        rewind($this->resource);

        $this->assertSame(strlen($fileContent), $bytesWritten);
        $this->assertSame($fileContent, fread($this->resource, strlen($fileContent)));
    }

    /**
     * @test
     * @covers ::write
     * @expectedException \RuntimeException
     */
    public function writeShouldThrowExceptionOnWriteError(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->write('File content');
    }

    /**
     * @test
     * @covers ::read
     */
    public function shouldReadFromResource(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $fileContent = $resourceStream->read($resourceStream->getSize());

        $this->assertSame(self::$resourceStreamFileContent, $fileContent);
    }

    /**
     * @test
     * @covers ::read
     * @expectedException \RuntimeException
     */
    public function readShouldThrowExceptionIfLengthIsLessThanOrEqualToZero(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->read(0);
    }

    /**
     * @test
     * @covers ::read
     * @expectedException \RuntimeException
     */
    public function readShouldThrowExceptionOnReadError(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'w');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->read(10);
    }

    /**
     * @test
     * @covers ::getContents
     */
    public function getContentsShouldReturnEntireContentIfPositionIsAtStart(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $content = $resourceStream->getContents();

        $this->assertSame(self::$resourceStreamFileContent, $content);
    }

    /**
     * @test
     * @covers ::getContents
     */
    public function getContentsShouldReturnRemainingContent(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        fseek($this->resource, 12);

        $content = $resourceStream->getContents();

        $this->assertSame(substr(self::$resourceStreamFileContent, 12), $content);
    }

    /**
     * @test
     * @covers ::getContents
     * @expectedException \RuntimeException
     */
    public function getContentsShouldThrowExceptionOnReadError(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'w');
        $resourceStreamMock = $this->getMockBuilder(ResourceStream::class)
                                   ->setConstructorArgs([$this->resource])
                                   ->setMethods(['getSize'])
                                   ->getMock();
        $resourceStreamMock->expects($this->once())
                           ->method('getSize')
                           ->willReturn(20);

        /** @noinspection PhpUndefinedMethodInspection */
        $resourceStreamMock->getContents();
    }

    /**
     * @test
     * @covers ::getMetadata
     */
    public function shouldReturnAllMetadata(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertSame(self::$resourceStreamFileMetadata, $resourceStream->getMetadata());
    }

    /**
     * @test
     * @covers ::getMetadata
     */
    public function shouldReturnMetadataForGivenKey(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertSame('r', $resourceStream->getMetadata('mode'));
    }

    /**
     * @test
     * @covers ::getMetadata
     */
    public function getMetadataShouldReturnNullIfGivenKeyIsNotFound(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $this->assertNull($resourceStream->getMetadata('non-existing-key'));
    }

    /**
     * @test
     * @covers ::getMetadata
     */
    public function getMetadataShouldReturnEmptyArrayIfResourceIsDetached(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $this->assertEmpty($resourceStream->getMetadata());
    }

    /**
     * @test
     * @covers ::getMetadata
     */
    public function getMetadataShouldReturnNulIfResourceIsDetachedAndKeyIsGiven(): void
    {
        $this->resource = fopen(self::$resourceStreamFile, 'r');
        $resourceStream = new ResourceStream($this->resource);

        $resourceStream->detach();

        $this->assertNull($resourceStream->getMetadata('mode'));
    }
}
