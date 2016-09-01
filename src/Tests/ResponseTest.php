<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\Response;
use Solid\Http\StringStream;
use Solid\Http\HeaderContainer;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 * @coversDefaultClass Solid\Http\Response
 */
class ResponseTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var Response
     */
    protected $response;

    /**
     * @api
     * @since 0.1.0
     */
    public function __construct()
    {
        $this->response = new Response;
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
            'Solid\Kernel\ResponseInterface',
            $this->response,
            'Should implement kernel response interface'
        );
        $this->assertInstanceOf(
            'Psr\Http\Message\ResponseInterface',
            $this->response,
            'Should implement PSR-7 response interface'
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
        $this->assertSame('1.1', $this->response->getProtocolVersion(), 'Should return correct protocol version');
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
        $newProtocolVersion = $this->response->withProtocolVersion('2.0');
        $this->assertInstanceOf('Solid\Http\Response', $newProtocolVersion, 'Should return new instance');
        $this->assertNotSame($this->response, $newProtocolVersion, 'Should return new instance');
        $this->assertSame('1.1', $this->response->getProtocolVersion(), 'Should not mutate the original request');
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
            $this->response->getHeaders(),
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
            $this->response->getHeader('Content-Length'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            [0],
            $this->response->getHeader('conTent-LEngTh'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            [],
            $this->response->getHeader('Non-Existing-Header'),
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
            $this->response->getHeaderLine('Content-Length'),
            'Should return the correct headerline'
        );
        $this->assertSame(
            '0',
            $this->response->getHeaderLine('conTeNt-lEngTh'),
            'Should return the correct headerline'
        );

        $multipleHeaders = $this->response->withAddedHeader('Content-Length', '24');
        $this->assertSame(
            '0,24',
            $multipleHeaders->getHeaderLine('Content-Length'),
            'Should return the correct headerline'
        );

        $this->assertSame(
            '',
            $this->response->getHeaderLine('Non-Existing-Header'),
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
        $this->assertTrue($this->response->hasHeader('Content-Length'), 'Should be able to determine if a header is set');
        $this->assertTrue($this->response->hasHeader('coNteNt-lengtH'), 'Should be able to determine if a header is set');
        $this->assertFalse($this->response->hasHeader('host'), 'Should be able to determine if a header is set');
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
        $newContentLength = $this->response->withHeader('content-length', 24);
        $this->assertInstanceOf('Solid\Http\Response', $newContentLength, 'Should return new instance');
        $this->assertNotSame($this->response, $newContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->response->getHeader('content-length'),
            'Should not mutate the original response'
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
        $newAddedContentLength = $this->response->withAddedHeader('content-length', 24);
        $this->assertInstanceOf('Solid\Http\Response', $newAddedContentLength, 'Should return new instance');
        $this->assertNotSame($this->response, $newAddedContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->response->getHeader('Content-Length'),
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
        $noContentLength = $this->response->withoutHeader('conTeNt-lengtH');
        $this->assertInstanceOf('Solid\Http\Response', $noContentLength, 'Should return new instance');
        $this->assertNotSame($this->response, $noContentLength, 'Should return new instance');
        $this->assertSame(
            [0],
            $this->response->getHeader('content-length'),
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
        $body = $this->response->getBody();

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
        $newBody = $this->response->withBody(new StringStream('This is the body'));

        $this->assertInstanceOf('Solid\Http\Response', $newBody, 'Should return new instance');
        $this->assertNotSame($this->response, $newBody, 'Should return new instance');
        $this->assertSame(
            '',
            (string) $this->response->getBody(),
            'Should not mutate the original request'
        );
        $this->assertSame(
            'This is the body',
            (string) $newBody->getBody(),
            'Should be able to set new body'
        );
    }

    /**
     * @api
     * @test
     * @covers ::getStatusCode
     * @since 0.1.0
     * @return void
     */
    public function testGetStatusCode()
    {
        $this->assertSame(200, $this->response->getStatusCode(), 'Should return the correct status code');
    }

    /**
     * @api
     * @test
     * @covers ::withStatus
     * @covers ::getStatusCode
     * @since 0.1.0
     * @return void
     */
    public function testWithStatus()
    {
        $newStatus = $this->response->withStatus(400);

        $this->assertInstanceOf('Solid\Http\Response', $newStatus, 'Should return new instance');
        $this->assertNotSame($this->response, $newStatus, 'Should return new instance');
        $this->assertSame(
            200,
            $this->response->getStatusCode(),
            'Should not mutate the original request'
        );
        $this->assertSame(
            'OK',
            $this->response->getReasonPhrase(),
            'Should not mutate the original request'
        );
        $this->assertSame(
            400,
            $newStatus->getStatusCode(),
            'Should be able to set new status'
        );
        $this->assertSame(
            'Bad Request',
            $newStatus->getReasonPhrase(),
            'Should be able to set new status'
        );

        $newStatusAndReasonPhrase = $this->response->withStatus(500, 'Custom Reason Phrase');
        $this->assertSame(
            500,
            $newStatusAndReasonPhrase->getStatusCode(),
            'Should be able to set new status'
        );
        $this->assertSame(
            'Custom Reason Phrase',
            $newStatusAndReasonPhrase->getReasonPhrase(),
            'Should be able to set new status'
        );
    }

    /**
     * @api
     * @test
     * @covers ::withStatus
     * @expectedException InvalidArgumentException
     * @since 0.1.0
     * @return void
     */
    public function testWithInvalidStatus()
    {
        $invalidStatus = $this->response->withStatus(900);
    }

    /**
     * @api
     * @test
     * @covers ::__toString
     * @since 0.1.0
     * @return void
     */
    public function testToString()
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/Fixtures/empty-response.txt',
            (string) $this->response,
            'Should render correctly as a string'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @covers ::getReasonPhrase
     * @since 0.1.0
     * @return void
     */
    public function testConstructor()
    {
        $protocol = new Response('2.0');
        $this->assertSame(
            '2.0',
            $protocol->getProtocolVersion(),
            'Should be able to set the protocol version through the constructor'
        );

        $status = new Response(null, 404);
        $this->assertSame(
            404,
            $status->getStatusCode(),
            'Should be able to set the status through the constructor'
        );
        $this->assertSame(
            'Not Found',
            $status->getReasonPhrase(),
            'Should be able to set the status through the constructor'
        );

        $customStatus = new Response(null, 404, 'Custom Reason Phrase');
        $this->assertSame(
            404,
            $customStatus->getStatusCode(),
            'Should be able to set the status through the constructor'
        );
        $this->assertSame(
            'Custom Reason Phrase',
            $customStatus->getReasonPhrase(),
            'Should be able to set the status through the constructor'
        );

        $headers = new Response(null, null, null, new HeaderContainer([
            'Test-Header' => ['value1', 'value2']
        ]));
        $this->assertSame(
            ['value1', 'value2'],
            $headers->getHeader('test-header'),
            'Should be able to set headers through the constructor'
        );

        $body = new Response(null, null, null, null, new StringStream('This is the body'));
        $this->assertSame(
            'This is the body',
            (string) $body->getBody(),
            'Should be able to set the body through the constructor'
        );
    }

    /**
     * @api
     * @test
     * @covers ::__construct
     * @expectedException InvalidArgumentException
     * @since 0.1.0
     * @return void
     */
    public function testInvalidStatusInConstructor()
    {
        $invalidResponse = new Response(null, 900);
    }
}
