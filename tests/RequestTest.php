<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use Solid\Http\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\Request
 */
class RequestTest extends TestCase
{
    use MockGeneratorTrait;

    /**
     * @test
     * @coversNothing
     */
    public function shouldImplementPsrRequestInterface(): void
    {
        $this->assertContains(RequestInterface::class, class_implements(Request::class));
    }

    /**
     * @test
     * @covers ::getRequestTarget
     * @covers ::__construct
     */
    public function shouldReturnTheUriPathIfNoExplicitTargetExists(): void
    {
        $uriWithEmptyPath = $this->getUriMock();
        $uriWithEmptyPath->method('getPath')->willReturn('');
        $uriWithEmptyPath->method('getQuery')->willReturn('');

        $uriWithPath = $this->getUriMock();
        $uriWithPath->method('getPath')->willReturn('/path');
        $uriWithPath->method('getQuery')->willReturn('');

        $uriWithPathAndQuery = $this->getUriMock();
        $uriWithPathAndQuery->method('getPath')->willReturn('/path');
        $uriWithPathAndQuery->method('getQuery')->willReturn('key=value');

        /** @noinspection PhpParamsInspection */
        $requestWithEmptyPath = new Request(
            'GET',
            $uriWithEmptyPath,
            '1.1',
            $this->getHeadersMock(),
            $this->getBodyMock()
        );

        /** @noinspection PhpParamsInspection */
        $requestWithPath = new Request('GET', $uriWithPath, '1.1', $this->getHeadersMock(), $this->getBodyMock());

        /** @noinspection PhpParamsInspection */
        $requestWithPathAndQuery = new Request(
            'GET',
            $uriWithPathAndQuery,
            '1.1',
            $this->getHeadersMock(),
            $this->getBodyMock()
        );

        $this->assertSame('', $requestWithEmptyPath->getRequestTarget());
        $this->assertSame('/path', $requestWithPath->getRequestTarget());
        $this->assertSame('/path?key=value', $requestWithPathAndQuery->getRequestTarget());
    }

    /**
     * @test
     * @covers ::getRequestTarget
     */
    public function shouldReturnExplicitRequestTargetIfPresent(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = (new Request(
            'GET',
            $this->getUriMock(),
            '1.1',
            $this->getHeadersMock(),
            $this->getBodyMock()
        ))->withRequestTarget('target');

        $this->assertSame('target', $request->getRequestTarget());
    }

    /**
     * @test
     * @covers ::withRequestTarget
     */
    public function shouldReturnNewInstanceWithTarget(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $requestWithTarget = $request->withRequestTarget('target');

        $this->assertInstanceOf(RequestInterface::class, $requestWithTarget);
        $this->assertSame('target', $requestWithTarget->getRequestTarget());
    }

    /**
     * @test
     * @covers ::withRequestTarget
     */
    public function withRequestTargetShouldPreserveTheOriginalRequest(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getPath')->willReturn('');
        $uriMock->method('getQuery')->willReturn('');

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $uriMock, '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $requestWithTarget = $request->withRequestTarget('target');

        $this->assertNotSame($request, $requestWithTarget);
        $this->assertSame('', $request->getRequestTarget());
    }

    /**
     * @test
     * @covers ::getMethod
     * @covers ::__construct
     */
    public function shouldReturnTheRequestMethod(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame('GET', $request->getMethod());
    }

    /**
     * @test
     * @covers ::withMethod
     */
    public function shouldReturnNewInstanceWithMethod(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $requestWithMethod = $request->withMethod('POST');

        $this->assertInstanceOf(RequestInterface::class, $requestWithMethod);
        $this->assertSame('POST', $requestWithMethod->getMethod());
    }

    /**
     * @test
     * @covers ::withMethod
     */
    public function withMethodShouldNotNormalizeTheCaseOfMethod(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $requestWithMethod = $request->withMethod('PosT');

        $this->assertSame('PosT', $requestWithMethod->getMethod());
    }

    /**
     * @test
     * @covers ::withMethod
     */
    public function withMethodShouldPreserveOriginalRequest(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $requestWithMethod = $request->withMethod('POST');

        $this->assertNotSame($request, $requestWithMethod);
        $this->assertSame('GET', $request->getMethod());
    }

    /**
     * @test
     * @covers ::withMethod
     * @expectedException \InvalidArgumentException
     */
    public function withMethodShouldThrowExceptionIfInvalidMethod(): void
    {
        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $request->withMethod('invalid');
    }

    /**
     * @test
     * @covers ::getUri
     */
    public function shouldReturnTheUri(): void
    {
        $uriMock = $this->getUriMock();

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $uriMock, '1.1', $this->getHeadersMock(), $this->getBodyMock());

