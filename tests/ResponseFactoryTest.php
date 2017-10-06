<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Solid\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\ResponseFactory
 */
class ResponseFactoryTest extends TestCase
{
    /**
     * @var \Solid\Http\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamFactoryMock;

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();

        $this->streamFactoryMock = $this->getMockBuilder(StreamFactoryInterface::class)
                                        ->setMethods([
                                            'createStream',
                                            'createStreamFromFile',
                                            'createStreamFromResource'
                                        ])
                                        ->getMock();

        /** @noinspection PhpParamsInspection */
        $this->responseFactory = new ResponseFactory($this->streamFactoryMock);
    }

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrResponseFactoryInterface(): void
    {
        $this->assertContains(ResponseFactoryInterface::class, class_implements(ResponseFactory::class));
    }

    /**
     * @test
     * @covers ::createResponse
     * @covers ::__construct
     */
    public function shouldReturnAResponseInterfaceWithTheCorrectStatus(): void
    {
        $statusCode = 200;
        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();
        $this->streamFactoryMock->expects($this->once())
                                ->method('createStream')
                                ->willReturn($streamMock);
        $response = $this->responseFactory->createResponse($statusCode);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
    }
}
