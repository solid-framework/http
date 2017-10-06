<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Psr\Http\Message\ResponseInterface;
use Solid\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\Response
 */
class ResponseTest extends TestCase
{
    use MockGeneratorTrait;

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrResponseInterface(): void
    {
        $this->assertContains(ResponseInterface::class, class_implements(Response::class));
    }

    /**
     * @test
     * @covers ::getStatusCode
     * @covers ::__construct
     */
    public function shouldReturnStatusCode(): void
    {
        $statusCode = 200;

        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', $statusCode, $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame($statusCode, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::withStatus
     * @covers ::getStatusCode
     */
    public function shouldReturnNewInstanceWithStatusCode()
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 200, $this->getHeadersMock(), $this->getBodyMock());

        $responseWithStatusCode = $response->withStatus(404);

        $this->assertInstanceOf(ResponseInterface::class, $responseWithStatusCode);
        $this->assertSame(404, $responseWithStatusCode->getStatusCode());
    }

    /**
     * @test
     * @covers ::withStatus
     * @expectedException \InvalidArgumentException
     */
    public function withProtocolShouldThrowExceptionIfInvalidStatusCode(): void
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 200, $this->getHeadersMock(), $this->getBodyMock());

        $response->withStatus(600);
    }

    /**
     * @test
     * @covers ::withStatus
     */
    public function withStatusCodeShouldPreserveTheOriginalResponse(): void
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 200, $this->getHeadersMock(), $this->getBodyMock());

        $responseWithStatusCode = $response->withStatus(404);

        $this->assertNotSame($response, $responseWithStatusCode);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     * @covers ::getReasonPhrase
     */
    public function shouldReturnReasonPhrase(): void
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 404, $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    /**
     * @test
     * @covers ::withStatus
     * @covers ::getReasonPhrase
     */
    public function withStatusCodeShouldAcceptCustomReasonPhrase(): void
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 200, $this->getHeadersMock(), $this->getBodyMock());

        $customReasonPhrase = 'Custom Reason Phrase';
        $responseWithStatusCode = $response->withStatus(404, $customReasonPhrase);

        $this->assertInstanceOf(ResponseInterface::class, $responseWithStatusCode);
        $this->assertSame($customReasonPhrase, $responseWithStatusCode->getReasonPhrase());
    }

    /**
     * @test
     * @covers ::withStatus
     * @covers ::getReasonPhrase
     */
    public function withStatusCodeShouldPreserveTheOriginalReasonPhrase(): void
    {
        /** @noinspection PhpParamsInspection */
        $response = new Response('1.1', 200, $this->getHeadersMock(), $this->getBodyMock());

        $responseWithStatusCode = $response->withStatus(404, 'Custom Reason Phrase');

        $this->assertNotSame($response, $responseWithStatusCode);
        $this->assertSame('OK', $response->getReasonPhrase());
    }
}
