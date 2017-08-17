<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\StringStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\StringStream
 */
class StringStreamTest extends TestCase
{
    /**
     * @since 0.1.0
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrStreamInterface(): void
    {
        $this->assertContains(StreamInterface::class, class_implements(StringStream::class));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__construct
     * @covers ::__toString
     */
    public function shouldReturnTheEntireStreamWhenCastToString(): void
    {
        $streamContent = 'Stream Content';
        $stringStream = new StringStream($streamContent);

        $this->assertSame($streamContent, (string)$stringStream);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__construct
     * @covers ::__toString
     */
    public function shouldProvideDefaultStreamContent(): void
    {
        $stringStream = new StringStream;

        $this->assertSame('', (string)$stringStream);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__construct
     * @covers ::isReadable
     * @covers ::isWritable
     * @covers ::isSeekable
     */
    public function shouldInitializeProperly(): void
    {
        $stringStream = new StringStream;

        $this->assertTrue($stringStream->isReadable());
        $this->assertTrue($stringStream->isWritable());
        $this->assertTrue($stringStream->isSeekable());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::close
     */
    public function shouldCloseProperly(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->close();

        $this->assertSame('', (string)$stringStream);
        $this->assertFalse($stringStream->isReadable());
        $this->assertFalse($stringStream->isWritable());
        $this->assertFalse($stringStream->isSeekable());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::detach
     */
    public function shouldDetachProperly(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stream = $stringStream->detach();

        $this->assertSame('', (string)$stringStream);
        $this->assertFalse($stringStream->isReadable());
        $this->assertFalse($stringStream->isWritable());
        $this->assertFalse($stringStream->isSeekable());
        $this->assertNull($stream);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getSize
     */
    public function shouldReturnContentSize(): void
    {
        $emptyStream = new StringStream('');
        $stringStream = new StringStream('Stream Content');

        $this->assertSame(0, $emptyStream->getSize());
        $this->assertSame(14, $stringStream->getSize());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::__construct
     * @covers ::tell
     */
    public function shouldSetInitialPointerPosition(): void
    {
        $stringStream = new StringStream('Stream Content');

        $this->assertSame(0, $stringStream->tell());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::seek
     */
    public function shouldSeekToGivenPosition(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->seek(4, SEEK_SET);
        $this->assertSame(4, $stringStream->tell());

        $stringStream->seek(4, SEEK_SET);
        $this->assertSame(4, $stringStream->tell());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::seek
     */
    public function shouldSeekToPositionFromCurrentOffset(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->seek(4, SEEK_CUR);
        $this->assertSame(4, $stringStream->tell());

        $stringStream->seek(4, SEEK_CUR);
        $this->assertSame(8, $stringStream->tell());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::seek
     */
    public function shouldSeekToPositionFromEnd(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->seek(-4, SEEK_END);
        $this->assertSame(10, $stringStream->tell());

        $stringStream->seek(-4, SEEK_END);
        $this->assertSame(10, $stringStream->tell());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::seek
     * @expectedException RuntimeException
     */
    public function shouldThrowIfInvalidWhence(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(0, 12);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::seek
     * @expectedException RuntimeException
     */
    public function shouldThrowIfInvalidOffset(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(20);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::eof
     */
    public function shouldDetermineIfEof(): void
    {
        $stringStream = new StringStream('Stream Content');

        $this->assertFalse($stringStream->eof());

        $stringStream->seek(0, SEEK_END);

        $this->assertTrue($stringStream->eof());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::rewind
     */
    public function shouldRewindStream(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(4);
        $this->assertSame(4, $stringStream->tell());

        $stringStream->rewind();

        $this->assertSame(0, $stringStream->tell());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::read
     */
    public function shouldReadGivenLengthOfBytes(): void
    {
        $stringStream = new StringStream('Stream Content');

        $this->assertSame('Stream', $stringStream->read(6));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::read
     */
    public function shouldReadGivenLengthOfBytesFromCurrentOffset(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->seek(7);
        $this->assertSame('Content', $stringStream->read(7));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::read
     */
    public function shouldReturnEmptyStringIfNoMoreBytesAvailable(): void
    {
        $stringStream = new StringStream('Stream Content');
        $emptyStream = new StringStream('');
        $stringStream->seek(0, SEEK_END);

        $this->assertSame('', $stringStream->read(20));
        $this->assertSame('', $emptyStream->read(20));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getContents
     */
    public function shouldReturnTheRemainingContent(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(7);

        $this->assertSame('Content', $stringStream->getContents());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::write
     */
    public function shouldWriteGivenData(): void
    {
        $stringStream = new StringStream('Stream Content');

        $stringStream->write('String ');

        $this->assertSame('String Stream Content', (string)$stringStream);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::write
     */
    public function shouldWriteGivenDataAtCurrentOffset(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(7);

        $stringStream->write('String ');

        $this->assertSame('Stream String Content', (string)$stringStream);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::write
     */
    public function shouldReturnBytesWritten(): void
    {
        $stringStream = new StringStream('Stream Content');

        $bytesWritten = $stringStream->write('String ');

        $this->assertSame(7, $bytesWritten);
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldReturnMetadataArray(): void
    {
        $stringStream = new StringStream('');

        $this->assertEquals([
            'timed_out' => false,
            'blocked' => false,
            'eof' => true,
            'unread_bytes' => 0,
            'stream_type' => 'string',
            'wrapper_type' => 'php://',
            'wrapper_data' => null,
            'mode' => 'r+',
            'seekable' => true,
            'uri' => ''
        ], $stringStream->getMetadata());
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldReturnMetadataValueByGivenKey(): void
    {
        $stringStream = new StringStream('');

        $this->assertSame('string', $stringStream->getMetadata('stream_type'));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldReturnNullIfMetadataKeyNotFound(): void
    {
        $stringStream = new StringStream('');

        $this->assertNull($stringStream->getMetadata('nokey'));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldUpdateEofMetadata(): void
    {
        $stringStream = new StringStream('Stream Content');

        $this->assertFalse($stringStream->getMetadata('eof'));

        $stringStream->seek(0, SEEK_END);

        $this->assertTrue($stringStream->getMetadata('eof'));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldUpdateUnreadBytesMetadata(): void
    {
        $stringStream = new StringStream('Stream Content');
        $stringStream->seek(7);

        $this->assertSame(7, $stringStream->getMetadata('unread_bytes'));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldUpdateModeMetadata(): void
    {
        $stringStream = new StringStream('');

        $this->assertSame('r+', $stringStream->getMetadata('mode'));

        $stringStream->close();

        $this->assertSame('', $stringStream->getMetadata('mode'));
    }

    /**
     * @since 0.1.0
     * @test
     * @covers ::getMetadata
     */
    public function shouldUpdateSeekableMetadata(): void
    {
        $stringStream = new StringStream('');

        $this->assertTrue($stringStream->getMetadata('seekable'));

        $stringStream->close();

        $this->assertFalse($stringStream->getMetadata('seekable'));
    }
}
