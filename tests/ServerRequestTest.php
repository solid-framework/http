<?php

/**
 * Copyright (c) 2017 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Solid\Http\ServerRequest;
use Solid\Http\Tests\Fixtures\DummyDeserializer;

/**
 * @package Solid\Http\Tests
 * @author Martin Pettersson <martin@solid-framework.com>
 * @coversDefaultClass \Solid\Http\ServerRequest
 */
class ServerRequestTest extends TestCase
{
	use MockGeneratorTrait;

	/**
	 * @test
	 * @coversNothing
	 */
	public function shouldImplementPsrServerRequestInterface(): void
	{
		$this->assertContains(ServerRequestInterface::class, class_implements(ServerRequest::class));
	}

	/**
	 * @test
	 * @covers ::getServerParams
	 * @covers ::__construct
	 */
	public function shouldReturnServerParameters(): void
	{
	    $serverParameters = ['key' => 'value'];

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
            [],
            [],
            $serverParameters
		);

		$this->assertEquals($serverParameters, $request->getServerParams());
	}

	/**
	 * @test
	 * @covers ::getCookieParams
	 * @covers ::__construct
	 */
	public function shouldReturnCookieParameters(): void
	{
		$cookies = [
			'key' => 'value'
		];

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			[],
			$cookies
		);

		$this->assertEquals($cookies, $request->getCookieParams());
	}

	/**
	 * @test
	 * @covers ::withCookieParams
	 */
	public function shouldReturnNewInstanceWithCookieParameters(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			[],
			['key' => 'value']
		);
		$newCookies = ['new-key' => 'new-value'];
		$requestWithCookie = $request->withCookieParams($newCookies);

		$this->assertInstanceOf(ServerRequestInterface::class, $requestWithCookie);
		$this->assertEquals($newCookies, $requestWithCookie->getCookieParams());
	}

	/**
	 * @test
	 * @covers ::withCookieParams
	 */
	public function withCookieParamsShouldPreserveTheOriginalRequest(): void
	{
		$cookies = ['key' => 'value'];

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			[],
			$cookies
		);
		$newCookies = ['new-key' => 'new-value'];
		$requestWithCookies = $request->withCookieParams($newCookies);

		$this->assertNotSame($requestWithCookies, $request);
		$this->assertEquals($cookies, $request->getCookieParams());
	}

	/**
	 * @test
	 * @covers ::getQueryParams
	 */
	public function shouldReturnQueryParameters(): void
	{
		$uriMock = $this->getUriMock();
		$uriMock->method('getQuery')
		        ->willReturn('key=value');

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$uriMock,
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		$this->assertEquals(['key' => 'value'], $request->getQueryParams());
	}

	/**
	 * @test
	 * @covers ::getQueryParams
	 */
	public function shouldReturnNewInstanceWithQueryParameters(): void
	{
		$uriMock = $this->getUriMock();
		$uriMock->method('getQuery')
		        ->willReturn('key=value');

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$uriMock,
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$queryParameters = ['new-key' => 'new-value'];
		$requestWithQueryParameters = $request->withQueryParams($queryParameters);

		$this->assertInstanceOf(ServerRequestInterface::class, $requestWithQueryParameters);
		$this->assertEquals($queryParameters, $requestWithQueryParameters->getQueryParams());
	}

	/**
	 * @test
	 * @covers ::withQueryParams
	 */
	public function withQueryParamsShouldPreserveTheOriginalRequest(): void
	{
		$uriMock = $this->getUriMock();
		$uriMock->method('getQuery')
		        ->willReturn('key=value');

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$uriMock,
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$queryParameters = ['new-key' => 'new-value'];
		$requestWithQueryParameters = $request->withQueryParams($queryParameters);

		$this->assertNotSame($requestWithQueryParameters, $request);
		$this->assertEquals(['key' => 'value'], $request->getQueryParams());
	}

	/**
	 * @test
	 * @covers ::getUploadedFiles
	 * @covers ::__construct
	 */
	public function shouldReturnUploadedFilesParameters(): void
	{
		$uploadedFiles = ['file' => $this->getUploadedFileMock()];

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			$uploadedFiles
		);

		$this->assertEquals($uploadedFiles, $request->getUploadedFiles());
	}

	/**
	 * @test
	 * @covers ::withUploadedFiles
	 */
	public function shouldReturnNewInstanceWithUploadedFiles(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			['file' => $this->getUploadedFileMock()]
		);
		$newFiles = ['files' => ['new' => $this->getUploadedFileMock()]];
		$requestWithFiles = $request->withUploadedFiles($newFiles);

		$this->assertInstanceOf(ServerRequestInterface::class, $requestWithFiles);
		$this->assertEquals($newFiles, $requestWithFiles->getUploadedFiles());
	}

	/**
	 * @test
	 * @covers ::withUploadedFiles
	 */
	public function withUploadedFilesShouldPreserveTheOriginalRequest(): void
	{
		$files = ['file' => $this->getUploadedFileMock()];

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock(),
			$files
		);
		$newFiles = ['files' => ['new' => $this->getUploadedFileMock()]];
		$requestWithFiles = $request->withUploadedFiles($newFiles);

		$this->assertNotSame($requestWithFiles, $request);
		$this->assertEquals($files, $request->getUploadedFiles());
	}

	/**
	 * @test
	 * @covers ::getAttributes
	 */
	public function shouldReturnEmptyArrayIfNoAttributesAreSet(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		$this->assertEmpty($request->getAttributes());
	}

	/**
	 * @test
	 * @covers ::getAttributes
	 * @covers ::withAttribute
	 */
	public function shouldReturnRequestAttributes(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		$this->assertEquals(['key' => 'value'], $request->withAttribute('key', 'value')->getAttributes());
	}

	/**
	 * @test
	 * @covers ::getAttribute
	 * @covers ::withAttribute
	 */
	public function shouldReturnRequestAttributeForGivenName(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		$this->assertEquals('value', $request->withAttribute('key', 'value')->getAttribute('key'));
	}

	/**
	 * @test
	 * @covers ::getAttribute
	 */
	public function shouldReturnRequestDefaultValueIfAttributeIsNotSet(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		$this->assertEquals('default', $request->getAttribute('key', 'default'));
	}

	/**
	 * @test
	 * @covers ::withAttribute
	 * @covers ::getAttributes
	 */
	public function shouldReturnInstanceWithAttribute(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$withAttribute = $request->withAttribute('key', 'value');

		$this->assertInstanceOf(ServerRequestInterface::class, $withAttribute);
		$this->assertEquals(['key' => 'value'], $withAttribute->getAttributes());
	}

	/**
	 * @test
	 * @covers ::withAttribute
	 * @covers ::getAttributes
	 */
	public function withAttributeShouldPreserveTheOriginalRequest(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$withAttribute = $request->withAttribute('key', 'value');

		$this->assertNotSame($request, $withAttribute);
		$this->assertEmpty($request->getAttributes());
	}

	/**
	 * @test
	 * @covers ::withoutAttribute
	 * @covers ::getAttributes
	 */
	public function shouldReturnInstanceWithoutAttribute(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$withAttribute = $request->withAttribute('key', 'value');

		$this->assertSame('value', $withAttribute->getAttribute('key'));

		$withoutAttribute = $withAttribute->withoutAttribute('key');

		$this->assertInstanceOf(ServerRequestInterface::class, $withoutAttribute);
		$this->assertEmpty($withoutAttribute->getAttributes());
	}

	/**
	 * @test
	 * @covers ::withoutAttribute
	 * @covers ::getAttributes
	 */
	public function withoutAttributeShouldPreserveTheOriginalRequest(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$withAttribute = $request->withAttribute('key', 'value');

		$this->assertSame('value', $withAttribute->getAttribute('key'));

		$withoutAttribute = $withAttribute->withoutAttribute('key');

		$this->assertNotSame($withAttribute, $withoutAttribute);
		$this->assertEquals(['key' => 'value'], $withAttribute->getAttributes());
	}

	/**
	 * @test
	 * @covers ::getParsedBody
	 * @covers ::shouldReturnDefaultBody
	 */
	public function shouldReturnContentsOfPOSTWhenApplicable(): void
	{
		$_POST = ['key' => 'value'];

		$headersMock = $this->getHeadersMock();

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'POST',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);

		$headersMock->method('get')
		            ->willReturnOnConsecutiveCalls(
			            ['application/x-www-form-urlencoded'],
			            ['multipart/form-data']
		            );

		$this->assertEquals($_POST, $request->getParsedBody());
		$this->assertEquals($_POST, $request->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::getParsedBody
	 */
	public function shouldReturnNullIfNoDeserializerIsConfiguredForContentType(): void
	{
		$headersMock = $this->getHeadersMock();

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'POST',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);

		$headersMock->method('get')
		            ->willReturn(['application/custom-content-type']);

		$this->assertNull($request->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::getParsedBody
	 */
	public function shouldReturnAnInstanceWithExplicitlySetParsedBody(): void
	{
		$_POST = [];

		$headersMock = $this->getHeadersMock();

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'POST',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);
		$parsedBody = ['key' => 'value'];
		$withParsedBody = $request->withParsedBody($parsedBody);

		$headersMock->method('get')
		            ->willReturn(['application/x-www-form-urlencoded']);

		$this->assertInstanceOf(ServerRequestInterface::class, $withParsedBody);
		$this->assertEquals($parsedBody, $withParsedBody->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::withParsedBody
	 */
	public function withParsedBodyShouldPreserveTheOriginalRequest(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);
		$withParsedBody = $request->withParsedBody(['key' => 'value']);

		$this->assertNotSame($request, $withParsedBody);
		$this->assertNull($request->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::withParsedBody
	 * @expectedException \InvalidArgumentException
	 */
	public function shouldThrowExceptionIfUnsupportedParsedBodyType(): void
	{
		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$this->getHeadersMock(),
			$this->getBodyMock()
		);

		/** @noinspection PhpParamsInspection */
		$request->withParsedBody('Unsupported type');
	}

	/**
	 * @test
	 * @covers ::getParsedBody
	 * @covers ::addDeserializer
	 */
	public function shouldUseMatchingDeserializer(): void
	{
		$contentTypes = ['application/custom-content-type'];
		$parsedBody = ['key' => 'value'];
		$headersMock = $this->getHeadersMock();
		$headersMock->method('get')
		            ->willReturn($contentTypes);

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);
		$request->addDeserializer(new DummyDeserializer($contentTypes, $parsedBody));

		$this->assertEquals($parsedBody, $request->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::addDeserializer
	 */
	public function shouldAppendDeserializer(): void
	{
		$contentTypes = ['application/custom-content-type'];
		$parsedBody = ['key' => 'value'];
		$headersMock = $this->getHeadersMock();
		$headersMock->method('get')
		            ->willReturn($contentTypes);

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);
		$request->addDeserializer(new DummyDeserializer($contentTypes, $parsedBody));
		$request->addDeserializer(new DummyDeserializer($contentTypes, ['another-key' => 'another-value']));

		$this->assertEquals($parsedBody, $request->getParsedBody());
	}

	/**
	 * @test
	 * @covers ::addDeserializer
	 */
	public function shouldPrependDeserializer(): void
	{
		$contentTypes = ['application/custom-content-type'];
		$parsedBody = ['key' => 'value'];
		$headersMock = $this->getHeadersMock();
		$headersMock->method('get')
		            ->willReturn($contentTypes);

		/** @noinspection PhpParamsInspection */
		$request = new ServerRequest(
			'GET',
			$this->getUriMock(),
			'1.1',
			$headersMock,
			$this->getBodyMock()
		);
		$request->addDeserializer(new DummyDeserializer($contentTypes, ['another-key' => 'another-value']));
		$request->addDeserializer(new DummyDeserializer($contentTypes, $parsedBody), true);

		$this->assertEquals($parsedBody, $request->getParsedBody());
	}
}
