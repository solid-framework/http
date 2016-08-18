<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Kernel\Request as KernelRequest;
use Solid\Http\Uri;
use Solid\Http\Request;
use Solid\Http\StringStream;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class RequestTest extends TestCase
{
    /**
     * @internal
     * @since 0.1.0
     * @var Request
     */
    protected $getRequest;

    /**
     * @internal
     * @since 0.1.0
     * @var Request
     */
    protected $postRequest;

    /**
     * @internal
     * @since 0.1.0
     * @var Request
     */
    protected $emptyRequest;

    /**
     * @api
     * @before
     * @since 0.1.0
     * @return void
     */
    public function setup()
    {
        $this->getRequest = Request::fromKernelRequest(new Fixtures\GetKernelRequest);
        $this->postRequest = Request::fromKernelRequest(new Fixtures\PostKernelRequest);
        $this->putRequest = Request::fromKernelRequest(new Fixtures\PutKernelRequest);
        $this->emptyRequest = Request::fromKernelRequest(new Fixtures\EmptyKernelRequest);
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testImplementationRequirements()
    {
        $this->assertInstanceOf(
            'Solid\Kernel\RequestInterface',
            $this->getRequest,
            'Should implement kernel request interface'
        );
        $this->assertInstanceOf(
            'Psr\Http\Message\RequestInterface',
            $this->getRequest,
            'Should implement PSR-7 request interface'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetProtocolVersion()
    {
        $this->assertSame('1.1', $this->getRequest->getProtocolVersion(), 'Should return correct protocol version');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithProtocolVersion()
    {
        $newProtocolVersion = $this->getRequest->withProtocolVersion('2.0');
        $this->assertInstanceOf('Solid\Http\Request', $newProtocolVersion, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newProtocolVersion, 'Should return new instance');
        $this->assertSame('1.1', $this->getRequest->getProtocolVersion(), 'Should not mutate the original request');
        $this->assertSame('2.0', $newProtocolVersion->getProtocolVersion(), 'Should return correct protocol version');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetHeaders()
    {
        $this->assertSame([], $this->emptyRequest->getHeaders(), 'Should return the correct headers');
        $this->assertSame(
            [
                'Content-Length' => [0],
                'Accept' => ['application/json'],
                'Host' => ['example.com']
            ],
            $this->getRequest->getHeaders(),
            'Should return the correct headers'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetHeader()
    {
        $this->assertSame(
            ['example.com'],
            $this->getRequest->getHeader('Host'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            ['example.com'],
            $this->getRequest->getHeader('hoSt'),
            'Should be able to retrieve a single header field'
        );
        $this->assertSame(
            [],
            $this->getRequest->getHeader('Non-Existing-Header'),
            'Should return an empty array if the header field does not exist'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetHeaderLine()
    {
        $this->assertSame(
            'example.com',
            $this->getRequest->getHeaderLine('Host'),
            'Should return the correct headerline'
        );
        $this->assertSame(
            'example.com',
            $this->getRequest->getHeaderLine('hoSt'),
            'Should return the correct headerline'
        );

        $multipleHeaders = $this->getRequest->withAddedHeader('Host', 'new-host.com');
        $this->assertSame(
            'example.com,new-host.com',
            $multipleHeaders->getHeaderLine('Host'),
            'Should return the correct headerline'
        );

        $this->assertSame(
            '',
            $this->getRequest->getHeaderLine('Non-Existing-Header'),
            'Should return an empty string if the header field does not exist'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testHasHeader()
    {
        $this->assertTrue($this->getRequest->hasHeader('Host'), 'Should be able to determine if a header is set');
        $this->assertTrue($this->getRequest->hasHeader('hoSt'), 'Should be able to determine if a header is set');
        $this->assertFalse($this->emptyRequest->hasHeader('host'), 'Should be able to determine if a header is set');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithHeader()
    {
        $newHost = $this->getRequest->withHeader('host', 'new-host.com');
        $this->assertInstanceOf('Solid\Http\Request', $newHost, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newHost, 'Should return new instance');
        $this->assertSame(
            ['example.com'],
            $this->getRequest->getHeader('host'),
            'Should not mutate the original request'
        );
        $this->assertSame(['new-host.com'], $newHost->getHeader('hOsT'), 'Should be able to set new header value');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithAddedHeader()
    {
        $newHost = $this->getRequest->withAddedHeader('host', 'new-host.com');
        $this->assertInstanceOf('Solid\Http\Request', $newHost, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newHost, 'Should return new instance');
        $this->assertSame(
            ['example.com'],
            $this->getRequest->getHeader('host'),
            'Should not mutate the original request'
        );
        $this->assertSame(
            ['example.com', 'new-host.com'],
            $newHost->getHeader('hOsT'),
            'Should be able to set new header value'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithoutHeader()
    {
        $noHost = $this->getRequest->withoutHeader('hoSt');
        $this->assertInstanceOf('Solid\Http\Request', $noHost, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $noHost, 'Should return new instance');
        $this->assertSame(
            ['example.com'],
            $this->getRequest->getHeader('host'),
            'Should not mutate the original request'
        );
        $this->assertSame(
            [],
            $noHost->getHeader('Host'),
            'Should be able to remove headers'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetBody()
    {
        $getBody = $this->getRequest->getBody();
        $putBody = $this->putRequest->getBody();
        $postBody = $this->postRequest->getBody();

        $this->assertInstanceOf(
            'Psr\Http\Message\StreamInterface',
            $getBody,
            'Should return a PSR-7 string stream object'
        );

        $this->assertSame('', (string) $getBody, 'Should return the correct body object');
        $this->assertSame(
            'parameter1=value1&parameter2=value2',
            (string) $putBody,
            'Should return the correct body object'
        );
        $this->assertSame(
            'parameter1=value1&parameter2=value2',
            (string) $postBody,
            'Should return the correct body object'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithBody()
    {
        $newBody = $this->postRequest->withBody(new StringStream('This is the body'));

        $this->assertInstanceOf('Solid\Http\Request', $newBody, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newBody, 'Should return new instance');
        $this->assertSame(
            '',
            (string) $this->getRequest->getBody(),
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
     * @since 0.1.0
     * @return void
     */
    public function testGetRequestTarget()
    {
        $this->assertSame(
            '/',
            $this->emptyRequest->getRequestTarget(),
            'Should return correct request target'
        );
        $this->assertSame(
            '/example/path?parameter1=value1&parameter2=value2',
            $this->getRequest->getRequestTarget(),
            'Should return correct request target'
        );
        $this->assertSame(
            '/',
            $this->postRequest->getRequestTarget(),
            'Should return correct request target'
        );
        $this->assertSame(
            '/path',
            $this->putRequest->getRequestTarget(),
            'Should return correct request target'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithRequestTarget()
    {
        $newRequest = $this->getRequest->withRequestTarget('/new/path');

        $this->assertInstanceOf('Solid\Http\Request', $newRequest, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newRequest, 'Should return new instance');
        $this->assertSame(
            '/example/path?parameter1=value1&parameter2=value2',
            $this->getRequest->getRequestTarget(),
            'Should not mutate the original request'
        );
        $this->assertSame('/new/path', $newRequest->getRequestTarget(), 'Should be able to set request target');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetMethod()
    {
        $this->assertSame('GET', $this->getRequest->getMethod(), 'Should return the correct request method');
        $this->assertSame('post', $this->postRequest->getMethod(), 'Should return the correct request method');
        $this->assertSame('Put', $this->putRequest->getMethod(), 'Should return the correct request method');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithMethod()
    {
        $newRequest = $this->getRequest->withMethod('DelEtE');

        $this->assertInstanceOf('Solid\Http\Request', $newRequest, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $newRequest, 'Should return new instance');
        $this->assertSame(
            'GET',
            $this->getRequest->getMethod(),
            'Should not mutate the original request'
        );
        $this->assertSame(
            'DelEtE',
            $newRequest->getMethod(),
            'Should be able to set request method (case insensitive)'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     * @expectedException InvalidArgumentException
     */
    public function testWithInvalidMethod()
    {
        $newRequestt = $this->getRequest->withMethod('UNSUPPORTED_METHOD');
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testGetUri()
    {
        $this->assertInstanceOf(
            'Psr\Http\Message\UriInterface',
            $this->getRequest->getUri(),
            'Should return a Uri instance'
        );
        $this->assertSame(
            'http://username:password@example.com/example/path?parameter1=value1&parameter2=value2',
            (string) $this->getRequest->getUri(),
            'Should return the correct uri instance'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testWithUri()
    {
        $withHost = $this->getRequest->withUri(new Uri('new-example.com/new/path'));
        $this->assertInstanceOf('Solid\Http\Request', $withHost, 'Should return new instance');
        $this->assertNotSame($this->getRequest, $withHost, 'Should return new instance');
        $this->assertSame(
            'example.com',
            $this->getRequest->getUri()->getHost(),
            'Should not mutate the original request'
        );
        $this->assertSame('new-example.com', $withHost->getUri()->getHost(), 'Should be able to set url');
        $this->assertSame('new-example.com', $withHost->getHeader('Host')[0], 'Should update host header');

        $emptyUri = $this->getRequest->withUri(new Uri);
        $this->assertSame(
            'example.com',
            $emptyUri->getHeader('Host')[0],
            'Should carry over host header if no host is provided in the new uri'
        );

        $newHost = $this->getRequest->withUri(new Uri('another-example.com'));
        $this->assertSame(
            'another-example.com',
            $newHost->getHeader('Host')[0],
            'Should update the host header if new host is provided'
        );

        $newPreservedHost = $this->getRequest->withUri(new Uri('another-example.com'), true);
        $this->assertSame(
            'example.com',
            $newPreservedHost->getHeader('Host')[0],
            'Should be able to preserve host if desired'
        );

        $newEmptyHost = $this->emptyRequest->withUri(new Uri, true);
        $this->assertEmpty(
            $newEmptyHost->getHeader('Host'),
            'Should not update the host header if no new host is provided'
        );

        $newPreservedEmptyHost = $this->emptyRequest->withUri(new Uri('example.com'), true);
        $this->assertSame(
            'example.com',
            $newPreservedEmptyHost->getHeader('Host')[0],
            'Should always update host header if previous host was empty and a new is provided'
        );
    }

    /**
     * @api
     * @test
     * @since 0.1.0
     * @return void
     */
    public function testToString()
    {
        $this->assertStringEqualsFile(
            __DIR__ . '/Fixtures/empty-request.txt',
            (string) $this->emptyRequest,
            'Should render correctly as a string'
        );
        $this->assertStringEqualsFile(
            __DIR__ . '/Fixtures/get-request.txt',
            (string) $this->getRequest,
            'Should render correctly as a string'
        );
        $this->assertStringEqualsFile(
            __DIR__ . '/Fixtures/post-request.txt',
            (string) $this->postRequest,
            'Should render correctly as a string'
        );
        $this->assertStringEqualsFile(
            __DIR__ . '/Fixtures/put-request.txt',
            (string) $this->putRequest,
            'Should render correctly as a string'
        );
    }
}