        $this->assertSame($uriMock, $request->getUri());
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function shouldReturnNewInstanceWithUri(): void
    {
        $uriMock = $this->getUriMock();

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $this->getHeadersMock(), $this->getBodyMock());

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($uriMock);

        $this->assertSame($uriMock, $requestWithUri->getUri());
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function withUriShouldPreserveOriginalRequest(): void
    {
        $uriMock = $this->getUriMock();

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $uriMock, '1.1', $this->getHeadersMock(), $this->getBodyMock());

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($this->getUriMock());

        $this->assertNotSame($request, $requestWithUri);
        $this->assertSame($uriMock, $request->getUri());
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function withUriShouldUpdateHostHeaderIfUriHostIsPresent(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $headersMock = $this->getCollectionImplementation();

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $this->getUriMock(), '1.1', $headersMock, $this->getBodyMock());

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($uriMock);

        $this->assertSame('solid-framework.com', $requestWithUri->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function withUriShouldNotUpdateHostHeaderIfUriHostIsNotPresent(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('');

        $headersMock = $this->getHeadersMock();
        $headersMock->method('get')->willReturn(['host']);
        $headersMock->expects($this->never())
            ->method('set');

        /** @noinspection PhpParamsInspection */
        $request = new Request(
            'GET',
            $this->getUriMock(),
            '1.1',
            $headersMock,
            $this->getBodyMock()
        );

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($uriMock);

        $this->assertSame('host', $requestWithUri->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function withUriShouldNotUpdateHostHeaderIfPreserveHostIsTrueAndPreviousHostHeaderExists(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $headersMock = $this->getHeadersMock();
        $headersMock->method('get')->willReturn(['host']);
        $headersMock->expects($this->never())
                    ->method('set');

        /** @noinspection PhpParamsInspection */
        $request = new Request(
            'GET',
            $this->getUriMock(),
            '1.1',
            $headersMock,
            $this->getBodyMock()
        );

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($uriMock, true);

        $this->assertSame('host', $requestWithUri->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::withUri
     */
    public function withUriShouldUpdateHostHeaderIfPreserveHostIsTrueAndHostIsNotPresentAndUriHostIsPresent(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $headersMock = $this->getCollectionImplementation(['Host' => ['']]);

        /** @noinspection PhpParamsInspection */
        $request = new Request(
            'GET',
            $this->getUriMock(),
            '1.1',
            $headersMock,
            $this->getBodyMock()
        );

        /** @noinspection PhpParamsInspection */
        $requestWithUri = $request->withUri($uriMock, true);

        $this->assertSame('solid-framework.com', $requestWithUri->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function shouldSetHostHeaderAtInitializationIfNotPresentAndPresentInUri(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $headersMock = $this->getCollectionImplementation(['Host' => ['']]);

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $uriMock, '1.1', $headersMock, $this->getBodyMock());

        $this->assertSame('solid-framework.com', $request->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function shouldNotSetHostHeaderAtInitializationIfAlreadyPresent(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $headersMock = $this->getHeadersMock();
        $headersMock->method('get')->willReturn(['host']);
        $headersMock->expects($this->never())
                    ->method('set');

        /** @noinspection PhpParamsInspection */
        $request = new Request('GET', $uriMock, '1.1', $headersMock, $this->getBodyMock());

        $this->assertSame('host', $request->getHeaderLine('Host'));
    }

    /**
     * @test
     * @covers ::__toString
     */
    public function shouldRenderCorrectlyAsAString(): void
    {
        $uriMock = $this->getUriMock();
        $uriMock->method('getPath')->willReturn('/path');
        $uriMock->method('getQuery')->willReturn('key=value');
        $uriMock->method('getHost')->willReturn('solid-framework.com');

        $bodyMock = $this->getBodyMock();
        $bodyMock->method('__toString')->willReturn('Request body');
        $bodyMock->method('getSize')->willReturn(12);

        $headersMock = $this->getCollectionImplementation(['Content-Type' => ['text/plain']]);

        /** @noinspection PhpParamsInspection */
        $request = new Request(
            'POST',
            $uriMock,
            '1.1',
            $headersMock,
            $bodyMock
        );

        $this->assertSame(
            <<<REQUEST
POST /path?key=value HTTP/1.1
Content-Type: text/plain
Content-Length: 12
Host: solid-framework.com

Request body
REQUEST
            ,
            (string)$request
        );

        $this->assertSame(
            <<<REQUEST
POST * HTTP/1.1
Content-Type: text/plain
Content-Length: 12
Host: solid-framework.com

Request body
REQUEST
            ,
            (string)$request->withRequestTarget('*')
        );
    }
}
