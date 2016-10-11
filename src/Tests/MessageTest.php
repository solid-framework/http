<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Solid\Http\HeaderContainer;
use Solid\Http\Message;
use Solid\Http\StringStream;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Message
 */
class MessageTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var Message
     */
    protected $message;

    /**
     * @api
     * @before
     * @since 0.1.0
     */
    public function setup()
    {
        $this->message = new Message;
    }

    /**
     * @api
     * @test
     * @coversNothing
     * @since 0.1.0
     * @return void
     */
    public function testImplementationRequirements()
    {
        $this->assertInstanceOf(
            'Psr\Http\Message\MessageInterface',
            $this->message,
            'Should implement PSR-7 message interface'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getProtocolVersion
     * @since 0.1.0
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $this->assertSame('1.1', $this->message->getProtocolVersion(), 'Should return correct protocol version');
    }

    /**
     * @api
     * @test
     * @covers ::withProtocolVersion
     * @covers ::getProtocolVersion
     * @since 0.1.0
     * @return void
     */
    public function testWithProtocolVersion()
    {
        $newProtocolVersion = $this->message->withProtocolVersion('2.0');
        $this->assertInstanceOf('Solid\Http\Message', $newProtocolVersion, 'Should return new instance');
        $this->assertNotSame($this->message, $newProtocolVersion, 'Should return new instance');
        $this->assertSame('1.1', $this->message->getProtocolVersion(), 'Should not mutate the original request');
        $this->assertSame('2.0', $newProtocolVersion->getProtocolVersion(), 'Should return correct protocol version');
    }

    /**
     * @api
     * @test
     * @covers ::getHeaders
     * @since 0.1.0
     * @return void
     */
    public function testGetHeaders()
    {
        $this->assertSame(
            [
                'Content-Length' => [0]
            ],
            $this->message->getHeaders(),
            'Should return the correct headers'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getHeader
     * @since 0.1.0
     * @return void
     */
    public function testGetHeader()
    {
        $this->assertSame(
            [0],
            $this->message->getHeader('Content-Length'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            [0],
            $this->message->getHeader('conTent-LEngTh'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            [],
            $this->message->getHeader('Non-Existing-Header'),
            'Should return an empty array if the header field does not exist'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getHeaderLine
     * @since 0.1.0
     * @return void
     */
    public function testGetHeaderLine()
    {
        $this->assertSame(
            '0',
            $this->message->getHeaderLine('Content-Length'),
            'Should return the correct headerline'
        );
        $this->assertSame(
            '0',
            $this->message->getHeaderLine('conTeNt-lEngTh'),
            'Should return the correct headerline'
        );

        $multipleHeaders = $this->message->withAddedHeader('Content-Length', '24');
        $this->assertSame(
            '0,24',
            $multipleHeaders->getHeaderLine('Content-Length'),
            'Should return the correct headerline'
        );

        $this->assertSame(
            '',
            $this->message->getHeaderLine('Non-Existing-Header'),
            'Should return an empty string if the header field does not exist'
        );
    }

    /**
     * @api
     * @test
     * @covers ::hasHeader
     * @covers ::__construct
     * @since 0.1.0
     * @return void
     */
    public function testHasHeader()
    {
        $this->assertTrue($this->message->hasHeader('Content-Length'), 'Should be able to determine if a header is set');
        $this->assertTrue($this->message->hasHeader('coNteNt-lengtH'), 'Should be able to determine if a header is set');
        $this->assertFalse($this->message->hasHeader('host'), 'Should be able to determine if a header is set');
    }

    /**
     * @api
     * @test
     * @covers ::withHeader
     * @covers ::getHeader
     * @covers ::__clone
     * @since 0.1.0
     * @return void
     */
    public function testWithHeader()
    {
        $newContentLength = $this->message->withHeader('content-length', 24);
        $this->assertInstanceOf('Solid\Http\Message', $newContentLength, 'Should return new instance');
        $this->assertNotSame($this->message, $newContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->message->getHeader('content-length'),
            'Should not mutate the original message'
        );

        $this->assertSame(
            [24],
            $newContentLength->getHeader('conTent-lengTh'),
            'Should be able to set new header value'
        );
    }

    /**
     * @api
     * @test
     * @covers ::withAddedHeader
     * @covers ::getHeader
     * @covers ::__clone
     * @since 0.1.0
     * @return void
     */
    public function testWithAddedHeader()
    {
        $newAddedContentLength = $this->message->withAddedHeader('content-length', 24);
        $this->assertInstanceOf('Solid\Http\Message', $newAddedContentLength, 'Should return new instance');
        $this->assertNotSame($this->message, $newAddedContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->message->getHeader('Content-Length'),
            'Should not mutate the original request'
        );
        $this->assertSame(
            [0, 24],
            $newAddedContentLength->getHeader('Content-Length'),
            'Should be able to set new header value'
        );
    }

    /**
     * @api
     * @test
     * @covers ::withoutHeader
     * @covers ::getHeader
     * @covers ::__clone
     * @since 0.1.0
     * @return void
     */
    public function testWithoutHeader()
    {
        $noContentLength = $this->message->withoutHeader('conTeNt-lengtH');
        $this->assertInstanceOf('Solid\Http\Message', $noContentLength, 'Should return new instance');
        $this->assertNotSame($this->message, $noContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->message->getHeader('content-length'),
            'Should not mutate the original request'
        );
        $this->assertSame(
            [],
            $noContentLength->getHeader('Content-Length'),
            'Should be able to remove headers'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getBody
     * @covers ::__construct
     * @since 0.1.0
     * @return void
     */
    public function testGetBody()
    {
        $body = $this->message->getBody();

        $this->assertInstanceOf(
            'Psr\Http\Message\StreamInterface',
            $body,
            'Should return a PSR-7 string stream object'
        );

        $this->assertSame('', (string) $body, 'Should return the correct body object');
    }

    /**
     * @api
     * @test
     * @covers ::withBody
     * @covers ::getBody
     * @covers ::__clone
     * @since 0.1.0
     * @return void
     */
    public function testWithBody()
    {
        $newBody = $this->message->withBody(new StringStream('This is the body'));

        $this->assertInstanceOf('Solid\Http\Message', $newBody, 'Should return new instance');
        $this->assertNotSame($this->message, $newBody, 'Should return new instance');
        $this->assertSame(
            '',
            (string) $this->message->getBody(),
            'Should not mutate the original request'
        );
        $this->assertSame(
            'This is the body',
            (string) $newBody->getBody(),
            'Should be able to set new body'
        );
    }
}
