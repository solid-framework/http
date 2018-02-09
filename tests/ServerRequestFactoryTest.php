<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Solid\Http\ServerRequestFactory;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\ServerRequestFactory
 */
class ServerRequestFactoryTest extends TestCase
{
    /**
     * @var \Solid\Http\ServerRequestFactory
     */
    protected $serverRequestFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $uriFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $uploadedFileFactoryMock;

    /**
     * @before
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->uriFactoryMock = $this->getMockBuilder(UriFactoryInterface::class)
                                     ->setMethods(['createUri'])
                                     ->getMock();

        $this->streamFactoryMock = $this->getMockBuilder(StreamFactoryInterface::class)
                                        ->setMethods([
                                            'createStream',
                                            'createStreamFromFile',
                                            'createStreamFromResource'
                                        ])
                                        ->getMock();

        $this->uploadedFileFactoryMock = $this->getMockBuilder(UploadedFileFactoryInterface::class)
                                              ->setMethods(['createUploadedFile'])
                                              ->getMock();

        /** @noinspection PhpParamsInspection */
        $this->serverRequestFactory = new ServerRequestFactory(
            $this->uriFactoryMock,
            $this->streamFactoryMock,
            $this->uploadedFileFactoryMock
        );
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrServerRequestFactoryInterface(): void
    {
        $this->assertContains(ServerRequestFactoryInterface::class, class_implements(ServerRequestFactory::class));
    }

    /**
     * @test
     * @covers ::createServerRequest
     * @covers ::__construct
     */
    public function shouldCreateNewServerRequestFromMethodAndUriInterface(): void
    {
        $method = 'GET';
        $uriMock = $this->getMockBuilder(UriInterface::class)->getMock();
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $this->uriFactoryMock->expects($this->never())
                             ->method('createUri');

        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);

        $serverRequest = $this->serverRequestFactory->createServerRequest($method, $uriMock);

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertSame($method, $serverRequest->getMethod());
        $this->assertSame($uriMock, $serverRequest->getUri());
        $this->assertEmpty($serverRequest->getUploadedFiles());
        $this->assertEmpty($serverRequest->getCookieParams());
        $this->assertEmpty($serverRequest->getServerParams());
    }

    /**
     * @test
     * @covers ::createServerRequest
     * @covers ::__construct
     */
    public function shouldCreateServerRequestFromMethodAndUri(): void
    {
        $method = 'GET';
        $uri = 'http://solid-framework.com';
        $uriMock = $this->getMockBuilder(UriInterface::class)->getMock();
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $this->uriFactoryMock->expects($this->once())
                             ->method('createUri')
                             ->with($uri)
                             ->willReturn($uriMock);

        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);

        $serverRequest = $this->serverRequestFactory->createServerRequest($method, $uri);

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertSame($method, $serverRequest->getMethod());
        $this->assertSame($uriMock, $serverRequest->getUri());
        $this->assertEmpty($serverRequest->getUploadedFiles());
        $this->assertEmpty($serverRequest->getCookieParams());
        $this->assertEmpty($serverRequest->getServerParams());
    }

    /**
     * @test
     * @covers ::createServerRequestFromArray
     * @covers ::__construct
     * @covers ::getMethod
     * @covers ::getProtocolVersion
     * @covers ::getUri
     * @covers ::isSsl
     * @covers ::getCredentials
     * @covers ::getHost
     * @covers ::getPort
     * @covers ::getRequestUri
     */
    public function shouldCreateServerRequestFromArray(): void
    {
        $method = 'GET';
        $uri = 'http://username:password@solid-framework.com:80/path?key=value';
        $server = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'solid-framework.com',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/path?key=value',
            'REQUEST_METHOD' => $method,
            'PHP_AUTH_USER' => 'username',
            'PHP_AUTH_PW' => 'password',
            'HTTP_HOST' => 'solid-framework.com',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_USER_AGENT' => 'User agent',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,sv;q=0.6',
            'REQUEST_TIME_FLOAT' => 1507671562.5422,
            'REQUEST_TIME' => 1507671562
        ];

        $uriMock = $this->getMockBuilder(UriInterface::class)->getMock();
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $this->uriFactoryMock->expects($this->once())
                             ->method('createUri')
                             ->with($uri)
                             ->willReturn($uriMock);

        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);

        $serverRequest = $this->serverRequestFactory->createServerRequestFromArray($server);

        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertSame($method, $serverRequest->getMethod());
        $this->assertSame($uriMock, $serverRequest->getUri());
        $this->assertEmpty($serverRequest->getUploadedFiles());
        $this->assertEmpty($serverRequest->getCookieParams());
        $this->assertEquals($server, $serverRequest->getServerParams());
    }

    /**
     * @test
     * @covers ::createServerRequestFromArray
     * @covers ::getMethod
     * @covers ::getProtocolVersion
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfMethodCannotBeDetermined(): void
    {
        $server = [
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/path?key=value',
            'HTTP_HOST' => 'solid-framework.com'
        ];

        $this->serverRequestFactory->createServerRequestFromArray($server);
    }

    /**
     * @test
     * @covers ::createServerRequestFromArray
     * @covers ::getUri
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfUriCannotBeDetermined(): void
    {
        $server = [
            'REQUEST_METHOD' => 'GET'
        ];

        $this->serverRequestFactory->createServerRequestFromArray($server);
    }
}
