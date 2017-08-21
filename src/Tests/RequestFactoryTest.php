<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Solid\Http\RequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass Solid\Http\RequestFactory
 */
class RequestFactoryTest extends TestCase
{
    /**
     * @var \Solid\Http\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $uriFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamFactoryMock;

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

        /** @noinspection PhpParamsInspection */
        $this->requestFactory = new RequestFactory($this->uriFactoryMock, $this->streamFactoryMock);
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrRequestFactoryInterface(): void
    {
        $this->assertContains(RequestFactoryInterface::class, class_implements(RequestFactory::class));
    }

    /**
     * @test
     * @covers ::createRequest
     * @covers ::__construct
     */
    public function shouldCreateRequestFromUri(): void
    {
        $uriMock = $this->getMockBuilder(UriInterface::class)->getMock();
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $this->uriFactoryMock->expects($this->never())
                             ->method('createUri');
        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);

        $request = $this->requestFactory->createRequest('GET', $uriMock);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame($uriMock, $request->getUri());
    }

    /**
     * @test
     * @covers ::createRequest
     * @covers ::__construct
     */
    public function shouldCreateRequestFromUriString(): void
    {
        $uriMock = $this->getMockBuilder(UriInterface::class)->getMock();
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $this->uriFactoryMock->expects($this->once())
                             ->method('createUri')
                             ->willReturn($uriMock);
        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);

        $request = $this->requestFactory->createRequest('GET', 'http://solid-framework.com');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame($uriMock, $request->getUri());
    }
}
