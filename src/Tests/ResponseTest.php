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
     * @before
     * @since 0.1.0
     */
    public function setup()
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
