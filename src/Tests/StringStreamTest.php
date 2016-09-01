<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Solid\Http\StringStream;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\StringStream
 */
class StringStreamTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var string
     */
    protected $stringStream;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->stringStream = new StringStream('This is a test string.');
    }


    /**
     * @api
     * @before
     * @coversNothing
     * @since 0.1.0
     * @return void
     */
    public function testImplementationRequirements()
    {
        $this->assertInstanceOf(
            'Psr\Http\Message\StreamInterface',
            $this->stringStream,
            'Should implement PSR-7 StreamInterface'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__toString
     * @covers ::__construct
     * @since 0.1.0
     * @return void
     */
    public function testToString()
    {
        $this->assertSame(
            'This is a test string.',
            (string) $this->stringStream,
            'Should render correctly as a string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::isReadable
     * @since 0.1.0
     * @return void
     */
    public function testIsReadable()
    {
        $this->assertTrue($this->stringStream->isReadable(), 'Should be readable');
    }

    /**
     * @api
     * @test
     * @covers ::isWritable
     * @since 0.1.0
     * @return void
     */
    public function testIsWritable()
    {
        $this->assertTrue($this->stringStream->isWritable(), 'Should be writable');
    }

    /**
     * @api
     * @test
     * @covers ::isSeekable
     * @since 0.1.0
     * @return void
     */
    public function testIsSeekable()
    {
        $this->assertTrue($this->stringStream->isSeekable(), 'Should be seekable');
    }

    /**
     * @api
     * @test
     * @covers ::close
     * @since 0.1.0
     * @return void
     */
    public function testClose()
    {
        $this->stringStream->close();

        $this->assertFalse($this->stringStream->isReadable(), 'Should be able to close stream');
        $this->assertFalse($this->stringStream->isWritable(), 'Should be able to close stream');
        $this->assertFalse($this->stringStream->isSeekable(), 'Should be able to close stream');
        $this->assertSame('', (string) $this->stringStream, 'Should be able to close stream');
    }

    /**
     * @api
     * @test
     * @covers ::detach
     * @since 0.1.0
     * @return void
     */
    public function testDetach()
    {
        $this->stringStream->detach();

        $this->assertFalse($this->stringStream->isReadable(), 'Should be able to detach stream');
        $this->assertFalse($this->stringStream->isWritable(), 'Should be able to detach stream');
        $this->assertFalse($this->stringStream->isSeekable(), 'Should be able to detach stream');
        $this->assertSame('', (string) $this->stringStream, 'Should be able to detach stream');
    }

    /**
     * @api
     * @test
     * @covers ::getSize
     * @since 0.1.0
     * @return void
     */
    public function testGetSize()
    {
        $this->assertSame(
            strlen('This is a test string.'),
            $this->stringStream->getSize(),
            'Should be able to determine the size of the stream'
        );
    }

    /**
     * @api
     * @test
     * @covers ::tell
     * @since 0.1.0
     * @return void
     */
    public function testTell()
    {
        $this->assertSame(
            0,
            $this->stringStream->tell(),
            'Should be able to determine the current position of the pointer'
        );

        $this->stringStream->seek(4);
        $this->assertSame(
            4,
            $this->stringStream->tell(),
            'Should be able to determine the current position of the pointer'
        );
    }

    /**
     * @api
     * @test
     * @covers ::eof
     * @since 0.1.0
     * @return void
     */
    public function testEof()
    {
        $this->assertFalse(
            $this->stringStream->eof(),
            'Should be able to tell if current pointer is at end of stream'
        );

        $this->stringStream->seek(0, SEEK_END);
        $this->assertTrue(
            $this->stringStream->eof(),
            'Should be able to tell if current pointer is at end of stream'
        );
    }

    /**
     * @api
     * @test
     * @covers ::seek
     * @since 0.1.0
     * @return void
     */
    public function testSeek()
    {
        $this->assertSame(0, $this->stringStream->tell(), 'Shoule be able to correctly seek the stream');

        $this->stringStream->seek(2, SEEK_SET);
        $this->stringStream->seek(2);
        $this->assertSame(2, $this->stringStream->tell(), 'Shoule be able to correctly seek the stream');

        $this->stringStream->seek(0, SEEK_SET);
        $this->stringStream->seek(2, SEEK_CUR);
        $this->stringStream->seek(2, SEEK_CUR);
        $this->assertSame(4, $this->stringStream->tell(), 'Shoule be able to correctly seek the stream');

        $this->stringStream->seek(0, SEEK_END);
        $this->assertSame(
            strlen('This is a test string.'),
            $this->stringStream->tell(),
            'Shoule be able to correctly seek the stream'
        );

        $this->stringStream->seek(-4, SEEK_END);
        $this->stringStream->seek(-4, SEEK_END);
        $this->assertSame(
            strlen('This is a test string.') - 4,
            $this->stringStream->tell(),
            'Shoule be able to correctly seek the stream'
        );
    }

    /**
     * @api
     * @test
     * @covers ::seek
     * @expectedException RuntimeException
     * @since 0.1.0
     * @return void
     */
    public function testInvalidSeek()
    {
        $this->stringStream->seek(0, 299);
    }

    /**
     * @api
     * @test
     * @covers ::rewind
     * @since 0.1.0
     * @return void
     */
    public function testRewind()
    {
        $this->stringStream->seek(4);
        $this->stringStream->rewind();

        $this->assertSame(
            0,
            $this->stringStream->tell(),
            'Shoule be able to rewind the stream'
        );
    }

    /**
     * @api
     * @test
     * @covers ::write
     * @since 0.1.0
     * @return void
     */
    public function testWrite()
    {
        $writeString = 'New content - ';

        $response = $this->stringStream->write($writeString);

        $this->assertSame(strlen($writeString), $response, 'Should be able to correctly write to the stream');
        $this->assertSame(
            'New content - This is a test string.',
            (string) $this->stringStream,
            'Should be able to correctly write to the stream'
        );

        $this->stringStream->seek(0, SEEK_END);
        $this->stringStream->write('..');
        $this->assertSame(
            'New content - This is a test string...',
            (string) $this->stringStream,
            'Should be able to correctly write to the stream'
        );
    }

    /**
     * @api
     * @test
     * @covers ::read
     * @since 0.1.0
     * @return void
     */
    public function testRead()
    {
        $this->assertSame('This is', $this->stringStream->read(7), 'Should be able to correctly read from the stream');

        $this->stringStream->seek(7);
        $this->assertSame(' a test', $this->stringStream->read(7), 'Should be able to correctly read from the stream');
    }

    /**
     * @api
     * @test
     * @covers ::getContents
     * @since 0.1.0
     * @return void
     */
    public function testGetContents()
    {
        $this->assertSame(
            'This is a test string.',
            $this->stringStream->getContents(),
            'Should correctly return the remaining contents'
        );

        $this->stringStream->seek(-4, SEEK_END);
        $this->assertSame(
            'ing.',
            $this->stringStream->getContents(),
            'Should correctly return the remaining contents'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getMetadata
     * @since 0.1.0
     * @return void
     */
    public function testGetMetadata()
    {
        $this->assertEquals(
            [
                'timed_out' => false,
                'blocked' => false,
                'eof' => false,
                'unread_bytes' => strlen('This is a test string.'),
                'stream_type' => 'string',
                'wrapper_type' => 'php://',
                'wrapp_data' => null,
                'mode' => 'r+',
                'seekable' => true,
                'uri' => ''
            ],
            $this->stringStream->getMetadata(),
            'Should return correct metadata'
        );

        $this->stringStream->seek(12);

        $this->assertFalse($this->stringStream->getMetadata('eof'), 'Should return correct metadata');
        $this->assertSame(10, $this->stringStream->getMetadata('unread_bytes'), 'Should return correct metadata');

        $this->stringStream->seek(0, SEEK_END);
        $this->assertTrue($this->stringStream->getMetadata('eof'), 'Should return correct metadata');
        $this->assertSame(0, $this->stringStream->getMetadata('unread_bytes'), 'Should return correct metadata');
        $this->assertNull($this->stringStream->getMetadata('non_existing_key'), 'Should return correct metadata');

        $this->stringStream->detach();
        $this->assertSame('', $this->stringStream->getMetadata('mode'), 'Should return correct metadata');
    }
}
